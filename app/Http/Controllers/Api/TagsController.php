<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\FormatResponseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use App\Services\UploadImage;

use App\Models\User;
use App\Models\FamilyTagId;
use App\Models\TagUser;
use App\Models\Post;
use App\Models\SavedTag;
use App\Models\Follow;

use SimpleSoftwareIO\QrCode\Facades\QrCode;


class TagsController extends Controller
{
    use OneSignalTrait;
    use FormatResponseTrait;
    protected $UploadImage;

    public function __construct(UploadImage $UploadImage)
    {
        $this->UploadImage = $UploadImage;
    }

    // public function index(Request $request)
    // {
    //     try 
    //     {
    //         $authUser = Auth::user();
    //         $limit    = (int) $request->get('limit', 10); 
    //         $page     = (int) $request->get('page', 1); 
    //         $offset   = ($page - 1) * $limit;
    //         $tag_type = $request->get('tag_type'); // my, collaborator, viewer

    //         $query = FamilyTagId::query();

    //         /** ----------------------------------------------------
    //          * 1. IF FILTER = collaborator / viewer → access tags
    //          * --------------------------------------------------- */
    //         if ($tag_type === 'collaborator' || $tag_type === 'viewer') 
    //         {
    //             $query->select('family_tag_ids.*')
    //                 ->join('tag_users', 'tag_users.tag_id', '=', 'family_tag_ids.id')
    //                 ->where('tag_users.user_id', $authUser->id)
    //                 ->where('tag_users.role', $tag_type)
    //                 ->where('tag_users.approval_status', 'accepted');

    //             $myOwnedTagIds = [];
    //         } 
    //         else 
    //         {
    //             /** ----------------------------------------------------
    //              * 2. MY OWN TAGS
    //              * --------------------------------------------------- */
    //             $query->where('created_user_id', $authUser->id);

    //             $myOwnedTagIds = FamilyTagId::where('created_user_id', $authUser->id)
    //                 ->pluck('id')
    //                 ->toArray();
    //         }

    //         /** ----------------------------------------------------
    //          * PAGINATION
    //          * --------------------------------------------------- */
    //         $total = $query->count();

    //         $tags = $query->orderBy('family_tag_ids.id', 'DESC')
    //             ->skip($offset)
    //             ->take($limit)
    //             ->get();

    //         /** ----------------------------------------------------
    //          * 3. LATEST INVITES RECEIVED (This user invited)
    //          * --------------------------------------------------- */
    //         $latest_invitations_received = TagUser::select('id','tag_id','role','user_id','approval_status','created_at')
    //             ->with('tags:id,family_tag_id,title,image','user:id,first_name,last_name,email,username,image','tagOwner')
    //             ->where('user_id', $authUser->id)
    //             ->where('approval_status', 'pending')
    //             ->orderBy('id','DESC')
    //             ->take(8)
    //             ->get();

    //         /** ----------------------------------------------------
    //          * 4. LATEST REQUESTS SENT BY ME (My Tags - User Request List)
    //          * --------------------------------------------------- */
    //         $latest_requests_to_my_tags = TagUser::select('id','tag_id','role','user_id','approval_status','created_at')
    //             ->with('tags:id,family_tag_id,title,image',
    //                    'user:id,first_name,last_name,email,username,image')
    //             ->whereIn('tag_id', $myOwnedTagIds)
    //             ->where('approval_status', 'pending')
    //             ->orderBy('id','DESC')
    //             ->take(8)
    //             ->get();

    //         /** ----------------------------------------------------
    //          * 5. SAVED TAGS
    //          * --------------------------------------------------- */
    //         $my_saved_tag = SavedTag::select('id','tag_id','created_at')
    //             ->with('tagData:id,family_tag_id,title,image')
    //             ->where('user_id',$authUser->id)
    //             ->orderBy('id','DESC')
    //             ->take(8)
    //             ->get();

    //         /** ----------------------------------------------------
    //          * FINAL RESPONSE
    //          * --------------------------------------------------- */
    //         $data = [
    //             'count'       => $total,
    //             'page'        => $page,
    //             'limit'       => $limit,
    //             'total_pages' => ceil($total / $limit),

    //             'tags'                     => $tags,
    //             'latest_requests_to_my_tags'  => $latest_requests_to_my_tags,
    //             'latest_invitations_received' => $latest_invitations_received,
    //             'my_saved_tag'                => $my_saved_tag,
    //         ];

    //         return response()->json([
    //             'message' => 'My Tags List successfully',
    //             'status'  => 'success',
    //             'data'    => $data
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => "Something Went Wrong! " . $e->getMessage(),
    //             'status'  => 'failed'
    //         ], 400);
    //     }
    // }

    public function index(Request $request)
    {
        try 
        {
            $authUser = Auth::user();
            $limit    = (int) $request->get('limit', 10); 
            $page     = (int) $request->get('page', 1); 
            $offset   = ($page - 1) * $limit;
            $tag_type = $request->get('tag_type'); // my, collaborator, viewer

            $query = FamilyTagId::query();

            /** ----------------------------------------------------
             * 1. collaborator/viewer = show shared tags
             * --------------------------------------------------- */
            if ($tag_type === 'collaborator' || $tag_type === 'viewer') 
            {
                $query->select('family_tag_ids.*')
                    ->join('tag_users', 'tag_users.tag_id', '=', 'family_tag_ids.id')
                    ->where('tag_users.user_id', $authUser->id)
                    ->where('tag_users.role', $tag_type)
                    ->where('tag_users.approval_status', 'accepted');

                $myOwnedTagIds = [];

                // Since NOT owner → return empty for these two sections
                $showMyRequests = false;
            } 
            else 
            {
                /** ----------------------------------------------------
                 * 2. MY OWN TAGS
                 * --------------------------------------------------- */
                $query->where('created_user_id', $authUser->id);

                $myOwnedTagIds = FamilyTagId::where('created_user_id', $authUser->id)
                    ->pluck('id')
                    ->toArray();

                $showMyRequests = true;  // Owner → show these sections
            }

            /** ----------------------------------------------------
             * PAGINATION
             * --------------------------------------------------- */
            $total = $query->count();

            $tags = $query->orderBy('family_tag_ids.id', 'DESC')
                            ->skip($offset)
                            ->take($limit)
                            ->get();

            /** ----------------------------------------------------
             * 3. LATEST INVITATIONS RECEIVED  (only when MY TAGS)
             * --------------------------------------------------- */
            $latest_invitations_received = $showMyRequests
                ? TagUser::select('id','tag_id','role','user_id','approval_status','created_at')
                    ->with(
                        'tags:id,family_tag_id,title,image',
                        'user:id,first_name,last_name,email,username,image',
                        'tagOwner'
                    )
                    ->where('user_id', $authUser->id)
                    ->where('approval_status', 'pending')
                    ->orderBy('id','DESC')
                    ->take(8)
                    ->get()
                : [];

            /** ----------------------------------------------------
             * 4. LATEST REQUESTS SENT TO MY TAGS (only when MY TAGS)
             * --------------------------------------------------- */
            $latest_requests_to_my_tags = $showMyRequests
                ? TagUser::select('id','tag_id','role','user_id','approval_status','created_at')
                    ->with(
                        'tags:id,family_tag_id,title,image',
                        'user:id,first_name,last_name,email,username,image'
                    )
                    ->whereIn('tag_id', $myOwnedTagIds)
                    ->where('approval_status', 'pending')
                    ->orderBy('id','DESC')
                    ->take(8)
                    ->get()
                : [];

            /** ----------------------------------------------------
             * 5. SAVED TAGS (always)
             * --------------------------------------------------- */
            $my_saved_tag = SavedTag::select('id','tag_id','created_at')
                ->with('tagData:id,family_tag_id,title,image')
                ->where('user_id',$authUser->id)
                ->orderBy('id','DESC')
                ->take(8)
                ->get();

            /** ----------------------------------------------------
             * FINAL RESPONSE
             * --------------------------------------------------- */
            $data = [
                'count'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),

                'tags'                         => $tags,
                'latest_requests_to_my_tags'   => $latest_requests_to_my_tags,
                'latest_invitations_received'  => $latest_invitations_received,
                'my_saved_tag'                 => $my_saved_tag,
            ];

            return response()->json([
                'message' => 'My Tags List successfully',
                'status'  => 'success',
                'data'    => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }



    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'title'       => 'required|string|max:250',
                'description' => 'required|string',
                'privacy_type'=> 'required|in:Public,Private',
                'image'       => 'required|file|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $userId   = $authUser->id;

            DB::beginTransaction();

            // Upload image
            if (!$request->hasFile('image') || !$request->file('image')->isValid()) 
            {
                return response()->json([
                    'message' => 'Invalid image upload.',
                    'status'  => 'failed'
                ], 400);
            }

            $checkName = FamilyTagId::where('title',$request->title)->where('user_id',$userId)->first();
            if($checkName)
            {
                return response()->json([
                    'message' => 'Tag Name already Exsit.',
                    'status'  => 'failed'
                ], 400);
            }

            $file = $request->file('image');
            $filePath = $this->UploadImage->saveMedia($file, $userId);

            // Generate FamilyTag ID
            $family_tag_id = $this->generateFamilyTagId();

            // Create DB row
            $createData = FamilyTagId::create([
                'title'            => $request->title,
                'description'      => $request->description,
                'privacy_type'     => $request->privacy_type,
                'family_tag_id'    => $family_tag_id,
                'user_id'          => $userId,
                'created_user_id'  => $userId,
                'image'            => $filePath,
            ]);
            DB::commit();

            return response()->json([
                'message' => 'Tags Created Successfully',
                'status'  => 'success',
                'data'    => $createData
            ], 200);

        } catch (\Exception $exception) {

            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try 
        {

            $validator = Validator::make($request->all(), [
                'id'          => 'required',
                'title'       => 'required|string|max:250',
                'description' => 'required|string',
                'privacy_type'=> 'required|in:Public,Private',
                'image'       => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $userId = Auth::id();

            DB::beginTransaction();

            // Fetch record
            $getData = FamilyTagId::where('id', $request->id)
                                  ->where('user_id', $userId)
                                  ->first();

            if (!$getData) {
                return response()->json([
                    'message' => 'Tag Details Not Found',
                    'status'  => 'failed'
                ], 400);
            }

            // Check duplicate name
            $checkName = FamilyTagId::where('title', $request->title)
                                    ->where('user_id', $userId)
                                    ->where('id', '!=', $getData->id)
                                    ->first();

            if ($checkName) {
                return response()->json([
                    'message' => 'Tag Name already exists.',
                    'status'  => 'failed'
                ], 400);
            }

            // Handle image
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                if (!$file->isValid()) {
                    return response()->json([
                        'message' => 'Invalid image upload.',
                        'status'  => 'failed'
                    ], 400);
                }

                $filePath = $this->UploadImage->saveMedia($file, $userId);

            } else {
                $filePath = $getData->image; // keep old image
            }

            // Update data
            $getData->update([
                'title'        => $request->title,
                'description'  => $request->description,
                'privacy_type' => $request->privacy_type,
                'image'        => $filePath,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Tags updated Successfully',
                'status'  => 'success',
                'data'    => $getData
            ], 200);

        } catch (\Exception $exception) {

            DB::rollBack();

            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function view(Request $request, $id)
    {
        try 
        {
            $userId = Auth::id();

            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

            // Fetch tag with creator
            $get_tag_data = FamilyTagId::with('createdUser:id,first_name,last_name,image')
                                       ->where('id', $id)
                                       ->first();

            if (!$get_tag_data) {
                return response()->json([
                    'message' => 'Tags Details not found',
                    'status'  => 'failed'
                ], 404);
            }

            if ($get_tag_data->created_user_id != $userId) {
                return response()->json([
                    'message' => 'You cannot access tag Details',
                    'status' => 'failed'
                ], 403);
            }

            
            // Apply S3 URL for tag image
            // if ($get_tag_data->image) {
            //     $get_tag_data->image_url = rtrim($s3BaseUrl, '/') . '/' . ltrim($get_tag_data->image, '/');
            // }
            // $get_tag_data->makeHidden(['image','avatar']);


            // Fetch tag users
            $tag_user_list = TagUser::with('user:id,first_name,last_name,email,username,image')
                                    ->where('tag_id', $get_tag_data->id)
                                    ->orderBy('id','DESC')
                                    ->where('approval_status','accepted')
                                    ->get();

            // Format tag users
            $tag_users = $tag_user_list->map(function ($member) use ($s3BaseUrl) {
                $user = $member->user;

                return [
                    'id'             => $member->id,
                    'user_id'        => $user->id,
                    'first_name'     => $user->first_name,
                    'last_name'      => $user->last_name,
                    'email'          => $user->email,
                    'username'       => $user->username,
                    'image'          => $user->image ? $user->image : null,
                    'role'           => $member->role,
                    'approval_status'=> $member->approval_status,
                ];
            });

            $get_tag_data['tag_user'] = $tag_users;
            $posts = Post::with('user')
                         ->withCount('like','comments')
                         ->where('tag_id',$get_tag_data->id)
                         ->orderBy('id','DESC')
                         ->get();

            $get_tag_data['tag_post'] = $posts;

            return response()->json([
                'message' => 'Tags fetched successfully',
                'status'  => 'success',
                'data'    => $get_tag_data
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function addOrUpdateTagMember(Request $request)
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
                'tag_id' => 'required',
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

            
            $tag = FamilyTagId::where('id', $request->tag_id)
                                ->where('created_user_id',$authUser->id)
                                ->first();

            if (!$tag) {
                return response()->json([
                    'message' => 'Tag not found.',
                    'status'  => 'failed'
                ], 404);
            }

            // Owner check
            if ($tag->created_user_id !== $authUser->id) {
                return response()->json([
                    'message' => 'You are not the tag owner. You cannot add or update members.',
                    'status'  => 'failed'
                ], 403);
            }

            $added = [];
            $updated = [];
            DB::beginTransaction();

            // Validate member users existence and deleted_at (if present)
            foreach ($request->members as $member) {
                $memberUserId = data_get($member, 'user_id');

                if ($memberUserId == $authUser->id) {
                    return response()->json([
                        'message' => 'You cannot add yourself as a member of your own Tag.',
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

                $existing = TagUser::where('tag_id', $tag->id)
                                    ->where('user_id', $memberUserId)
                                    ->first();

                if ($existing) {
                    $existing->role = $role;
                    $existing->save();
                    $updated[] = $memberUserId;
                } else {
                    TagUser::create([
                        'tag_id'           => $tag->id,
                        'user_id'          => $memberUserId,
                        'role'             => $role,
                        'invited_by'       => $authUser->id,
                        'approval_status'  => 'pending',
                    ]);
                    $added[] = $memberUserId;

                    $notifType = $role === 'collaborator'? 'tag_collaborator_request': 'tag_viewer_request';
                    // $this->notifyMessage($authUser,$memberUserId,$tag->id,$notifType);
                    if($notifType == 'tag_collaborator_request')
                    {
                     $message = "$authUser->first_name has requested to add you as a collaborator to $tag->title tag";
                    }else{
                     $message = "$authUser->first_name has requested to add you as a viewer to $tag->title tag";
                    }
                    
                    $this->notifyMessage($authUser, $memberUserId, $tag->id, $notifType, null, null,null,$message);
                }
            }
            DB::commit();

            return response()->json([
                'message' => 'Tag members added/updated successfully!',
                'status'  => 'success',
                'data'    => [
                    'tag_id' => $tag->id,
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

    public function approveOrRejectTagMember(Request $request)
    {
        try 
        {
            $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'tag_id' => 'required|exists:family_tag_ids,id',
                'status' => 'required|in:accepted,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $record = TagUser::where('tag_id', $request->tag_id)
                              ->where('user_id', $authUser->id)
                              ->first();

            if (!$record) {
                return response()->json([
                    'message' => 'You are not invited to this Tag.',
                    'status'  => 'failed'
                ], 400);
            }

            if ($record->approval_status !== 'pending') {
                return response()->json([
                    'message' => 'This request is already processed.',
                    'status'  => 'failed'
                ], 400);
            }

            DB::beginTransaction();

            // Update status
            $record->approval_status = $request->status;
            $record->save();

            // Notify the owner
            $tag     = FamilyTagId::find($request->tag_id);
            $ownerId = $tag->created_user_id;

            // Create notification message
            if ($request->status === 'accepted') {
                $notifType = 'tag_member_approved';
                $message   = "{$authUser->first_name} has accepted your invitation to join the tag {$tag->title}.";
            } else {
                $notifType = 'tag_member_rejected';
                $message   = "{$authUser->first_name} has rejected your invitation to join the tag {$tag->title}.";
            }

            // Send notification
            $this->notifyMessage(
                $authUser,
                $ownerId,
                $tag->id,
                $notifType,
                null,
                null,
                null,
                $message
            );

            DB::commit();

            return response()->json([
                'message' => "Tag request {$request->status} successfully.",
                'status'  => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong! '.$e->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }


    public function removeTagMember(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'tag_id' => 'required|exists:family_tag_ids,id',
                'user_id'  => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            $tag = FamilyTagId::find($request->tag_id);

            // Owner check
            if ($tag->created_user_id !== $authUser->id) {
                return response()->json([
                    'message' => 'Only the Tag owner can remove members.',
                    'status' => 'failed'
                ], 403);
            }

            $tagUser = TagUser::where('tag_id', $request->tag_id)
                                ->where('user_id', $request->user_id)
                                ->first();

            if (!$tagUser) {
                return response()->json([
                    'message' => 'User is not a member of this tag.',
                    'status' => 'failed'
                ], 400);
            }

            // Delete the member
            $tagUser->delete();

            // Notify removed user
            $this->notifyMessage(
                $authUser,                 
                $request->user_id,        
                $tag->id,               
                'remove_tag'
            );

            return response()->json([
                'message' => 'Tag member removed successfully!',
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong! '.$e->getMessage(),
                'status' => 'failed'
            ], 500);
        }
    }

    public function tagRequestList(Request $request)
    {
        try 
        {
            $limit  = (int) $request->get('limit', 30);
            $page   = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $search = $request->get('search');

            $authUser = Auth::user();

            // Fetch tag IDs owned by this user
            $ownerTagIds = FamilyTagId::where('created_user_id', $authUser->id)
                                      ->pluck('id')
                                      ->toArray();

            if (empty($ownerTagIds)) {
                return response()->json([
                    'message' => "No Requests Found",
                    'status'  => "success",
                    'data'    => [
                        'count' => 0,
                        'page' => $page,
                        'limit' => $limit,
                        'total_pages' => 0,
                        'requests' => []
                    ]
                ], 200);
            }

            // Base Query
            $query = TagUser::select(
                        'id',
                        'tag_id',
                        'user_id',
                        'role',
                        'approval_status',
                        'invited_by',
                        'created_at'
                    )
                    ->with([
                        'tags:id,title,image,family_tag_id',
                        'user:id,first_name,last_name,email,username,image'
                    ])
                    ->whereIn('tag_id', $ownerTagIds)
                    ->where('approval_status', 'pending')
                    ->whereNotNull('invited_by')
                    ->where('invited_by', '!=', $authUser->id);

            // Optional Search — username / first_name etc.
            if (!empty($search)) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            }

            // Count before pagination
            $total = $query->count();

            // Get Paginated results
            $requests = $query->orderBy('id', 'desc')
                              ->skip($offset)
                              ->take($limit)
                              ->get()
                              ->map(function ($req) {

                                    return [
                                        'request_id'    => $req->id,
                                        'tag_id'        => $req->tag_id,
                                        'tag_title'     => $req->tags->title ?? '',
                                        'tag_image'     => $req->tags->image ?? null,
                                        'family_tag_id' => $req->tags->family_tag_id ?? "",

                                        'user_id'       => $req->user_id,
                                        'user_name'     => trim(($req->user->first_name ?? '') . ' ' . ($req->user->last_name ?? '')),
                                        'username'      => $req->user->username ?? "",
                                        'user_image'    => $req->user->image ?? null,

                                        'role_requested'=> $req->role,
                                        'requested_at'  => $req->created_at->format('Y-m-d H:i:s A')
                                    ];
                              });

            $data = [
                'count'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),
                'requests'    => $requests
            ];

            return response()->json([
                'message' => "Tag Request List Fetched Successfully",
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

    public function receivedTagInvitations(Request $request)
    {
        try 
        {
            $limit  = (int) $request->get('limit', 30);
            $page   = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;

            $authUser = Auth::user();

            // Base Query: Invitations where user is the receiver
            $query = TagUser::select(
                        'id',
                        'tag_id',
                        'role',
                        'approval_status',
                        'invited_by',
                        'created_at'
                    )
                    ->with([
                        'tags:id,title,image,family_tag_id,created_user_id',
                        'inviter:id,first_name,last_name,username,image'
                    ])
                    ->where('user_id', $authUser->id)
                    ->where('approval_status', 'pending')
                    ->whereNotNull('invited_by')
                    ->where('invited_by', '!=', $authUser->id);  // Ensure invite came from owner/admin

            // Count before pagination
            $total = $query->count();

            // Get paginated results
            $invitations = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get()
                ->map(function ($invite) {

                    return [
                        'invite_id'     => $invite->id,
                        'tag_id'        => $invite->tag_id,
                        'family_tag_id' => $invite->tags->family_tag_id ?? '',
                        'tag_title'     => $invite->tags->title ?? '',
                        'tag_image'     => $invite->tags->image ?? null,

                        'invited_by_id'   => $invite->inviter->id ?? null,
                        'invited_by_name' => trim(($invite->inviter->first_name ?? '') . ' ' . ($invite->inviter->last_name ?? '')),
                        'invited_by_username' => $invite->inviter->username ?? '',
                        'invited_by_image'    => $invite->inviter->image ?? null,

                        'role'          => $invite->role,
                        'status'        => $invite->approval_status,
                        'invited_at'    => $invite->created_at->format('Y-m-d H:i:s A'),
                    ];
            });

            // Final response format
            $data = [
                'count'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),
                'invitations' => $invitations
            ];

            return response()->json([
                'message' => "Received Tag Invitations Fetched Successfully",
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



    public function tagSave(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'tag_id' => 'required|exists:family_tag_ids,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            $tag = FamilyTagId::where('id',$request->tag_id)->first();
            if(empty($tag)){
                return response()->json([
                    'message' => 'Tag Details not found.',
                    'status' => 'failed'
                ], 403);
            }

            // Owner check
            if ($tag->created_user_id == $authUser->id) {
                return response()->json([
                    'message' => 'You cannot save own Tag in Saved Tag',
                    'status' => 'failed'
                ], 403);
            }

            // if($tag->privacy_type == 'Private')
            // {
            //     return response()->json([
            //         'message' => 'This tag is Private So Please send Request for tag owner as collaborator or viewer',
            //         'status' => 'failed'
            //     ], 403);
            // }

            $existingTag = SavedTag::where('user_id', $authUser->id)
                                   ->where('tag_id', $tag->id)
                                   ->first();
            if($existingTag)
            {
                return response()->json([
                    'message' => 'Tag already Already saved',
                    'status' => 'failed'
                ], 403);

            }

            

            $savedTag = SavedTag::create([
                'tag_id' => $tag->id,
                'family_tag_id' => $tag->family_tag_id,
                'user_id' => $authUser->id,
                'is_removed' => 0,
            ]);

            return response()->json([
                'message' => 'Tag Saved successfully!',
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong! '.$e->getMessage(),
                'status' => 'failed'
            ], 500);
        }
    }

    public function tagSaveList(Request $request)
    {
        try 
        {
            $authUser   = Auth::user();
            $my_saved_tag = SavedTag::select('id','tag_id','created_at')
                                      ->with('tagData:id,family_tag_id,title,image')
                                      ->where('user_id',$authUser->id)
                                      ->get();                
            return response()->json([
                'message' => 'My Saved Tags List successfully',
                'status'  => 'success',
                'data'    => $my_saved_tag
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

    public function saveTagRemove(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'tag_id' => 'required|exists:family_tag_ids,id',
                'save_tag_id'  => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            

            $checkSavetag = SavedTag::where('tag_id', $request->tag_id)
                                ->where('id', $request->save_tag_id)
                                ->where('user_id', $authUser->id)
                                ->first();

            if (!$checkSavetag) {
                return response()->json([
                    'message' => 'Save tags data not found',
                    'status' => 'failed'
                ], 400);
            }

            // Delete the member
            $checkSavetag->delete();

            return response()->json([
                'message' => 'Saved Tag removed successfully!',
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong! '.$e->getMessage(),
                'status' => 'failed'
            ], 500);
        }
    }

    public function sendTagRequest(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'family_tag_id'  => 'required|exists:family_tag_ids,family_tag_id',
                'access_type'   => 'required|in:collaborator,viewer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            $tag = FamilyTagId::where('family_tag_id',$request->family_tag_id)->first();

            if (!$tag) {
                return response()->json([
                    'message' => 'Tag not found.',
                    'status' => 'failed'
                ], 404);
            }

            // Cannot request access for your own tag
            if ($tag->created_user_id == $authUser->id) {
                return response()->json([
                    'message' => 'You cannot request access to your own Tag.',
                    'status' => 'failed'
                ], 403);
            }

            // Only private tags can have request flow
            if ($tag->privacy_type === 'Public') 
            {
                return response()->json([
                    'message' => 'This is a public tag. No request required.',
                    'status' => 'failed'
                ], 403);
            }

            // Prevent duplicate request (pending/accepted)
            $existing = TagUser::where('tag_id', $tag->id)
                                ->where('user_id', $authUser->id)
                                ->whereIn('approval_status', ['pending', 'accepted'])
                                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'A request or access for this tag already exists.',
                    'status' => 'failed'
                ], 403);
            }

            // Create request
            $createRequest = TagUser::create([
                'tag_id'          => $tag->id,
                'user_id'         => $authUser->id,
                'invited_by'      => $authUser->id,  // self-request
                'role'            => $request->access_type,
                'approval_status' => 'pending',
            ]);

            // Notification message
            $type = $request->access_type === 'collaborator'
                    ? 'tag_collaborator_request_scan'
                    : 'tag_viewer_request_scan';

            $message = "{$authUser->first_name} has requested access as {$request->access_type} for tag {$tag->title}.";

            // Notify Tag Owner
            $this->notifyMessage(
                $authUser,
                $tag->created_user_id,
                $tag->id,
                $type,
                null,
                null,
                null,
                $message
            );

            return response()->json([
                'message' => 'Tag access request sent successfully!',
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong! '.$e->getMessage(),
                'status' => 'failed'
            ], 500);
        }
    }

    public function handleTagUserRequest(Request $request)
    {
         try 
         {
            $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'request_id'     => 'required|exists:tag_users,id',
                'status'     => 'required|in:accepted,rejected',
                'role'       => 'nullable|in:viewer,collaborator'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }
            // check record
            $record = TagUser::where('id', $request->request_id)->first();

            if (!$record) 
            {
                return response()->json([
                    'message' => 'No request found for this user.',
                    'status' => 'failed'
                ], 404);
            }

            // check tag owner
            $tag = FamilyTagId::where('id',$record->tag_id)->first();
            if ($tag->created_user_id != $authUser->id) 
            {
                return response()->json([
                    'message' => 'Only tag owner can approve/reject requests.',
                    'status' => 'failed'
                ], 403);
            }

            

            if ($record->approval_status !== 'pending') {
                return response()->json([
                    'message' => 'Request already processed.',
                    'status' => 'failed'
                ], 400);
            }

            DB::beginTransaction();

            // update approval status
            $record->approval_status = $request->status;

            // if owner changes role
            if (!empty($request->role)) {
                $record->role = $request->role;
            }

            $record->save();

            // Notify user
            $notifType = $request->status === 'accepted'? 'tag_access_approved': 'tag_access_rejected';

            $this->notifyMessage($authUser, $request->member_id, $tag->id, $notifType);

            DB::commit();

            return response()->json([
                'message' => "User request {$request->status} successfully.",
                'status' => 'success'
            ]);
             
         } catch (Exception $e) {
             return response()->json([
                'message' => 'Something went wrong! '.$e->getMessage(),
                'status' => 'failed'
            ], 500);
         }
        
    }

    public function tagscanView(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'family_tag_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed',
                    'is_request_sent' => 3
                ], 400);
            }

            $authUser = Auth::user();

            // Fetch tag with creator
            $get_tag_data = FamilyTagId::with('createdUser:id,first_name,last_name,image')
                                        ->where('family_tag_id', $request->family_tag_id)
                                        ->first();

            if (!$get_tag_data) {
                return response()->json([
                    'message' => 'Tags Details not found',
                    'status'  => 'failed',
                    'is_request_sent' => 3
                ], 404);
            }

            /**
             * ==========================
             * PRIVATE TAG ACCESS CHECK
             * ==========================
             */
            if ($get_tag_data->privacy_type !== 'Public') 
            {

                $checkTagUserAccess = TagUser::where('user_id', $authUser->id)
                                            ->where('tag_id', $get_tag_data->id)
                                            ->first();

                if (!$checkTagUserAccess) {
                    return response()->json([
                        'message' => 'You cannot access this tag without access So Please send viewer or collaborator role request',
                        'status'  => 'failed',
                        'is_request_sent' => 0,
                        'data'    => $get_tag_data
                    ], 404);
                }

                if ($checkTagUserAccess->approval_status === 'pending') {
                    return response()->json([
                        'message' => 'Your Tag request is approval is pending When request is approved the you can access',
                        'status'  => 'failed',
                        'is_request_sent' => 1,
                        'data'    => $get_tag_data
                    ], 404);
                }
            }

            /**
             * ==========================
             * FETCH TAG USERS
             * ==========================
             */
            $tag_user_list = TagUser::with('user:id,first_name,last_name,email,username,image')
                ->where('tag_id', $get_tag_data->id)
                ->where('approval_status', 'accepted')
                ->orderBy('id', 'DESC')
                ->get();

            $tag_users = $tag_user_list->map(function ($member) {
                $user = $member->user;

                return [
                    'id'              => $member->id,
                    'user_id'         => $user->id,
                    'first_name'      => $user->first_name,
                    'last_name'       => $user->last_name,
                    'email'           => $user->email,
                    'username'        => $user->username,
                    'image'           => $user->image ? $user->image : null,
                    'role'            => $member->role,
                    'approval_status' => $member->approval_status,
                ];
            });

            /**
             * ==========================
             * FETCH POSTS
             * ==========================
             */
            $posts = Post::with('user')
                         ->withCount('like', 'comments')
                         ->where('tag_id', $get_tag_data->id)
                         ->orderBy('id', 'DESC')
                         ->get();

            $get_save_tag = SavedTag::where('tag_id',$get_tag_data->id)->where('user_id',$authUser->id)->first();

            $get_tag_data['tag_user'] = $tag_users;
            $get_tag_data['tag_post'] = $posts;
            $get_tag_data['isSaved'] = $get_save_tag ? 1 : 0;

            /**
             * ==========================
             * SUCCESS RESPONSE
             * ==========================
             */
            return response()->json([
                'message' => 'Tags fetched successfully',
                'status'  => 'success',
                'data'    => $get_tag_data,
                'is_request_sent' => $get_tag_data->privacy_type === 'Public' ? 3 : 2
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed',
                'is_request_sent' => 3
            ], 500);
        }
    }


    public function followersUserList(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'tag_id' => 'required|exists:family_tag_ids,id',
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
            $tag = FamilyTagId::findOrFail($request->tag_id);

            // Only owner can see member list (optional rule)
            if ($tag->created_user_id != $authUser->id) {
                return response()->json([
                    'message' => 'You are not the Tag Owner, so you cannot view members.',
                    'status'  => 'failed'
                ], 403);
            }
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            $tagMemberIds = TagUser::where('tag_id', $request->tag_id)
                                        ->pluck('user_id')
                                        ->toArray();
            $notgetUserIds = array_unique(array_merge($blockedUserIds,$tagMemberIds));

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






    function generateFamilyTagId()
    {
        // Randomly choose between AA pattern and HA pattern
        $patterns = ['AA', 'HA'];
        $choice = $patterns[array_rand($patterns)];

        if ($choice === 'AA') {
            // AA + [0-4] + 4 digits
            return 'AA' . rand(0, 4) . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } else {
            // HA00 + [0-4] + 3 digits
            return 'HA00' . rand(0, 4) . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        }
    }

    function getFolderName($extension)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'bmp':
            case 'webp':
            case 'tiff':
            case 'svg':
            case 'heif':
                return 'images';
            case 'mp4':
            case 'mov':
            case 'wmv':
            case 'avi':
            case 'MOV':
            case 'mkv':
            case 'flv':
            case 'webm':
            case 'mpeg':
            case 'mpg':
            case '3gp':
                return 'videos';
            case 'mp3':
            case 'wav':
            case 'aac':
            case 'flac':
            case 'ogg':
            case 'm4a':
            case 'wma':
            case 'alac':
                return 'audio';

            case 'pdf':
            case 'docx':
            case 'doc':
            case 'txt':
            case 'xlsx':
            case 'xls':
            case 'ppt':
            case 'pptx':
            case 'csv':
                return 'documents';
            default:
                return 'other';
        }
    }




}
