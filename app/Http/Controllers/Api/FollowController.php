<?php

// app/Http/Controllers/Api/FollowController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;
class FollowController extends Controller
{
    use OneSignalTrait;

    public function follow(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'following_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            $id = $request->following_id;

            $targetUser = User::findOrFail($id);
            $authUser = Auth::user();

            if ($targetUser->id === $authUser->id) {
                return response()->json(['message' => "You can't follow yourself", 'status' => 'failed'], 400);
            }
            $existing = Follow::where('follower_id', $authUser->id)
                ->where('following_id', $targetUser->id)
                ->first();

            if ($existing) {
                return response()->json(['message' => 'Already requested or following'], 400);
            }

            $status = $targetUser->is_private ? 'pending' : 'approved';

            $createFollow=  Follow::create([
                'follower_id' => Auth::id(),
                'following_id' => $targetUser->id,
                'status' => $status,
            ]);

            if ($status == 'pending') {
                $msg = "Follow request sent to {$targetUser->first_name}";
                $this->notifyMessage($authUser, $targetUser->id, $authUser->id, "follow_request"); // pending request
            } else {
                $msg = "You are now following {$targetUser->first_name}";
                $this->notifyMessage($authUser, $targetUser->id, $authUser->id, "follow"); // auto follow
            }

            return response()->json(['message' => $msg, 'status' => 'success'], 200);

        } catch (Exception $e) {
            return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }


    public function unfollow(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'following_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
            $userId = $request->following_id;
            $authUser = Auth::id();

            $follow = Follow::where('follower_id', $authUser)
                ->where('following_id', $userId)
                ->where('status', 'approved')
                ->first();

            if (!$follow) {
                return response()->json(['message' => 'Not following this user'], 404);
            }

            $follow->delete();
            return response()->json(['message' => "You unfollowed user", 'status' => 'success'], 200);

        } catch (Exception $e) {
            return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }


    public function followers(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 30);
            $page = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $authUser = Auth::user();
            $search = $request->get('search'); // optional search param

            $query = Follow::where('following_id', $authUser->id)
                            ->where('status', 'approved')
                            ->with('follower:id,first_name,last_name,email,username,image');

            // 🔍 Apply search if present
            if (!empty($search)) {
                $query->whereHas('follower', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $totalUsers = $query->count();

            $followers = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            $users = $followers->map(function ($follow) use ($authUser) {
                $follower = $follow->follower;

                // Check if I already follow this user back
                $relation = Follow::where('follower_id', $authUser->id)
                                ->where('following_id', $follower->id)
                                ->first();

                if (!$relation) {
                    $action = "Follow Back"; // not following yet
                } elseif ($relation->status === 'approved') {
                    $action = "Following";   // already following
                } elseif ($relation->status === 'pending') {
                    $action = "Requested";   // request sent (private account)
                } else {
                    $action = "Follow Back"; // fallback
                }

                return [
                    'follow_id'     => $follow->id,
                    'user_id'       => $follower->id,
                    'first_name'    => $follower->first_name,
                    'last_name'     => $follower->last_name,
                    'email'         => $follower->email,
                    'username'      => $follower->username,
                    'image'         => $follower->image,
                    'action_button' => $action
                ];
            });

            $data = [
                'user_id'     => $authUser->id,
                'count'       => $totalUsers,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($totalUsers / $limit),
                'users'       => $users
            ];

            return response()->json([
                'message' => 'Followers fetched successfully',
                'status'  => "success",
                'data'    => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status' => 'failed'
            ], 400);
        }
    }

    public function following(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 30);
            $page = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $authUser = Auth::user();
            $search = $request->get('search'); // 🔍 search query

            $query = Follow::where('follower_id', $authUser->id)
                ->where('status', 'approved')
                ->with('following:id,first_name,last_name,email,username,image');

            // Apply search on "following" user details
            if (!empty($search)) {
                $query->whereHas('following', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $totalUsers = $query->count();

            $following = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            $users = $following->map(function ($follow) {
                $user = $follow->following;

                return [
                    'follow_id'   => $follow->id,   // unique row id from follows table
                    'user_id'     => $user->id,
                    'first_name'  => $user->first_name,
                    'last_name'   => $user->last_name,
                    'email'       => $user->email,
                    'username'    => $user->username,
                    'image'       => $user->image,
                ];
            });

            $data = [
                'user_id'     => $authUser->id,
                'count'       => $totalUsers,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($totalUsers / $limit),
                'users'       => $users
            ];

            return response()->json([
                'message' => 'Following fetched successfully',
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



    public function pendingRequests(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 30);
            $page = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $authUser = Auth::user();

            $query = Follow::where('following_id', $authUser->id)
                            ->where('status', 'pending')
                            ->with('follower:id,first_name,last_name,email,username,image');

            $totalRequests = $query->count();

            $requests = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            // Map to include follow_id + follower user info
            $users = $requests->map(function ($follow) {
                return [
                    'follow_id'   => $follow->id,   // unique follow row id
                    'user_id'     => $follow->follower->id,
                    'first_name'  => $follow->follower->first_name,
                    'last_name'   => $follow->follower->last_name,
                    'email'       => $follow->follower->email,
                    'username'    => $follow->follower->username,
                    'image'       => $follow->follower->image,
                ];
            });

            $data = [
                'user_id' => $authUser->id,
                'count' => $totalRequests,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalRequests / $limit),
                'users' => $users
            ];

            return response()->json([
                'message' => 'Pending requests fetched successfully',
                'status' => "success",
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status' => 'failed'
            ], 400);
        }
    }


    public function respondToRequest(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'follow_id' => 'required',
                'request_status' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            $authUser = Auth::user();
            DB::beginTransaction();

            $followRequest = Follow::where('id', $request->follow_id)
                                    ->where('following_id', $authUser->id) // only requests TO me
                                    ->where('status', 'pending')
                                    ->first();
            if (!$followRequest) {
                return response()->json(['message' => 'Follow Request not found!'], 400);
            }
            $action = $request->request_status;

            if ($action === 'approve') {
                $followRequest->status = 'approved';
                $this->notifyMessage($authUser,$followRequest->follower_id,$authUser->id,"follow_accept");
            } elseif ($action === 'reject') {
                $followRequest->status = 'rejected';
                // $this->notifyMessage($authUser,$followRequest->follower_id,null,"follow_reject");
            } else {
                return response()->json(['message' => 'Invalid action'], 400);
            }
            $followRequest->save();
            DB::commit();

            return response()->json([
                'message' => "Request {$action}ed successfully",
                'status' => 'success'
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status' => 'failed'
            ], status: 400);
        }
    }

    public function getFollowRequestDetail(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'follow_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            $authUser = Auth::user();
            $followRequest = Follow::where('id', $request->follow_id)
                ->where('following_id', $authUser->id)
                ->where('status', 'pending')
                ->with([
                    'follower:id,first_name,last_name,username,email,image',
                ])
                ->first();

            if (!$followRequest) {
                return response()->json([
                    'message' => 'Follow request not found or not pending',
                    'status'  => 'failed'
                ], 404);
            }

            $user = $followRequest->follower;

            $data = [
                'request_id'   => $followRequest->id,
                'status'       => $followRequest->status,
                'created_at'   => $followRequest->created_at->diffForHumans(),
                'user' => [
                    'id'         => $user->id,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'username'   => $user->username,
                    'email'      => $user->email,
                    'image'      => $user->image,
                ]
            ];

            return response()->json([
                'message' => 'Follow request details fetched successfully',
                'status'  => 'success',
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong! ' . $e->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }







}
