<?php

// app/Http/Controllers/Api/FollowController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use App\Models\FamilyMember;
use App\Models\BlockUser;
use App\Models\Notification;
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

        } catch (\Exception $e) {
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
    
    public function followerRemove(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'follower_user_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            $userId   = $request->follower_user_id;
            $authUser = Auth::id();

            $follow = Follow::where('follower_id', $userId)
                ->where('following_id', $authUser)
                ->where('status', 'approved')
                ->first();

            if (!$follow) {
                return response()->json(['message' => 'This user is not your follower', 'status' => 'failed'], 404);
            }

            $follow->delete();
            return response()->json(['message' => "You removed this user from your followers", 'status' => 'success'], 200);

        } catch (Exception $e) {
            return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }

    public function followers(Request $request)
    {
        try {

            /* ---------------- Pagination ---------------- */
            $limit  = max((int) $request->get('limit', 30), 1);
            $page   = max((int) $request->get('page', 1), 1);
            $offset = ($page - 1) * $limit;
            $search = $request->get('search');
            $userId = $request->get('user_id');

            $authId = Auth::id();
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);

            /* ---------------- Target User ---------------- */
            if ($userId) {
                $targetUser = User::find($userId);
                if (!$targetUser) {
                    return response()->json([
                        'status'  => 'failed',
                        'message' => 'User not found'
                    ], 404);
                }
                $targetUserId = $userId;
            } else {
                $targetUserId = $authId;
            }

            /* ---------------- Followers Query ---------------- */
            $query = Follow::where('following_id', $targetUserId)
                ->where('status', 'approved')
                ->whereNotIn('follower_id', $blockedUserIds)
                ->whereHas('follower') // 🔥 prevent null users
                ->with('follower:id,first_name,last_name,email,username,image');

            if ($search) {
                $query->whereHas('follower', function ($q) use ($search) {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('username', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            }

            $totalUsers = $query->count();

            $followers = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            /* ---------------- Collect follower IDs ---------------- */
            $followerIds = $followers->pluck('follower.id')->filter()->values();

            /* ---------------- Follow relation (auth → follower) ---------------- */
            $relations = Follow::where('follower_id', $authId)
                ->whereIn('following_id', $followerIds)
                ->pluck('status', 'following_id');

            /* ---------------- Blocked users ---------------- */
            $blocked = BlockUser::where('user_id', $authId)
                ->whereIn('marked_user_id', $followerIds)
                ->where('block', 1)
                ->pluck('marked_user_id')
                ->toArray();

            /* ---------------- Response mapping ---------------- */
            $users = $followers->map(function ($follow) use ($relations, $blocked) {

                if (!$follow->follower) {
                    return null;
                }

                $follower = $follow->follower;
                $status   = $relations[$follower->id] ?? null;

                if ($status === 'approved') {
                    $action = "Following";
                    $isFollowing = true;
                } elseif ($status === 'pending') {
                    $action = "Requested";
                    $isFollowing = false;
                } else {
                    $action = "Follow";
                    $isFollowing = false;
                }

                return [
                    'follow_id'     => $follow->id,
                    'user_id'       => $follower->id,
                    'first_name'    => $follower->first_name,
                    'last_name'     => $follower->last_name,
                    'email'         => $follower->email,
                    'username'      => $follower->username,
                    'image'         => $follower->image,
                    'action_button' => $action,
                    'is_following'  => $isFollowing,
                    'is_block'      => in_array($follower->id, $blocked),
                ];
            })->filter()->values();

            /* ---------------- Final Response ---------------- */
            return response()->json([
                'status'  => 'success',
                'message' => 'Followers fetched successfully',
                'data'    => [
                    'user_id'     => (int) $targetUserId,
                    'count'       => $totalUsers,
                    'page'        => $page,
                    'limit'       => $limit,
                    'total_pages' => ceil($totalUsers / $limit),
                    'users'       => $users
                ]
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function following(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 30);
            $page = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $search = $request->get('search');
            $user_id = $request->get('user_id');

            $authId = Auth::id();
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);

            if (!empty($user_id)) {
                $checkUser = User::find($user_id);
                if (!$checkUser) {
                    return response()->json([
                        'message' => 'User not found',
                        'status'  => 'failed'
                    ], 404);
                }
                $get_follower_user_id = $user_id;
            } else {
                $get_follower_user_id = $authId;
            }

            $query = Follow::where('follower_id', $get_follower_user_id)
                ->where('status', 'approved')
                ->whereNotIn('following_id', $blockedUserIds)
                ->with('following:id,first_name,last_name,email,username,image');

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

            // Collect all following user IDs
            $followingIds = $following->pluck('following.id')->filter()->all();

            // Fetch relations in one query
            $relations = Follow::where('follower_id', $authId)
                ->whereIn('following_id', $followingIds)
                ->pluck('status', 'following_id');

            // Fetch blocked users in one query
            $blocked = BlockUser::where('user_id', $authId)
                                ->whereIn('marked_user_id', $followingIds)
                                ->where('block',1)
                                ->pluck('marked_user_id')
                                ->toArray();

            $users = $following->map(function ($follow) use ($relations,$blocked) 
            {
                if (!$follow->following) 
                {
                    return null;
                }
                
                $user = $follow->following;
                $status = $relations[$user->id] ?? null;

                if ($status === 'approved') {
                    $action = "Following";
                    $isFollowing = true;
                } elseif ($status === 'pending') {
                    $action = "Requested";
                    $isFollowing = false;
                } else {
                    $action = "Follow";
                    $isFollowing = false;
                }

                $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

                return [
                    'follow_id'     => $follow->id,
                    'user_id'       => $user->id,
                    'first_name'    => $user->first_name,
                    'last_name'     => $user->last_name,
                    'email'         => $user->email,
                    'username'      => $user->username,
                    // 'image'         => $user->image ? $s3BaseUrl . $user->image : null,
                    'image'         => $user->image ? $user->image : null,
                    'action_button' => $action,
                    'is_following'  => $isFollowing,
                    'is_block'      => in_array($user->id, $blocked)
                ];
            });

            $data = [
                'user_id'     => (int) $get_follower_user_id,
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
                'status' => 'failed'
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

            $update_notification = Notification::where('item_id',$followRequest->id)
                                                ->where('receiver_id',$followRequest->following_id)
                                                ->where('has_actioned',0)
                                                ->first();
            if($update_notification)
            {
                $update_notification->has_actioned =1;
                $update_notification->save();
            }



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
                // ->where('status', 'pending')
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

    // public function addFamily(Request $request)
    // {
    //     try {

    //         $validator = Validator::make($request->all(), [
    //             'user_id' => 'required',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'message' => $validator->errors()->first(),
    //                 'status' => 'failed'
    //             ], 400);
    //         }

    //         $targetUser = User::findOrFail($request->user_id);
    //         $authUser   = Auth::user();

    //         // Self check
    //         if ($targetUser->id == $authUser->id) {
    //             return response()->json([
    //                 'message' => "You can't add yourself as family member",
    //                 'status' => 'failed'
    //             ], 400);
    //         }

    //         // Existing relation check
    //         $existing = FamilyMember::where('user_id', $authUser->id)
    //                                 ->where('member_id', $targetUser->id)
    //                                 ->first();

    //         if ($existing) {

    //             // 👉 If pending or accepted → error
    //             if (in_array($existing->approval_status, ['pending','accepted'])) {
    //                 return response()->json([
    //                     'message' => 'Already requested or already family member',
    //                     'status' => 'failed'
    //                 ], 400);
    //             }

    //             // 👉 If rejected → delete old record
    //             if ($existing->approval_status == 'rejected') {
    //                 $existing->delete();
    //             }
    //         }

    //         // 👉 Create new request
    //         FamilyMember::create([
    //             'user_id' => $authUser->id,
    //             'member_id' => $targetUser->id,
    //             'approval_status' => 'pending',
    //         ]);

    //         $msg = "Family request sent to {$targetUser->first_name}";

    //         $this->notifyMessage(
    //             $authUser,
    //             $targetUser->id,
    //             $authUser->id,
    //             "invite"
    //         );

    //         return response()->json([
    //             'message' => $msg,
    //             'status' => 'success'
    //         ], 200);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'message' => 'Something went wrong!',
    //             'status' => 'failed'
    //         ], 400);
    //     }
    // }

    public function addFamily(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $authUser   = Auth::user();
            $targetUser = User::findOrFail($request->user_id);
            
            // 🚫 Self check
            if ($authUser->id == $targetUser->id) {
                return response()->json([
                    'message' => "You can't add yourself as family member",
                    'status' => 'failed'
                ], 400);
            }
            
            // 🔍 Check existing in BOTH directions
            // $existing = FamilyMember::where(function ($q) use ($authUser, $targetUser) {

            //     $q->where('user_id', $authUser->id)
            //     ->where('member_id', $targetUser->id);

            // })->orWhere(function ($q) use ($authUser, $targetUser) {

            //     $q->where('user_id', $targetUser->id)
            //     ->where('member_id', $authUser->id);

            // })->first();

            $existing = FamilyMember::where('user_id', $authUser->id)
                                    ->where('member_id', $targetUser->id)
                                    ->first();

            if ($existing) 
            {

                if (in_array($existing->approval_status, ['pending','accepted'])) {
                    return response()->json([
                        'message' => 'Already requested or already family member',
                        'status' => 'failed'
                    ], 400);
                }

                if ($existing->approval_status == 'rejected') {
                    $existing->delete();
                }
            }

            // ✅ Create new request (one way pending)
            $familyRequest = FamilyMember::create([
                'user_id' => $authUser->id,
                'member_id' => $targetUser->id,
                'approval_status' => 'pending',
            ]);

            // 🔔 Notification
            $this->notifyMessage(
                $authUser,
                $targetUser->id,
                $familyRequest->id,
                "invite"
            );

            return response()->json([
                'message' => "Family request sent to {$targetUser->first_name}",
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'status' => 'failed'
            ], 400);
        }
    }



    public function getMyFmailyList(Request $request)
    {
        try 
        {
            $limit  = max((int) $request->get('limit', 30), 1);
            $page   = max((int) $request->get('page', 1), 1);
            $offset = ($page - 1) * $limit;

            $search = $request->get('search');
            $status = $request->get('status'); // pending | accepted | null

            $authId = Auth::id();
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);

            $query = FamilyMember::where('user_id', $authId)
                ->whereNotIn('member_id', $blockedUserIds)
                ->whereHas('user') // prevent null users
                ->with('user:id,first_name,last_name,email,username,image');

            // ✅ STATUS FILTER
            if ($status) {
                if ($status === 'pending') {
                    $query->where('approval_status', 'pending');
                } elseif (in_array($status, ['accepted', 'approved'])) {
                    $query->where('approval_status', 'accepted');
                }
            } else {
                // 🔥 status not passed → show all
                $query->whereIn('approval_status', ['pending', 'accepted']);
            }

            // 🔍 SEARCH FILTER
            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('username', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            }

            $totalUsers = $query->count();

            $users = $query->orderBy('id', 'desc')
                           ->skip($offset)
                           ->take($limit)
                           ->get();


            $users = $users->map(function ($follow)
            {

                if (!$follow->user) {
                    return null;
                }

                $follower = $follow->user;
                

                return [
                    'id'            => $follow->id,
                    'member_id'     => $follower->id,
                    'first_name'    => $follower->first_name,
                    'last_name'     => $follower->last_name,
                    'email'         => $follower->email,
                    'username'      => $follower->username,
                    'image'         => $follower->image,
                    'approval_status' => $follow->approval_status,
                ];
            })->filter()->values();

            return response()->json([
                'status'  => 'success',
                'message' => 'Family members fetched successfully',
                'data'    => [
                    'count'       => $totalUsers,
                    'page'        => $page,
                    'limit'       => $limit,
                    'total_pages' => ceil($totalUsers / $limit),
                    'users'       => $users
                ]
            ], 200);

        } catch (\Exception $e) {
            // dd($e);
            return response()->json([
                'message' => 'Something went wrong!',
                'status'  => 'failed'
            ], 400);
        }
    }

    public function getOtherFamilyList(Request $request)
    {
        try {

            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $limit  = max((int) $request->get('limit', 30), 1);
            $page   = max((int) $request->get('page', 1), 1);
            $offset = ($page - 1) * $limit;

            $search = $request->get('search');
            $status = $request->get('status'); // pending | accepted

            $userData = User::where('id', $request->user_id)
                            ->whereNull('deleted_at')
                            ->first();

            if (!$userData) {
                return response()->json([
                    'message' => 'User not found or deleted',
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);

            // 🔥 Check once (avoid N+1)
            $isFamoryMember = FamilyMember::where('user_id', $userData->id)
                                        ->where('member_id', $authUser->id)
                                        ->whereIn('approval_status', ['accepted', 'pending'])
                                        ->exists();

            $query = FamilyMember::where('user_id', $userData->id)
                                ->whereNotIn('member_id', $blockedUserIds)
                                ->whereHas('user')
                                ->with('user:id,first_name,last_name,email,username,image');

            // ✅ STATUS FILTER
            if ($status) {
                if ($status === 'pending') {
                    $query->where('approval_status', 'pending');
                } elseif (in_array($status, ['accepted', 'approved'])) {
                    $query->where('approval_status', 'accepted');
                }
            } else {
                $query->whereIn('approval_status', ['pending', 'accepted']);
            }

            // 🔍 SEARCH FILTER
            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            }

            $totalUsers = $query->count();

            $users = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            $users = $users->map(function ($follow) use ($isFamoryMember) {

                if (!$follow->user) {
                    return null;
                }

                $follower = $follow->user;

                return [
                    'id'               => $follow->id,
                    'member_id'        => $follower->id,
                    'first_name'       => $follower->first_name,
                    'last_name'        => $follower->last_name,
                    'email'            => $follower->email,
                    'username'         => $follower->username,
                    'image'            => $follower->image,
                    'approval_status'  => $follow->approval_status,
                    'is_famory_member' => $isFamoryMember,
                ];

            })->filter()->values();

            return response()->json([
                'status'  => 'success',
                'message' => 'Get Other Family members fetched successfully',
                'data'    => [
                    'count'       => $totalUsers,
                    'page'        => $page,
                    'limit'       => $limit,
                    'total_pages' => ceil($totalUsers / $limit),
                    'users'       => $users
                ]
            ], 200);

        } catch (\Exception $e) {

            // dd($e);

            return response()->json([
                'message' => 'Something went wrong!',
                'status'  => 'failed'
            ], 500);
        }
    }


    public function getAddedMeFamilyList(Request $request)
    {
        try {
            $limit  = max((int) $request->get('limit', 30), 1);
            $page   = max((int) $request->get('page', 1), 1);
            $offset = ($page - 1) * $limit;

            $search = $request->get('search');
            $status = $request->get('status'); // pending | accepted | null

            $authId = Auth::id();
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);

            $query = FamilyMember::where('member_id', $authId)
                ->whereNotIn('user_id', $blockedUserIds)
                ->whereHas('member') // who added me
                ->with('member:id,first_name,last_name,email,username,image');

            // ✅ STATUS FILTER
            if ($status) {
                if ($status === 'pending') {
                    $query->where('approval_status', 'pending');
                } elseif (in_array($status, ['accepted', 'approved'])) {
                    $query->where('approval_status', 'accepted');
                }
            } else {
                // status not passed → all
                $query->whereIn('approval_status', ['pending', 'accepted']);
            }

            // 🔍 SEARCH FILTER
            if ($search) {
                $query->whereHas('member', function ($q) use ($search) {
                    $q->where(function ($qq) use ($search) {
                        $qq->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('username', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            }

            $totalUsers = $query->count();

            $users = $query->orderBy('id', 'desc')
                           ->skip($offset)
                           ->take($limit)
                           ->get();
            $users = $users->map(function ($follow)
            {

                if (!$follow->member) {
                    return null;
                }

                $follower = $follow->member;
                

                return [
                    'id'            => $follow->id,
                    'member_id'     => $follower->id,
                    'first_name'    => $follower->first_name,
                    'last_name'     => $follower->last_name,
                    'email'         => $follower->email,
                    'username'      => $follower->username,
                    'image'         => $follower->image,
                    'approval_status' => $follow->approval_status,
                ];
            })->filter()->values();

            return response()->json([
                'status'  => 'success',
                'message' => 'Family invitations fetched successfully',
                'data'    => [
                    'count'       => $totalUsers,
                    'page'        => $page,
                    'limit'       => $limit,
                    'total_pages' => ceil($totalUsers / $limit),
                    'users'       => $users
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'status'  => 'failed'
            ], 400);
        }
    }

    // public function respondFamilyRequest(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'request_id' => 'required|exists:family_members,id',
    //             'action'     => 'required|in:accepted,rejected',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'message' => $validator->errors()->first(),
    //                 'status'  => 'failed'
    //             ], 422);
    //         }

    //         $authUser = Auth::user();
    //         $authId   = $authUser->id;

    //         // 🔹 Request jo mujhe aayi hai
    //         $requestRow = FamilyMember::where('id', $request->request_id)
    //             ->where('member_id', $authId)
    //             ->where('approval_status', 'pending')
    //             ->first();

    //         if (!$requestRow) {
    //             return response()->json([
    //                 'message' => 'Invalid or already processed request',
    //                 'status'  => 'failed'
    //             ], 404);
    //         }

    //         $senderUser = User::find($requestRow->user_id); // jisne request bheji

    //         // ❌ REJECT
    //         if ($request->action === 'rejected') 
    //         {

    //             $requestRow->update(['approval_status' => 'rejected']);

    //             // 🔔 Notification → Sender
    //             if ($senderUser) {
    //                 $this->notifyMessage(
    //                     $authUser,                 // actor
    //                     $senderUser->id,           // receiver
    //                     $authId,                   // sender
    //                     'family_rejected'
    //                 );
    //             }

    //             return response()->json([
    //                 'message' => 'Family request rejected',
    //                 'status'  => 'success'
    //             ], 200);
    //         }

    //         // ✅ ACCEPT
    //         DB::transaction(function () use ($requestRow, $authId) {

    //             // 1️⃣ Update original request
    //             $requestRow->update(['approval_status' => 'accepted']);

    //             // // 2️⃣ Create reverse relation
    //             // FamilyMember::firstOrCreate([
    //             //     'user_id'   => $authId,
    //             //     'member_id' => $requestRow->user_id,
    //             // ], [
    //             //     'approval_status' => 'accepted'
    //             // ]);
    //         });

    //         // 🔔 Notification → Sender
    //         if ($senderUser) {
    //             $this->notifyMessage(
    //                 $authUser,                 // actor
    //                 $senderUser->id,           // receiver
    //                 $authId,                   // sender
    //                 'family_accepted'
    //             );
    //         }

    //         return response()->json([
    //             'message' => 'Family request accepted successfully',
    //             'status'  => 'success'
    //         ], 200);

    //     } catch (\Exception $e) {
    //         dd($e);
    //         return response()->json([
    //             'message' => 'Something went wrong!',
    //             'status'  => 'failed'
    //         ], 400);
    //     }
    // }

    public function respondFamilyRequest(Request $request)
    {
        try 
        {

            $validator = Validator::make($request->all(), [
                'request_id' => 'required|exists:family_members,id',
                'action'     => 'required|in:accepted,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 422);
            }

            $authUser = Auth::user();
            $authId   = $authUser->id;

            // 🔍 Only request which came to me and still pending
            $requestRow = FamilyMember::where('id', $request->request_id)
                ->where('member_id', $authId)
                ->where('approval_status', 'pending')
                ->first();

            if (!$requestRow) {
                return response()->json([
                    'message' => 'Invalid or already processed request',
                    'status'  => 'failed'
                ], 404);
            }

            $senderUser = User::find($requestRow->user_id);

            // ❌ REJECT FLOW
            if ($request->action === 'rejected') {

                $requestRow->update([
                    'approval_status' => 'rejected'
                ]);

                if ($senderUser) {
                    $this->notifyMessage(
                        $authUser,
                        $senderUser->id,
                        $authId,
                        'family_rejected'
                    );
                }

                return response()->json([
                    'message' => 'Family request rejected',
                    'status'  => 'success'
                ], 200);
            }

            // ✅ ACCEPT FLOW (2 WAY)
            DB::transaction(function () use ($requestRow, $authId) {

                // 1️⃣ Update sender → me
                $requestRow->update([
                    'approval_status' => 'accepted'
                ]);

                // 2️⃣ Create/update me → sender
                FamilyMember::updateOrCreate(
                    [
                        'user_id'   => $authId,
                        'member_id' => $requestRow->user_id,
                    ],
                    [
                        'approval_status' => 'accepted'
                    ]
                );

                // Automaticaly Follow
                $follow =   Follow::updateOrCreate(
                    [
                        'follower_id'   => $authId,
                        'following_id' => $requestRow->user_id,
                    ],
                    [
                        'status' => 'approved'
                    ]
                );
                //Automaticaly Following
                $following =   Follow::updateOrCreate(
                    [
                        'following_id'   => $authId,
                        'follower_id' => $requestRow->user_id,
                    ],
                    [
                        'status' => 'approved'
                    ]
                );
            });

            if ($senderUser) {
                $this->notifyMessage(
                    $authUser,
                    $senderUser->id,
                    $authId,
                    'family_accepted'
                );
            }

            $update_notification = Notification::where('item_id',$requestRow->id)
                                                ->where('receiver_id',$requestRow->member_id)
                                                ->where('has_actioned',0)
                                                ->first();
            if($update_notification)
            {
                $update_notification->has_actioned =1;
                $update_notification->save();
            }

            return response()->json([
                'message' => 'Family request accepted successfully',
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong!',
                'status'  => 'failed'
            ], 400);
        }
    }

    // public function removeFamily(Request $request)
    // {
    //     try {

    //         $validator = Validator::make($request->all(), [
    //             'user_id' => 'required|exists:users,id', // family member to remove
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'message' => $validator->errors()->first(),
    //                 'status'  => 'failed'
    //             ], 422);
    //         }

    //         $authId   = Auth::id();
    //         $targetId = $request->user_id;

    //         // 🚫 Self protection
    //         if ($authId == $targetId) {
    //             return response()->json([
    //                 'message' => "You can't remove yourself",
    //                 'status'  => 'failed'
    //             ], 400);
    //         }

    //         DB::transaction(function () use ($authId, $targetId) {

    //             // 🗑 Delete family relation BOTH ways
    //             FamilyMember::where(function ($q) use ($authId, $targetId) {

    //                 $q->where('user_id', $authId)
    //                   ->where('member_id', $targetId);

    //             })->orWhere(function ($q) use ($authId, $targetId) {

    //                 $q->where('user_id', $targetId)
    //                   ->where('member_id', $authId);

    //             })->delete();

                
    //         });

    //         return response()->json([
    //             'message' => 'Family member removed successfully',
    //             'status'  => 'success'
    //         ], 200);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'message' => 'Something went wrong!',
    //             'status'  => 'failed'
    //         ], 400);
    //     }
    // }

    public function removeFamily(Request $request)
    {
        try 
        {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 422);
            }

            $authId   = Auth::id();
            $targetId = $request->user_id;

            // 🚫 Self protection
            if ($authId == $targetId) {
                return response()->json([
                    'message' => "You can't remove yourself",
                    'status'  => 'failed'
                ], 400);
            }

            DB::transaction(function () use ($authId, $targetId) {

                // 🗑 Delete family relation BOTH ways
                FamilyMember::where(function ($q) use ($authId, $targetId) {

                    $q->where('user_id', $authId)
                      ->where('member_id', $targetId);

                })->orWhere(function ($q) use ($authId, $targetId) {

                    $q->where('user_id', $targetId)
                      ->where('member_id', $authId);

                })->delete();


                // 🗑 Delete follow relation BOTH ways
                Follow::where(function ($q) use ($authId, $targetId) {

                    $q->where('follower_id', $authId)
                      ->where('following_id', $targetId);

                })->orWhere(function ($q) use ($authId, $targetId) {

                    $q->where('follower_id', $targetId)
                      ->where('following_id', $authId);

                })->delete();

            });

            return response()->json([
                'message' => 'Family member and follow relation removed successfully',
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong!',
                'status'  => 'failed'
            ], 400);
        }
    }

    public function cancelFamilyRequest(Request $request)
    {
        try 
        {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 422);
            }

            $authId   = Auth::id();
            $targetId = $request->user_id;

            // 🚫 Self check
            if ($authId == $targetId) {
                return response()->json([
                    'message' => "Invalid request",
                    'status'  => 'failed'
                ], 400);
            }

            // 🔍 Find my sent & pending request
            $familyRequest = FamilyMember::where('user_id', $authId)
                ->where('member_id', $targetId)
                ->where('approval_status', 'pending')
                ->first();

            if (!$familyRequest) {
                return response()->json([
                    'message' => 'No pending request found',
                    'status'  => 'failed'
                ], 404);
            }

            // 🗑 Delete request
            $familyRequest->delete();

            $delete_notification = Notification::where('item_id',$familyRequest->id)->delete();

            return response()->json([
                'message' => 'Family request cancelled successfully',
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong!',
                'status'  => 'failed'
            ], 400);
        }
    }















}
