<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\AlbumUser;
use App\Models\AlbumPost;
use App\Models\BlockUser;
use App\Models\Follow;
use App\Models\User;
use App\Models\LegacyAlbum;
use App\Models\Notification;
use App\Models\LegacyAlbumPost;
use App\Models\LegacyAlbumPurchaseHistory;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\FormatResponseTrait;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
class AlbumMemberController extends Controller
{
    use FormatResponseTrait;
    use OneSignalTrait;

    public function getAlbumlist(Request $request)
    {
        try {
            $user       = Auth::user();
            $perPage    = (int) $request->input('per_page', 10);
            $album_type = $request->get('album_type'); // my, collaborator, viewer

            $query = Album::query();

            if ($album_type === 'collaborator' || $album_type === 'viewer') 
            {
                $query->select('albums.*')
                    ->join('album_users', 'album_users.album_id', '=', 'albums.id')
                    ->where('album_users.user_id', $user->id)
                    ->where('album_users.role', $album_type)
                    ->addSelect('album_users.approval_status'); // Add approval status
            } 
            else 
            {
                // My albums
                $query->where('albums.user_id', $user->id);
            }

            $albums = $query->where('isDefault',0)->withCount('posts')
                            ->orderBy('albums.created_at', 'asc')
                            ->paginate($perPage);

            return $this->successResponse(
                "Albums retrieved successfully",
                200,
                $albums->items(),
                $albums
            );

        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    

    public function addOrUpdateMember(Request $request)
    {
        try {
            $authUser = Auth::user();

            // Auth user check
            if (!$authUser) {
                return response()->json([
                    'message' => 'Unauthorized.',
                    'status'  => 'failed'
                ], 401);
            }

            // Decode members if sent as JSON string (form-data)
            if ($request->has('members') && is_string($request->members)) {
                $decodedMembers = json_decode($request->members, true);
                $request->merge(['members' => $decodedMembers]);
            }

            // Basic validation - avoid using whereNull('deleted_at') in the rule to prevent SQL error
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
                'members'  => 'required|array|min:1',
                'members.*.user_id' => 'required|integer',
                'members.*.role'    => 'required|in:collaborator,viewer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            // Get album (and check deleted_at only if column exists)
            $album = Album::where('id', $request->album_id)->first();

            if (!$album) {
                return response()->json([
                    'message' => 'Album not found.',
                    'status'  => 'failed'
                ], 404);
            }

            if ($album->isDefault == 1) 
            {
                return response()->json([
                    'message' => 'Default Album do not Add members',
                    'status'  => 'failed'
                ], 404);
            }

            // If albums table actually has deleted_at, ensure album is not soft-deleted
            if (Schema::hasColumn('albums', 'deleted_at') && $album->deleted_at !== null) {
                return response()->json([
                    'message' => 'Album not found or deleted.',
                    'status'  => 'failed'
                ], 404);
            }

            // Owner check
            if ($album->user_id !== $authUser->id) {
                return response()->json([
                    'message' => 'You are not the album owner. You cannot add or update members.',
                    'status'  => 'failed'
                ], 403);
            }

            $added = [];
            $updated = [];

            // Validate member users existence and deleted_at (if present)
            foreach ($request->members as $member) {
                $memberUserId = data_get($member, 'user_id');

                if ($memberUserId == $authUser->id) {
                    return response()->json([
                        'message' => 'You cannot add yourself as a member of your own album.',
                        'status'  => 'failed'
                    ], 400);
                }

                // check user exists
                $userQuery = User::where('id', $memberUserId);

                // if users table has deleted_at, ensure user is not soft-deleted
                if (Schema::hasColumn('users', 'deleted_at')) {
                    $userQuery->whereNull('deleted_at');
                }

                $memberUser = $userQuery->first();

                if (!$memberUser) {
                    return response()->json([
                        'message' => "Member user with id {$memberUserId} not found or inactive.",
                        'status'  => 'failed'
                    ], 400);
                }
            }

            // All members validated; now insert/update
            foreach ($request->members as $member) {
                $memberUserId = $member['user_id'];
                $role = $member['role'];

                $existing = AlbumUser::where('album_id', $album->id)
                    ->where('user_id', $memberUserId)
                    ->first();

                    if ($existing) 
                    {

                        $existing->role = $role;

                        // ✅ If previously rejected, move back to pending
                        if ($existing->approval_status === 'rejected') {
                            $existing->approval_status = 'pending';

                            if($role === 'collaborator')
                            {
                            $message = "$authUser->first_name has requested to add you as a collaborator to an $album->album_name album";
                            $notifType = 'album_collaborator_request';

                            }else{
                            $message = "$authUser->first_name has requested to add you as a viewer to an $album->album_name album";
                            $notifType = 'album_viewer_request';

                            }

                            $this->notifyMessage(
                                $authUser,
                                $memberUserId,
                                $album->id,
                                $notifType,null, null,null,$message
                            );
                        }

                        $existing->save();
                        $updated[] = $memberUserId;

                    } else {

                        AlbumUser::create([
                            'album_id' => $album->id,
                            'user_id'  => $memberUserId,
                            'role'     => $role,
                            'approval_status' => 'pending',
                        ]);

                        $added[] = $memberUserId;

                        // $notifType = $role === 'collaborator'
                        // ? 'album_collaborator_request'
                        // : 'album_viewer_request';
                        if($role === 'collaborator')
                        {
                         $message = "$authUser->first_name has requested to add you as a collaborator to an $album->album_name.";
                         $notifType = 'album_collaborator_request';

                        }else{
                            $message = "$authUser->first_name has requested to add you as a viewer to an $album->album_name.";
                            $notifType = 'album_viewer_request';

                        }


                        $this->notifyMessage(
                            $authUser,
                            $memberUserId,
                            $album->id,
                            $notifType,null, null,null,$message
                        );
                    }

                // if ($existing) {
                //     $existing->role = $role;
                //     $existing->save();
                //     $updated[] = $memberUserId;
                // } else {
                //     AlbumUser::create([
                //         'album_id' => $album->id,
                //         'user_id'  => $memberUserId,
                //         'role'     => $role,
                //         'approval_status'     => 'pending',
                //     ]);
                //     $added[] = $memberUserId;

                //     $notifType = $role === 'collaborator'? 'album_collaborator_request': 'album_viewer_request';
                //     $this->notifyMessage($authUser,$memberUserId,$album->id,$notifType);
                // }
            }

            return response()->json([
                'message' => 'Album members added/updated successfully!',
                'status'  => 'success',
                'data'    => [
                    'album_id' => $album->id,
                    'added_user_ids' => $added,
                    'updated_user_ids' => $updated,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong! ' . $e->getMessage(),
                'status' => 'failed'
            ], 500);
        }
    }

    public function approveOrRejectMember(Request $request)
    {
        try 
        {
            $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
                'status'   => 'required|in:accepted,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            // Fetch AlbumUser record
            $record = AlbumUser::where('album_id', $request->album_id)
                ->where('user_id', $authUser->id)
                ->first();

            if (!$record) {
                return response()->json([
                    'message' => 'You are not added to this album.',
                    'status' => 'failed'
                ], 400);
            }

            if ($record->approval_status !== 'pending') {
                return response()->json([
                    'message' => 'Request already resolved.',
                    'status' => 'failed'
                ], 400);
            }

            DB::beginTransaction();

            // Update approval status
            $record->approval_status = $request->status;
            $record->save();

            // Notify the owner
            $album = Album::find($request->album_id);
            $ownerId = $album->user_id;

            $notifType = $request->status === 'accepted'? 'album_member_approved':'album_member_rejected';

            $update_notification = Notification::where('item_id',$record->album_id)
                                                ->where('receiver_id',$record->user_id)
                                                ->where('has_actioned',0)
                                                ->first();
            if($update_notification)
            {
                $update_notification->has_actioned =1;
                $update_notification->save();
            }

            $this->notifyMessage($authUser, $ownerId, $album->id, $notifType);
            DB::commit();
         

            return response()->json([
                'message' => "Request {$request->status} successfully.",
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong! '.$e->getMessage(),
                'status' => 'failed'
            ], 500);
        }
    }

    public function removeMember(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
                'user_id'  => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            $album = Album::find($request->album_id);

            // Owner check
            if ($album->user_id !== $authUser->id) {
                return response()->json([
                    'message' => 'Only the album owner can remove members.',
                    'status' => 'failed'
                ], 403);
            }

            $albumUser = AlbumUser::where('album_id', $request->album_id)
                                ->where('user_id', $request->user_id)
                                ->first();

            if (!$albumUser) {
                return response()->json([
                    'message' => 'User is not a member of this album.',
                    'status' => 'failed'
                ], 400);
            }

            // Delete the member
            $albumUser->delete();

            // Notify removed user
            $this->notifyMessage(
                $authUser,                 // sender (album owner)
                $request->user_id,         // receiver (removed user)
                $album->id,                // album_id
                'remove_album'             // notification type
            );

            return response()->json([
                'message' => 'Album member removed successfully!',
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong! '.$e->getMessage(),
                'status' => 'failed'
            ], 500);
        }
    }

    public function listMembers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
                'search'   => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $album = Album::findOrFail($request->album_id);

            // Only owner can see member list (optional rule)
            if ($album->user_id != $authUser->id) {
                return response()->json([
                    'message' => 'You are not the Album Owner, so you cannot view members.',
                    'status'  => 'failed'
                ], 403);
            }

            // Pagination parameters
            $limit  = (int) $request->get('limit', 30);
            $page   = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $search = $request->get('search');

            // Base query
            $query = AlbumUser::where('album_id', $album->id)
                ->with('user:id,first_name,last_name,email,username,image');

            // Search by user fields
            if (!empty($search)) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $totalMembers = $query->count();

            // Paginated results
            $albumMembers = $query->orderBy('id', 'desc')
                                  ->skip($offset)
                                  ->take($limit)
                                  ->get();

            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

            // Format data like follower list
            $members = $albumMembers->map(function ($member) use ($s3BaseUrl) {
                $user = $member->user;
                return [
                    'id'            => $member->id,
                    'user_id'       => $user->id,
                    'first_name'    => $user->first_name,
                    'last_name'     => $user->last_name,
                    'email'         => $user->email,
                    'username'      => $user->username,
                    // 'image'         => $user->image ? $s3BaseUrl . $user->image : null,
                    'image'         => $user->image ?  $user->image : null,
                    'role'          => $member->role, // collaborator/viewer
                    'approval_status'=> $member->approval_status,
                ];
            });

            // Paginated meta response
            $data = [
                'album_id'     => (int) $album->id,
                'count'        => $totalMembers,
                'page'         => $page,
                'limit'        => $limit,
                'total_pages'  => ceil($totalMembers / $limit),
                'members'      => $members,
            ];

            return response()->json([
                'message' => 'Album members fetched successfully',
                'status'  => 'success',
                'data'    => $data
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

    public function followersUserList(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $limit = (int) $request->get('limit', 30);
            $page = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $search = $request->get('search');
            

            $authId = Auth::id();
            $authUser = Auth::user();
            $album = Album::findOrFail($request->album_id);

            // Only owner can see member list (optional rule)
            if ($album->user_id != $authUser->id) {
                return response()->json([
                    'message' => 'You are not the Album Owner, so you cannot view members.',
                    'status'  => 'failed'
                ], 403);
            }
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            $albumMemberIds = AlbumUser::where('album_id', $request->album_id)
                                        ->whereNotIn('approval_status',['pending', 'accepted'])
                                        ->pluck('user_id')
                                        ->toArray();

            $notgetUserIds = array_unique(array_merge($blockedUserIds,$albumMemberIds));

            $get_follower_user_id = $authId;

            $query = Follow::where('following_id', $get_follower_user_id)
                ->where('status', 'approved')
                ->whereNotIn('follower_id', $notgetUserIds)
                ->with('follower:id,first_name,last_name,email,username,image');

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

            // Collect all follower user IDs
            $followerIds = $followers->pluck('follower.id')->filter()->all();

            // Fetch relations in one query
            $relations = Follow::where('follower_id', $authId)
                ->whereIn('following_id', $followerIds)
                ->pluck('status', 'following_id'); // key = following_id, value = status

            

            $users = $followers->map(function ($follow) use ($relations) {
                $follower = $follow->follower;
                $status = $relations[$follower->id] ?? null;

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
                    'user_id'       => $follower->id,
                    'first_name'    => $follower->first_name,
                    'last_name'     => $follower->last_name,
                    'email'         => $follower->email,
                    'username'      => $follower->username,
                    // 'image'         => $follower->image ? $s3BaseUrl . $follower->image : null,
                    'image'         => $follower->image ? $follower->image : null,
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

    public function leaveLeave(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            // Check membership
            $albumUser = AlbumUser::where('album_id', $request->album_id)
                                ->where('user_id', $authUser->id)
                                ->first();

            if (!$albumUser) {
                return response()->json([
                    'message' => "You are not a member of this album",
                    'status'  => 'failed'
                ], 400);
            }

            // Get album
            $album = Album::find($request->album_id);

            // Delete user membership
            $albumUser->delete();

            // Send notification to album owner
            $this->notifyMessage(
                $authUser,           // sender (user leaving)
                $album->user_id,     // receiver (album owner)
                $album->id,          // album id
                'leave_album'        // notification type
            );

            return response()->json([
                'message' => "You have successfully left the album",
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }
    
    public function getLegacyAlbumlist(Request $request)
    {
        try 
        {
            $limit  = (int) $request->get('limit', 30);
            $page   = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $search = $request->get('search');
            $legacy_type = $request->get('legacy_type');

            $validator = Validator::make($request->all(), [
                'legacy_type' => 'required|in:shared,my',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $remaining_lagecy_count   = $authUser->remaining_lagecy_count;
            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

            // =========================================================
            //                     SHARED WITH ME
            // =========================================================
            if ($legacy_type == 'shared') {

                $query = LegacyAlbum::select('id','title','conver_image','user_id')
                    ->where('shared_with_id', $authUser->id)
                    ->where('type','legacy')
                    ->where('is_deleted',0)
                    ->withCount('posts')
                    ->with([
                        'owner:id,first_name,is_dead,image'
                    ]);

                if (!empty($search)) {
                    $query->where('title', 'like', "%{$search}%");
                }

                $total = $query->count();

                $albums = $query->orderBy('id','desc')
                    ->skip($offset)
                    ->take($limit)
                    ->get()
                    ->map(function($album) use ($s3BaseUrl){
                        return [
                            'album_id'      => $album->id,
                            'title'         => $album->title,
                            // 'conver_image'  => $album->conver_image ? $s3BaseUrl . $album->conver_image : null,
                            'conver_image'  => $album->conver_image ? $album->conver_image : null,
                            'posts_count'   => $album->posts_count,
                            'owner_id'      => $album->owner->id ?? null,
                            'owner_name'    => $album->owner->first_name ?? '',
                            'is_dead'       => $album->owner->is_dead ? true : false,
                            // 'owner_image'   => !empty($album->owner->image) ? $s3BaseUrl . $album->owner->image : null,
                            'owner_image'   => !empty($album->owner->image) ? $album->owner->image : null,
                        ];
                    });

                $msg = "Shared Legacy Album List";
            } 

            // =========================================================
            //                      MY ALBUMS
            // =========================================================
            else {

                $query = LegacyAlbum::select('id','title','conver_image','shared_with_id')
                    ->where('user_id', $authUser->id)
                    ->where('is_deleted',0)
                    ->where('type','legacy')
                    ->withCount('posts')
                    ->with([
                        'sharedWith:id,first_name,is_dead,image'
                    ]);

                if (!empty($search)) {
                    $query->where('title', 'like', "%{$search}%");
                }

                $total = $query->count();

                $albums = $query->orderBy('id','desc')
                    ->skip($offset)
                    ->take($limit)
                    ->get()
                    ->map(function($album) use ($s3BaseUrl){

                        // Default values
                        $sharedImage = null;
                        $sharedName  = null;

                        if ($album->sharedWith) {

                            // If many shared users (collection)
                            if ($album->sharedWith instanceof \Illuminate\Support\Collection) {
                                $first = $album->sharedWith->first();
                                if ($first) {
                                    // $sharedImage = $first->image ? $s3BaseUrl . $first->image : null;
                                    $sharedImage = $first->image ?  $first->image : null;
                                    $sharedName  = $first->first_name ?? '';
                                }
                            }
                            // If only one shared user (belongsTo)
                            else {
                                // $sharedImage = $album->sharedWith->image ? $s3BaseUrl . $album->sharedWith->image : null;
                                $sharedImage = $album->sharedWith->image ? $album->sharedWith->image : null;

                                $sharedName  = $album->sharedWith->first_name ?? '';
                            }
                        }

                        return [
                            'album_id'          => $album->id,
                            'title'             => $album->title,
                            // 'conver_image'      => $album->conver_image ? $s3BaseUrl . $album->conver_image : null,
                            'conver_image'      => $album->conver_image ? $album->conver_image : null,
                            'posts_count'       => $album->posts_count,
                            'is_dead'           => false,  // creator is current user
                            'shared_user_name'  => $sharedName,
                            'shared_user_image' => $sharedImage,
                        ];
                    });

                $msg = "My Legacy Album List";
            }

            // =========================================================
            //                 FINAL RESPONSE FORMAT
            // =========================================================

            $data = [
                'remaining_lagecy_count'       => $remaining_lagecy_count,
                'count'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),
                'albums'      => $albums
            ];

            return response()->json([
                'message' => $msg,
                'status'  => "success",
                'data'    => $data
            ], 200);

        } 
        catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }



    public function getLegacyAlbumPostlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legacy_album_id' => 'required',
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
        }

        try 
        {
            $authUser = Auth::user();
            $getLegacyAlbum = LegacyAlbum::where('id',$request->legacy_album_id)->where('is_deleted',0)->first();
            if(empty($getLegacyAlbum))
            {
                 return response()->json(['message' =>"Legacy Album not found", 'status' => 'failed'], 400);
            }

            $get_legacy_postIds = LegacyAlbumPost::where('legacy_album_id',$getLegacyAlbum->id)
                                                 ->pluck('post_id')
                                                 ->toArray();
            $post = Post::withCount('like','comments')->whereIn('id',$get_legacy_postIds)->get();


            return response()->json([
                "message" => "Legacy Album Post List",
                "status"  => "success",
                "data"    => $post
            ], 200);
            
        } 
        catch (Exception $e) 
        {
            return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }

    public function buyLegacyAlbum(Request $request)
    {
        DB::beginTransaction();

        try {

            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'package_name' => 'required|string',
                'album_count'  => 'required|integer|min:1',
                'amount'       => 'required|numeric',
                'date'         => 'required',
                'status'       => 'required|string',
                'payment_id'   => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            // ✅ Convert date (dd-mm-yyyy -> yyyy-mm-dd)
            try {
                $formattedDate = Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Invalid date format. Use DD-MM-YYYY',
                    'status' => 'failed'
                ], 400);
            }

            // ✅ Total albums already used
            $total_used_album_count = LegacyAlbum::where('user_id', $authUser->id)->count();

            // ✅ Save purchase history
            $createPurchase = [
                'user_id'      => $authUser->id,
                'album_count' => $request->album_count,
                'package_name'=> $request->package_name,
                'amount'      => $request->amount,
                'date'        => $formattedDate,
                'status'      => $request->status,
                'payment_id'  => $request->payment_id,
            ];

            LegacyAlbumPurchaseHistory::create($createPurchase);

            // ✅ Total purchased albums
            $get_total_purchase_count = LegacyAlbumPurchaseHistory::where('user_id', $authUser->id)
                                            ->sum('album_count');

            // ✅ Remaining credits
            $remaining_lagecy_count = $get_total_purchase_count - $total_used_album_count;

            if ($remaining_lagecy_count < 0) {
                $remaining_lagecy_count = 0;
            }

            // ✅ Update user remaining count (NOT +=)
            $authUser->remaining_lagecy_count = $remaining_lagecy_count;
            $authUser->save();

            DB::commit();

            return response()->json([
                'message' => "Legacy Album Package Purchased Successfully",
                'status'  => 'success',
                'data'    => [
                    'remaining_lagecy_count' => $remaining_lagecy_count,
                    'total_purchase_count'  => $get_total_purchase_count,
                    'used_album_count'      => $total_used_album_count
                ]
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function legacyAlbumBuyHistory(Request $request)
    {
        try {

            $authUser = Auth::user();

            // ✅ Get purchase history (latest first)
            $history = LegacyAlbumPurchaseHistory::where('user_id', $authUser->id)
                        ->orderBy('id', 'desc')
                        ->get([
                            'id',
                            'package_name',
                            'album_count',
                            'amount',
                            'date',
                            'status',
                            'payment_id',
                            'created_at'
                        ]);

            // ✅ Total purchased albums
            $total_purchase_count = LegacyAlbumPurchaseHistory::where('user_id', $authUser->id)
                                        ->sum('album_count');

            // ✅ Total used albums
            $total_used_album_count = LegacyAlbum::where('user_id', $authUser->id)->count();

            // ✅ Remaining albums
            $remaining_lagecy_count = $total_purchase_count - $total_used_album_count;

            if ($remaining_lagecy_count < 0) {
                $remaining_lagecy_count = 0;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Legacy album purchase history fetched successfully',
                'summary' => [
                    'total_purchased' => $total_purchase_count,
                    'total_used' => $total_used_album_count,
                    'remaining' => $remaining_lagecy_count,
                ],
                'data' => $history
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong! '.$e->getMessage()
            ], 500);
        }
    }

    public function deleteLegacyAlbum(Request $request)
    {
        DB::beginTransaction();

        try {

            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'legacy_album_id' => 'required|integer|exists:legacy_albums,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            // ✅ Fetch album (owned by user & not deleted)
            $album = LegacyAlbum::where('id', $request->legacy_album_id)
                        ->where('user_id', $authUser->id)
                        ->where('is_deleted', 0)
                        ->first();

            if (!$album) {
                return response()->json([
                    'message' => "Album not found or already deleted",
                    'status'  => 'failed'
                ], 404);
            }

            // ✅ Soft delete (mark as deleted)
            $album->is_deleted = 1;
            $album->save();

            DB::commit();

            return response()->json([
                'message' => "Legacy Album deleted successfully",
                'status'  => 'success',
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }



}
