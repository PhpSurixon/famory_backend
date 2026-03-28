<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserReport;
use App\Models\User;
use App\Models\BlockUser;
use App\Models\Follow;
use App\Models\FamilyMember;
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

    // public function blockUser(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'marked_user_id' => 'required|exists:users,id',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'message' => $validator->errors()->first(),
    //                 'status'  => 'failed'
    //             ], 400);
    //         }

    //         $authUser = Auth::user();
    //         $targetId = $request->marked_user_id;
    //         DB::beginTransaction();

    //         if ($authUser->id == $targetId) {
    //             return response()->json([
    //                 'message' => "You cannot block yourself",
    //                 'status'  => 'failed'
    //             ], 400);
    //         }

    //         $block = BlockUser::where('user_id', $authUser->id)
    //             ->where('marked_user_id', $targetId)
    //             ->first();

    //         if ($block) {
    //             // Toggle block/unblock
    //             if ($block->block == 1) {
    //                 $block->update(['block' => 0]); // Unblock
    //                 $msg = "User unblocked successfully";
    //                 $action = "unblocked";
    //             } else {
    //                 $block->update(['block' => 1]); // Block
    //                 $msg = "User blocked successfully";
    //                 $action = "blocked";

    //                 Follow::where(function ($q) use ($authUser, $targetId) {
    //                     $q->where('follower_id', $authUser->id)->where('following_id', $targetId);
    //                 })->orWhere(function ($q) use ($authUser, $targetId) {
    //                     $q->where('follower_id', $targetId)->where('following_id', $authUser->id);
    //                 })->delete();
    //             }
    //         } else {
    //             // First time block
    //             $block = BlockUser::create([
    //                 'user_id'       => $authUser->id,
    //                 'marked_user_id'=> $targetId,
    //                 'block'         => 1,
    //                 'is_live'       => 0
    //             ]);

    //             // remove follow relations
    //             Follow::where(function ($q) use ($authUser, $targetId) {
    //                 $q->where('follower_id', $authUser->id)->where('following_id', $targetId);
    //             })->orWhere(function ($q) use ($authUser, $targetId) {
    //                 $q->where('follower_id', $targetId)->where('following_id', $authUser->id);
    //             })->delete();


    //             $msg = "User blocked successfully";
    //             $action = "blocked";
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => $msg,
    //             'status'  => "success",
    //             'data'    => [
    //                 'user_id'       => $authUser->id,
    //                 'marked_user_id'=> $targetId,
    //                 'action'        => $action
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => "Something went wrong! " . $e->getMessage(),
    //             'status'  => 'failed'
    //         ], 400);
    //     }
    // }

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

            if ($authUser->id == $targetId) {
                return response()->json([
                    'message' => "You cannot block yourself",
                    'status'  => 'failed'
                ], 400);
            }

            DB::beginTransaction();

            $block = BlockUser::where('user_id', $authUser->id)
                ->where('marked_user_id', $targetId)
                ->first();

            if ($block) {

                if ($block->block == 1) {
                    // 🔓 UNBLOCK
                    $block->update(['block' => 0]);
                    $msg = "User unblocked successfully";
                    $action = "unblocked";

                } else {
                    // 🔒 BLOCK
                    $block->update(['block' => 1]);
                    $msg = "User blocked successfully";
                    $action = "blocked";

                    $this->removeRelations($authUser->id, $targetId);
                }

            } else {

                // First time block
                $block = BlockUser::create([
                    'user_id'        => $authUser->id,
                    'marked_user_id' => $targetId,
                    'block'          => 1,
                    'is_live'        => 0
                ]);

                $this->removeRelations($authUser->id, $targetId);

                $msg = "User blocked successfully";
                $action = "blocked";
            }

            DB::commit();

            return response()->json([
                'message' => $msg,
                'status'  => "success",
                'data'    => [
                    'user_id'        => $authUser->id,
                    'marked_user_id' => $targetId,
                    'action'         => $action
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

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
            $search = $request->get('search');

            $authUser = Auth::user();

            /*
            |---------------------------------------
            | Base Query (ONLY NOT-DELETED USERS)
            |---------------------------------------
            */
            $query = BlockUser::where('user_id', $authUser->id)
                ->where('block', 1)
                ->whereHas('blockedUser', function ($q) {
                    $q->whereNull('deleted_at'); // ✅ exclude deleted users
                })
                ->with(['blockedUser' => function ($q) {
                    $q->select('id', 'first_name', 'last_name', 'email', 'username', 'image')
                    ->whereNull('deleted_at'); // ✅ ensure eager load also filtered
                }]);

            /*
            |---------------------------------------
            | Search Filter
            |---------------------------------------
            */
            if (!empty($search)) {
                $query->whereHas('blockedUser', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            }

            /*
            |---------------------------------------
            | Total Count
            |---------------------------------------
            */
            $totalUsers = $query->count();

            /*
            |---------------------------------------
            | Get Data
            |---------------------------------------
            */
            $blockedUsers = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            /*
            |---------------------------------------
            | Transform Data (SAFE)
            |---------------------------------------
            */
            $users = $blockedUsers->map(function ($block) {

                $blocked = $block->blockedUser;

                if (!$blocked) {
                    return null; // extra safety
                }

                return [
                    'block_id'      => $block->id,
                    'user_id'       => $blocked->id,
                    'first_name'    => $blocked->first_name,
                    'last_name'     => $blocked->last_name,
                    'email'         => $blocked->email,
                    'username'      => $blocked->username,
                    'image'         => $blocked->image ?: null,
                    'action_button' => "Unblock"
                ];
            })->filter()->values(); // ✅ remove nulls

            /*
            |---------------------------------------
            | Final Response
            |---------------------------------------
            */
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

    // public function blockedUsers(Request $request)
    // {
    //     try {
    //         $limit  = (int) $request->get('limit', 30);
    //         $page   = (int) $request->get('page', 1);
    //         $offset = ($page - 1) * $limit;
    //         $search = $request->get('search'); // optional search param

    //         $authUser = Auth::user();

    //         // Base query
    //         $query = BlockUser::where('user_id', $authUser->id)
    //             ->where('block', 1)
    //             ->with('blockedUser:id,first_name,last_name,email,username,image');

    //         // 🔍 Search filter
    //         if (!empty($search)) {
    //             $query->whereHas('blockedUser', function ($q) use ($search) {
    //                 $q->where('first_name', 'like', "%{$search}%")
    //                     ->orWhere('last_name', 'like', "%{$search}%")
    //                     ->orWhere('username', 'like', "%{$search}%")
    //                     ->orWhere('email', 'like', "%{$search}%");
    //             });
    //         }

    //         $totalUsers = $query->count();

    //         $blockedUsers = $query->orderBy('id', 'desc')
    //             ->skip($offset)
    //             ->take($limit)
    //             ->get();

    //         $users = $blockedUsers->map(function ($block) {
    //             $blocked = $block->blockedUser;
    //             $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

    //             return [
    //                 'block_id'     => $block->id,
    //                 'user_id'      => $blocked->id,
    //                 'first_name'   => $blocked->first_name,
    //                 'last_name'    => $blocked->last_name,
    //                 'email'        => $blocked->email,
    //                 'username'     => $blocked->username,
    //                 // 'image'        => $blocked->image ? $s3BaseUrl . $blocked->image : null,
    //                 'image'        => $blocked->image ? $blocked->image : null,
    //                 'action_button'=> "Unblock" // always unblock option
    //             ];
    //         });

    //         $data = [
    //             'user_id'     => $authUser->id,
    //             'count'       => $totalUsers,
    //             'page'        => $page,
    //             'limit'       => $limit,
    //             'total_pages' => $limit > 0 ? ceil($totalUsers / $limit) : 0,
    //             'users'       => $users
    //         ];

    //         return response()->json([
    //             'message' => 'Blocked users fetched successfully',
    //             'status'  => "success",
    //             'data'    => $data
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => "Something Went Wrong! " . $e->getMessage(),
    //             'status'  => 'failed'
    //         ], 400);
    //     }
    // }


    private function removeRelations($userA, $userB)
    {
        // 🗑 Remove Follow BOTH ways
        Follow::where(function ($q) use ($userA, $userB) {
            $q->where('follower_id', $userA)
              ->where('following_id', $userB);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('follower_id', $userB)
              ->where('following_id', $userA);
        })->delete();


        // 🗑 Remove FamilyMember BOTH ways
        FamilyMember::where(function ($q) use ($userA, $userB) {
            $q->where('user_id', $userA)
              ->where('member_id', $userB);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('user_id', $userB)
              ->where('member_id', $userA);
        })->delete();
    }



}
