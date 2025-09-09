<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserReport;
use App\Models\User;
use App\Models\BlockUser;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;

class UserReportController extends Controller
{
    public function storeReport(Request $request)
    {
        try {
            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'reported_user_id' => 'required|exists:users,id',
                'reason' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $reportedUser = User::findOrFail($request->reported_user_id);

            //Prevent self-report
            if ($reportedUser->id === $authUser->id) {
                return response()->json([
                    'message' => "You cannot report yourself",
                    'status' => 'failed'
                ], 400);
            }

            //Already reported check
            $exists = UserReport::where('reporter_id', $authUser->id)
                ->where('reported_user_id', $reportedUser->id)
                ->first();

            if ($exists) {
                return response()->json([
                    'message' => "You have already reported this user",
                    'status' => 'failed'
                ], 400);
            }

            //Create report
            $report = UserReport::create([
                'reporter_id'      => $authUser->id,
                'reported_user_id' => $reportedUser->id,
                'reason'           => $request->reason,
                'description'      => $request->description,
            ]);

            //Success response
            return response()->json([
                'message' => "You reported {$reportedUser->first_name} successfully",
                'status'  => 'success',
                'data'    => $report
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status' => 'failed'
            ], 400);
        }
    }

    public function blockUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'marked_user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $targetId = $request->marked_user_id;
            DB::beginTransaction();

            if ($authUser->id == $targetId) {
                return response()->json([
                    'message' => "You cannot block yourself",
                    'status'  => 'failed'
                ], 400);
            }

            $block = BlockUser::where('user_id', $authUser->id)
                ->where('marked_user_id', $targetId)
                ->first();

            if ($block) {
                // Toggle block/unblock
                if ($block->block == 1) {
                    $block->update(['block' => 0]); // Unblock
                    $msg = "User unblocked successfully";
                    $action = "unblocked";
                } else {
                    $block->update(['block' => 1]); // Block
                    $msg = "User blocked successfully";
                    $action = "blocked";
                }
            } else {
                // First time block
                $block = BlockUser::create([
                    'user_id'       => $authUser->id,
                    'marked_user_id'=> $targetId,
                    'block'         => 1,
                    'is_live'       => 0
                ]);

                // remove follow relations
                Follow::where(function ($q) use ($authUser, $targetId) {
                    $q->where('follower_id', $authUser->id)->where('following_id', $targetId);
                })->orWhere(function ($q) use ($authUser, $targetId) {
                    $q->where('follower_id', $targetId)->where('following_id', $authUser->id);
                })->delete();


                $msg = "User blocked successfully";
                $action = "blocked";
            }

            DB::commit();

            return response()->json([
                'message' => $msg,
                'status'  => "success",
                'data'    => [
                    'user_id'       => $authUser->id,
                    'marked_user_id'=> $targetId,
                    'action'        => $action
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }


    public function blockedUsers(Request $request)
    {
        try {
            $limit  = (int) $request->get('limit', 30);
            $page   = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $search = $request->get('search'); // optional search param

            $authUser = Auth::user();

            // Base query
            $query = BlockUser::where('user_id', $authUser->id)
                ->where('block', 1)
                ->with('blockedUser:id,first_name,last_name,email,username,image');

            // 🔍 Search filter
            if (!empty($search)) {
                $query->whereHas('blockedUser', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $totalUsers = $query->count();

            $blockedUsers = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            $users = $blockedUsers->map(function ($block) {
                $blocked = $block->blockedUser;
                $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

                return [
                    'block_id'     => $block->id,
                    'user_id'      => $blocked->id,
                    'first_name'   => $blocked->first_name,
                    'last_name'    => $blocked->last_name,
                    'email'        => $blocked->email,
                    'username'     => $blocked->username,
                    'image'        => $blocked->image ? $s3BaseUrl . $blocked->image : null,
                    'action_button'=> "Unblock" // always unblock option
                ];
            });

            $data = [
                'user_id'     => $authUser->id,
                'count'       => $totalUsers,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => $limit > 0 ? ceil($totalUsers / $limit) : 0,
                'users'       => $users
            ];

            return response()->json([
                'message' => 'Blocked users fetched successfully',
                'status'  => "success",
                'data'    => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }


}
