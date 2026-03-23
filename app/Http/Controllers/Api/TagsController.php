<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carts;
use App\Models\OrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\FormatResponseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use App\Services\UploadImage;
use Carbon\Carbon;
use App\Models\User;
use App\Models\FamilyTagId;
use App\Models\TagUser;
use App\Models\Post;
use App\Models\SchedulingPost;
use App\Models\SavedTag;
use App\Models\AlbumPost;
use App\Models\Like;
use App\Models\Follow;
use App\Models\Product;
use App\Models\TagsPurchaseHistory;
use App\Models\Notification;

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
            $remaining_tag_count   = $authUser->remaining_tag_count;

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

                $myOwnedTagIds = FamilyTagId::where('created_user_id', $authUser->id)->where('is_deleted',0)
                    ->pluck('id')
                    ->toArray();

                $showMyRequests = true;  // Owner → show these sections
            }

            /** ----------------------------------------------------
             * PAGINATION
             * --------------------------------------------------- */
            $total = $query->where('is_deleted',0)->count();

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
                        'tags:id,family_tag_id,title,privacy_type,image',
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
                        'tags:id,family_tag_id,title,privacy_type,image',
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
                                    ->with('tagData:id,family_tag_id,title,privacy_type,image')
                                    ->where('user_id',$authUser->id)
                                    ->orderBy('id','DESC')
                                    ->take(8)
                                    ->get();

            $FamoryTags = Product::take(8)->get();

            /** ----------------------------------------------------
             * FINAL RESPONSE
             * --------------------------------------------------- */
            $data = [
                'remaining_tag_count'       => $remaining_tag_count,
                'count'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),

                'tags'                         => $tags,
                'latest_requests_to_my_tags'   => $latest_requests_to_my_tags,
                'latest_invitations_received'  => $latest_invitations_received,
                'my_saved_tag'                 => $my_saved_tag,
                'FamoryTags'                   => $FamoryTags,
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
                'image'       => 'required|file|mimes:jpeg,png,jpg',
                'tag_code'    => 'nullable|string|max:200',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $userId   = $authUser->id;
            $remaining_tag_count   = $authUser->remaining_tag_count;

            DB::beginTransaction();

            // Upload image
            if (!$request->hasFile('image') || !$request->file('image')->isValid()) 
            {
                return response()->json([
                    'message' => 'Invalid image upload.',
                    'status'  => 'failed'
                ], 400);
            }

            if($remaining_tag_count == 0)
            {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Please purchase a package to create Tags'
                ], 403);
            }

            // Check PhysicalTag
            $checkInOrder = null;
            if ($request->filled('tag_code'))
            {
                // Verify the PT code belongs to THIS user's order
                $get_data = OrderDetails::with('order')
                    ->where('tag_code', $request->tag_code)
                    ->whereHas('order', function ($q) {
                        $q->where('user_id', auth()->id());
                    })
                    ->first();

                if (!$get_data) {
                    return response()->json([
                        'message' => 'Physical Tag Code not matched with your order',
                        'status' => 'failed'
                    ], 400);
                }

                // Prevent re-registration: PT code can only be claimed once globally
                $alreadyRegistered = FamilyTagId::where('family_tag_id', $request->tag_code)
                                                 ->where('is_deleted', 0)
                                                 ->exists();

                if ($alreadyRegistered) {
                    return response()->json([
                        'message' => 'This Physical Tag has already been registered.',
                        'status'  => 'failed'
                    ], 400);
                }

                $checkInOrder = $get_data;
            }

            // Duplicate title check (scoped to physical tag or digital tags)
            if ($checkInOrder) {
                $checkName = FamilyTagId::where('title', $request->title)
                                        ->where('user_id', $userId)
                                        ->where('is_deleted', 0)
                                        ->first();
            } else {
                $checkName = FamilyTagId::where('title', $request->title)
                                        ->where('user_id', $userId)
                                        ->where('is_deleted', 0)
                                        ->first();
            }

            if ($checkName) {
                return response()->json([
                    'message' => 'Tag Name already exists.',
                    'status'  => 'failed'
                ], 400);
            }

            $file = $request->file('image');
            $filePath = $this->UploadImage->saveMedia($file, $userId);

            // Generate FamilyTag ID
            if($checkInOrder){
               $family_tag_id = $checkInOrder->tag_code;
            }else{

                $family_tag_id = $this->generateFamilyTagId();
            }

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

            $authUser->remaining_tag_count -= 1;
            $authUser->save();

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
                'image'       => 'nullable|file|mimes:jpeg,png,jpg',
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
                                  ->where('is_deleted', 0)
                                  ->first();

            if (!$getData) {
                return response()->json([
                    'message' => 'Tag Details Not Found or Tags deleted',
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
                                       ->where('is_deleted',0)
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
                                ->where('is_deleted',0)
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

                    $notifType = $role === 'collaborator' 
                    ? 'tag_collaborator_request' 
                    : 'tag_viewer_request';

                    if ($notifType == 'tag_collaborator_request') 
                    {
                        $message = "{$authUser->first_name} requested you to be collaborator to the {$tag->title} tag.";
                    } else {
                        $message = "{$authUser->first_name} requested you to be viewer to the {$tag->title} tag.";
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

            $update_notification = Notification::where('item_id',$record->tag_id)
                                                ->where('receiver_id',$record->user_id)
                                                ->where('has_actioned',0)
                                                ->first();
            if($update_notification)
            {
                $update_notification->has_actioned =1;
                $update_notification->save();
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

            $tag = FamilyTagId::where('id',$request->tag_id)->where('is_deleted',0)->first();
            if(empty($tag)){
                return response()->json([
                    'message' => 'Tags Details not found',
                    'status' => 'failed'
                ], 403);
            }

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
                                      ->where('is_deleted',0)
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

            $tag = FamilyTagId::where('id',$request->tag_id)->where('is_deleted',0)->first();
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
                                      ->with('tagData:id,family_tag_id,title,privacy_type,image')
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
                // 'save_tag_id'  => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            

            $checkSavetag = SavedTag::where('tag_id', $request->tag_id)
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

    // public function sendTagRequest(Request $request)
    // {
    //     try 
    //     {
    //         $validator = Validator::make($request->all(), [
    //             'family_tag_id'  => 'required|exists:family_tag_ids,family_tag_id',
    //             'access_type'   => 'required|in:collaborator,viewer',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'message' => $validator->errors()->first(),
    //                 'status' => 'failed'
    //             ], 400);
    //         }

    //         $authUser = Auth::user();

    //         $tag = FamilyTagId::where('family_tag_id',$request->family_tag_id)->where('is_deleted',0)->first();

    //         if (!$tag) {
    //             return response()->json([
    //                 'message' => 'Tag not found.',
    //                 'status' => 'failed'
    //             ], 404);
    //         }

    //         // Cannot request access for your own tag
    //         if ($tag->created_user_id == $authUser->id) {
    //             return response()->json([
    //                 'message' => 'You cannot request access to your own Tag.',
    //                 'status' => 'failed'
    //             ], 403);
    //         }

    //         // Only private tags can have request flow
    //         if ($tag->privacy_type === 'Public') 
    //         {
    //             return response()->json([
    //                 'message' => 'This is a public tag. No request required.',
    //                 'status' => 'failed'
    //             ], 403);
    //         }

    //         // Prevent duplicate request (pending/accepted)
    //         $existing = TagUser::where('tag_id', $tag->id)
    //                             ->where('user_id', $authUser->id)
    //                             ->whereIn('approval_status', ['pending', 'accepted'])
    //                             ->first();

    //         if ($existing) {
    //             return response()->json([
    //                 'message' => 'A request or access for this tag already exists.',
    //                 'status' => 'failed'
    //             ], 403);
    //         }

    //         // Create request
    //         $createRequest = TagUser::create([
    //             'tag_id'          => $tag->id,
    //             'user_id'         => $authUser->id,
    //             'invited_by'      => $authUser->id,  // self-request
    //             'role'            => $request->access_type,
    //             'approval_status' => 'pending',
    //         ]);

    //         // Notification message
    //         $type = $request->access_type === 'collaborator'
    //                 ? 'tag_collaborator_request_scan'
    //                 : 'tag_viewer_request_scan';

    //         $message = "{$authUser->first_name} has requested access as {$request->access_type} for tag {$tag->title}.";

    //         // Notify Tag Owner
    //         $this->notifyMessage(
    //             $authUser,
    //             $tag->created_user_id,
    //             $createRequest->id,
    //             $type,
    //             null,
    //             null,
    //             null,
    //             $message
    //         );

    //         return response()->json([
    //             'message' => 'Tag access request sent successfully!',
    //             'status' => 'success'
    //         ], 200);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'message' => 'Something went wrong! '.$e->getMessage(),
    //             'status' => 'failed'
    //         ], 500);
    //     }
    // }

    public function sendTagRequest(Request $request)
    {
        try 
        {
            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'family_tag_id' => 'required|exists:family_tag_ids,family_tag_id',
                'access_type'   => 'required|in:collaborator,viewer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            // ✅ Get Tag
            $tag = FamilyTagId::where('family_tag_id', $request->family_tag_id)
                              ->where('is_deleted', 0)
                              ->first();

            if (!$tag) {
                return response()->json([
                    'message' => 'Tag not found.',
                    'status'  => 'failed'
                ], 404);
            }

            // ❌ Cannot request own tag
            if ($tag->created_user_id == $authUser->id) {
                return response()->json([
                    'message' => 'You cannot request access to your own Tag.',
                    'status'  => 'failed'
                ], 403);
            }

            // ❌ Public tag doesn't need request
            if ($tag->privacy_type === 'Public') {
                return response()->json([
                    'message' => 'This is a public tag. No request required.',
                    'status'  => 'failed'
                ], 403);
            }

            // ✅ Check Existing Record (any status)
            $existing = TagUser::where('tag_id', $tag->id)
                                ->where('user_id', $authUser->id)
                                ->first();

            if ($existing) 
            {
                // ❌ Already accepted
                if ($existing->approval_status === 'accepted') {
                    return response()->json([
                        'message' => 'You already have access to this tag.',
                        'status'  => 'failed'
                    ], 403);
                }

                // ❌ Already pending
                if ($existing->approval_status === 'pending') {
                    return response()->json([
                        'message' => 'Your request is already pending.',
                        'status'  => 'failed'
                    ], 403);
                }

                // ✅ If rejected → update to pending
                if ($existing->approval_status === 'rejected') {

                    $existing->update([
                        'role'            => $request->access_type,
                        'approval_status' => 'pending',
                        'invited_by'      => $authUser->id,
                    ]);

                    $requestRecord = $existing;
                }
            } 
            else 
            {
                // ✅ Create new request
                $requestRecord = TagUser::create([
                    'tag_id'          => $tag->id,
                    'user_id'         => $authUser->id,
                    'invited_by'      => $authUser->id,
                    'role'            => $request->access_type,
                    'approval_status' => 'pending',
                ]);
            }

            // ✅ Notification Type
            $type = $request->access_type === 'collaborator'
                    ? 'tag_collaborator_request_scan'
                    : 'tag_viewer_request_scan';

            $message = "{$authUser->first_name} requested {$request->access_type} access to {$tag->title}.";

            // ✅ Send Notification to Tag Owner
            $this->notifyMessage(
                $authUser,
                $tag->created_user_id,
                $requestRecord->id,
                $type,
                null,
                null,
                null,
                $message
            );

            return response()->json([
                'message' => 'Tag access request sent successfully!',
                'status'  => 'success'
            ], 200);

        } 
        catch (\Exception $e) 
        {
            return response()->json([
                'message' => 'Something went wrong! ' . $e->getMessage(),
                'status'  => 'failed'
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
            $tag = FamilyTagId::where('id',$record->tag_id)->where('is_deleted',0)->first();
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

            $update_notification = Notification::where('item_id',$tag->id)
                                                ->where('receiver_id',$record->user_id)
                                                ->where('has_actioned',0)
                                                ->first();
            if($update_notification)
            {
                $update_notification->has_actioned = 1;
                $update_notification->save();
            }

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

    // public function tagscanView(Request $request)
    // {
    //     try 
    //     {
    //         $validator = Validator::make($request->all(), [
    //             'family_tag_id' => 'required',
    //             'is_scan_send_notify' => 'nullable|in:0,1',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'message' => $validator->errors()->first(),
    //                 'status' => 'failed',
    //                 'is_request_sent' => 3
    //             ], 400);
    //         }

    //         $authUser = Auth::user();
    //         $tag_permission_type = "";
    //         $scan_time_send = $request->input('is_scan_send_notify', 0);

    //         // Fetch tag with creator
    //         $get_tag_data = FamilyTagId::with('createdUser:id,first_name,last_name,image')
    //                                     ->where('family_tag_id', $request->family_tag_id)
    //                                     ->where('is_deleted',0)
    //                                     ->first();

    //         if (!$get_tag_data) {
    //             return response()->json([
    //                 'message' => 'Tags Details not found',
    //                 'status'  => 'failed',
    //                 'is_request_sent' => 3
    //             ], 404);
    //         }
    //         $get_tag_data['isTagOwner'] = ($get_tag_data->created_user_id == $authUser->id) ? 1 : 0;

    //         if($get_tag_data->created_user_id == $authUser->id)
    //         {
    //             $tag_permission_type= 'owner';
    //         }

    //         /**
    //          * ==========================
    //          * PRIVATE TAG ACCESS CHECK
    //          * ==========================
    //          */
    //         if ($get_tag_data->privacy_type !== 'Public' && $get_tag_data->created_user_id != $authUser->id) 
    //         {

    //             $checkTagUserAccess = TagUser::where('user_id', $authUser->id)
    //                                         ->where('tag_id', $get_tag_data->id)
    //                                         ->whereIn('approval_status',['pending','accepted'])
    //                                         ->first();

    //             if (!$checkTagUserAccess) {
    //                 return response()->json([
    //                     'message' => 'You cannot access this tag without access So Please send viewer or collaborator role request',
    //                     'status'  => 'failed',
    //                     'is_request_sent' => 0,
    //                     'data'    => $get_tag_data
    //                 ], 200);
    //             }

    //             if ($checkTagUserAccess->approval_status === 'pending') {
    //                 return response()->json([
    //                     'message' => 'Your Tag request is approval is pending When request is approved the you can access',
    //                     'status'  => 'failed',
    //                     'is_request_sent' => 1,
    //                     'data'    => $get_tag_data
    //                 ], 200);
    //             }
    //             $tag_permission_type = $checkTagUserAccess->role;
    //         }else{

    //             $tag_permission_type = 'owner';
    //         }

    //         /**
    //          * ==========================
    //          * FETCH TAG USERS
    //          * ==========================
    //          */
    //         $tag_user_list = TagUser::with('user:id,first_name,last_name,email,username,image')
    //             ->where('tag_id', $get_tag_data->id)
    //             ->where('approval_status', 'accepted')
    //             ->orderBy('id', 'DESC')
    //             ->get();

    //         $tag_users = $tag_user_list->map(function ($member) {
    //             $user = $member->user;

    //             return [
    //                 'id'              => $member->id,
    //                 'user_id'         => $user->id,
    //                 'first_name'      => $user->first_name,
    //                 'last_name'       => $user->last_name,
    //                 'email'           => $user->email,
    //                 'username'        => $user->username,
    //                 'image'           => $user->image ? $user->image : null,
    //                 'role'            => $member->role,
    //                 'approval_status' => $member->approval_status,
    //             ];
    //         });

    //         /**
    //          * ==========================
    //          * FETCH POSTS
    //          * ==========================
    //          */
    //         $posts = Post::with('user')
    //                      ->withCount('like', 'comments')
    //                      ->where('tag_id', $get_tag_data->id)
    //                      ->orderBy('id', 'DESC')
    //                      ->get();


    //         $get_save_tag = SavedTag::where('tag_id',$get_tag_data->id)->where('user_id',$authUser->id)->first();

    //         $get_tag_data['tag_user'] = $tag_users;
    //         $get_tag_data['tag_post'] = $posts;
    //         $get_tag_data['isSaved'] = $get_save_tag ? 1 : 0;
    //         $get_tag_data['tag_permission_type'] = $tag_permission_type;

    //         if ($get_tag_data->created_user_id != $authUser->id && $scan_time_send == 1) 
    //         {

    //             $tagOwner = User::find($get_tag_data->created_user_id);

    //             if ($tagOwner) 
    //             {
    //                 $message = "{$authUser->first_name} scanned your {$get_tag_data->title} tag.";
    //                 $this->notifyMessage($authUser, $tagOwner->id, $get_tag_data->id, 'tag_scan', null, null,null,$message);
    //             }
    //         }
            

    //         /**
    //          * ==========================
    //          * SUCCESS RESPONSE
    //          * ==========================
    //          */
    //         return response()->json([
    //             'message' => 'Tags fetched successfully',
    //             'status'  => 'success',
    //             'data'    => $get_tag_data,
    //             'is_request_sent' => $get_tag_data->privacy_type === 'Public' ? 3 : 2
    //         ], 200);

    //     } catch (\Exception $exception) {
    //         return response()->json([
    //             'message' => $exception->getMessage(),
    //             'status'  => 'failed',
    //             'is_request_sent' => 3
    //         ], 500);
    //     }
    // }

    public function tagscanView(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'family_tag_id' => 'required',
                'is_scan_send_notify' => 'nullable|in:0,1',
                'per_page' => 'nullable|integer',
                'page' => 'nullable|integer',
                'timezone' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed',
                    'is_request_sent' => 3
                ], 400);
            }

            $authUser = Auth::user();
            $tag_permission_type = "";
            $scan_time_send = $request->input('is_scan_send_notify', 0);

            /**
             * ==============================
             * USER TIMEZONE DETECTION
             * ==============================
             */
            $apacheHeaders = function_exists('apache_request_headers') ? apache_request_headers() : [];
            $headers = array_change_key_case($apacheHeaders, CASE_LOWER);

            $userTimezone = $request->timezone
                ?? $headers['time_zone']
                ?? $headers['timezone']
                ?? $headers['time-zone']
                ?? 'UTC';

            if (!in_array($userTimezone, timezone_identifiers_list())) {
                $userTimezone = 'UTC';
            }

            /**
             * ==============================
             * FETCH TAG
             * ==============================
             */
            $get_tag_data = FamilyTagId::with('createdUser:id,first_name,last_name,image')
                ->where('family_tag_id', $request->family_tag_id)
                ->where('is_deleted', 0)
                ->first();

            if (!$get_tag_data) {
                return response()->json([
                    'message' => 'Tags Details not found',
                    'status'  => 'failed',
                    'is_request_sent' => 3,
                    'tag_not_register' => 1,//tag not register case
                    'tag_code' =>$request->family_tag_id
                ], 404);
            }

            $get_tag_data['isTagOwner'] = ($get_tag_data->created_user_id == $authUser->id) ? 1 : 0;

            /**
             * ==============================
             * PRIVATE TAG ACCESS CHECK
             * ==============================
             */
            if ($get_tag_data->privacy_type !== 'Public' && $get_tag_data->created_user_id != $authUser->id) {

                $checkTagUserAccess = TagUser::where('user_id', $authUser->id)
                    ->where('tag_id', $get_tag_data->id)
                    ->whereIn('approval_status', ['pending', 'accepted'])
                    ->first();

                if (!$checkTagUserAccess) {
                    return response()->json([
                        'message' => 'You cannot access this tag without access So Please send viewer or collaborator role request',
                        'status'  => 'failed',
                        'is_request_sent' => 0,
                        'tag_not_register' => 0,
                        'tag_code'  =>null,
                        'data'    => $get_tag_data
                    ], 200);
                }

                if ($checkTagUserAccess->approval_status === 'pending') {
                    return response()->json([
                        'message' => 'Your Tag request approval is pending. When request is approved then you can access.',
                        'status'  => 'failed',
                        'is_request_sent' => 1,
                        'tag_not_register' => 0,
                        'tag_code' => null,
                        'data'    => $get_tag_data
                    ], 200);
                }

                $tag_permission_type = $checkTagUserAccess->role;

            } else {
                $tag_permission_type = 'owner';
            }

            /**
             * ==============================
             * FETCH TAG USERS
             * ==============================
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
                    'image'           => $user->image ?: null,
                    'role'            => $member->role,
                    'approval_status' => $member->approval_status,
                ];
            });

            /**
             * ==============================
             * FETCH TAG POSTS
             * ==============================
             */
            $query = Post::with([
                        'user' => fn($q) => $q->withTrashed()
                            ->select('id','first_name','last_name','image','deleted_at')
                    ])
                    ->with('scheduling_post')
                    ->withCount(['like','comments'])
                    ->where('tag_id', $get_tag_data->id)
                    ->orderBy('updated_at','desc');

            $posts = $query->get();

            foreach ($posts as $post) {

                $post->is_like = Like::where([
                    'post_id' => $post->id,
                    'user_id' => $authUser->id
                ])->exists();

                $post->is_following = Follow::where([
                    'follower_id' => $authUser->id,
                    'following_id' => $post->user_id,
                    'status' => 'approved'
                ])->exists();

                $post->is_save = AlbumPost::where([
                    'user_id' => $authUser->id,
                    'post_id' => $post->id
                ])->exists();

                if ($post->scheduling_post) {

                    $createdAtUserTZ = $post->scheduling_post->created_at
                        ->copy()
                        ->timezone($userTimezone);

                    $post->created_date = $createdAtUserTZ->format('m/d/y');

                    if ($post->scheduling_post->schedule_type === 'now') {
                        $postedDateTime = $createdAtUserTZ;
                    } else {
                        $postedDateTime = \Carbon\Carbon::createFromFormat(
                            'Y-m-d H:i:s',
                            $post->scheduling_post->schedule_date . ' ' . $post->scheduling_post->schedule_time,
                            'UTC'
                        )->setTimezone($userTimezone);
                    }

                    $post->posted_date = $postedDateTime->format('m/d/y');
                    $post->scheduling_post->schedule_date = $postedDateTime->format('m/d/y');
                    $post->scheduling_post->schedule_time = $postedDateTime->format('h:i A');

                    $post->scheduling_post->makeHidden(['id','post_id']);
                }
            }

            /**
             * ==============================
             * PAGINATION
             * ==============================
             */
            $perPage = (int) $request->input('per_page', 10);
            $perPage = $perPage > 0 ? $perPage : 10;

            $page = (int) $request->input('page', 1);
            $page = $page > 0 ? $page : 1;

            $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $posts->slice(($page - 1) * $perPage, $perPage)->values(),
                $posts->count(),
                $perPage,
                $page
            );

            /**
             * ==============================
             * SAVE STATUS
             * ==============================
             */
            $get_save_tag = SavedTag::where('tag_id',$get_tag_data->id)
                ->where('user_id',$authUser->id)
                ->first();

            $get_tag_data['tag_user'] = $tag_users;
            $get_tag_data['tag_post'] = $paginated->items();
            $get_tag_data['total_records'] = $paginated->total();
            $get_tag_data['total_pages'] = $paginated->lastPage();
            $get_tag_data['current_page'] = $paginated->currentPage();
            $get_tag_data['per_page'] = $paginated->perPage();
            $get_tag_data['isSaved'] = $get_save_tag ? 1 : 0;
            $get_tag_data['tag_permission_type'] = $tag_permission_type;

            /**
             * ==============================
             * SCAN NOTIFICATION
             * ==============================
             */
            if ($get_tag_data->created_user_id != $authUser->id && $scan_time_send == 1) {

                $tagOwner = User::find($get_tag_data->created_user_id);

                if ($tagOwner) {
                    $message = "{$authUser->first_name} scanned your {$get_tag_data->title} tag.";
                    $this->notifyMessage(
                        $authUser,
                        $tagOwner->id,
                        $get_tag_data->id,
                        'tag_scan',
                        null,
                        null,
                        null,
                        $message
                    );
                }
            }

            /**
             * ==============================
             * SUCCESS RESPONSE (UNCHANGED)
             * ==============================
             */
            return response()->json([
                'message' => 'Tags fetched successfully',
                'status'  => 'success',
                'data'    => $get_tag_data,
                'is_request_sent' => $get_tag_data->privacy_type === 'Public' ? 3 : 2,
                'tag_not_register' => 0,
                'tag_code' => null,
            ], 200);

        } catch (\Exception $exception) {

            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed',
                'is_request_sent' => 3,
                'tag_not_register' => 2, //tag Fails case
                'tag_code' => null, //tag Fails case
            ], 500);
        }
    }


    public function followersUserListOLD05March(Request $request)
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
            $tag = FamilyTagId::where('id',$request->tag_id)->where('is_deleted',0)->first();
            if(empty($tag)){
                return response()->json([
                    'message' => 'Tags Details not found',
                    'status' => 'failed'
                ], 403);
            }

            // Only owner can see member list (optional rule)
            if ($tag->created_user_id != $authUser->id) {
                return response()->json([
                    'message' => 'You are not the Tag Owner, so you cannot view members.',
                    'status'  => 'failed'
                ], 403);
            }
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            $tagMemberIds = TagUser::where('tag_id', $request->tag_id)
                                        ->whereIn('approval_status',['accepted','pending'])
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

            $tag = FamilyTagId::where('id',$request->tag_id)
                    ->where('is_deleted',0)
                    ->first();

            if(empty($tag)){
                return response()->json([
                    'message' => 'Tags Details not found',
                    'status' => 'failed'
                ], 403);
            }

            if ($tag->created_user_id != $authUser->id) {
                return response()->json([
                    'message' => 'You are not the Tag Owner, so you cannot view members.',
                    'status'  => 'failed'
                ], 403);
            }

            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);

            $tagMemberIds = TagUser::where('tag_id', $request->tag_id)
                            ->whereIn('approval_status',['accepted','pending'])
                            ->pluck('user_id')
                            ->toArray();

            $notgetUserIds = array_unique(array_merge($blockedUserIds,$tagMemberIds));

            /*
            |--------------------------------------------------------------------------
            | FOLLOWING LIST (Users Auth User is Following)
            |--------------------------------------------------------------------------
            */

            $query = Follow::where('follower_id', $authId)
                        ->where('status', 'approved')
                        ->whereNotIn('following_id', $notgetUserIds)
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

            $followings = $query->orderBy('id', 'desc')
                                ->skip($offset)
                                ->take($limit)
                                ->get();

            $users = $followings->map(function ($follow) {

                $user = $follow->following;

                return [
                    'follow_id'     => $follow->id,
                    'user_id'       => $user->id,
                    'first_name'    => $user->first_name,
                    'last_name'     => $user->last_name,
                    'email'         => $user->email,
                    'username'      => $user->username,
                    'image'         => $user->image ? $user->image : null,
                ];
            });

            $data = [
                'user_id'     => (int) $authId,
                'count'       => $totalUsers,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($totalUsers / $limit),
                'users'       => $users
            ];

            return response()->json([
                'message' => 'Following users fetched successfully',
                'status'  => "success",
                'data'    => $data
            ]);

        } 
        catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status' => 'failed'
            ], 400);
        }
    }

    public function FamoryTagsBuy(Request $request)
    {
        return response()->json([
                'message' => "This tag Out of Stock.Try After some time",
                'status' => 'failed'
            ], 400);
    }

    public function listMembers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tag_id'      => 'required|exists:family_tag_ids,id',
                'search'      => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            // $album = FamilyTagId::findOrFail($request->tag_id);
            $album = FamilyTagId::where('id',$request->tag_id)->where('is_deleted',0)->first();
            if(empty($album)){
                return response()->json([
                    'message' => 'Tags Details not found',
                    'status' => 'failed'
                ], 403);
            }

            // Pagination parameters
            $limit  = (int) $request->get('limit', 30);
            $page   = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $search = $request->get('search');

            // Base query
            $query = TagUser::where('tag_id', $album->id)->whereIn('approval_status',['accepted','pending'])
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
                'tag_id'       => $album->id,
                'family_tag_id'=> $album->family_tag_id,
                'tag_image'    => $album->image,
                'title'        => $album->title,
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


    public function buyTag(Request $request)
    {
        DB::beginTransaction();

        try {

            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'package_name' => 'required|string',
                'tag_count'    => 'required|integer|min:1',
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
            $total_used_tag_count = FamilyTagId::where('created_user_id', $authUser->id)->count();

            // ✅ Save purchase history
            $createPurchase = [
                'user_id'      => $authUser->id,
                'tag_count'    => $request->tag_count,
                'package_name' => $request->package_name,
                'amount'       => $request->amount,
                'date'         => $formattedDate,
                'status'       => $request->status,
                'payment_id'   => $request->payment_id,
            ];

            TagsPurchaseHistory::create($createPurchase);

            // ✅ Total purchased albums
            $get_total_purchase_count = TagsPurchaseHistory::where('user_id', $authUser->id)->sum('tag_count');

            // ✅ Remaining credits
            $remaining_tag_count = $get_total_purchase_count - $total_used_tag_count;

            if ($remaining_tag_count < 0) {
                $remaining_tag_count = 0;
            }

            // ✅ Update user remaining count (NOT +=)
            $authUser->remaining_tag_count = $remaining_tag_count;
            $authUser->save();

            DB::commit();

            return response()->json([
                'message' => "Tags Package Purchased Successfully",
                'status'  => 'success',
                'data'    => [
                    'remaining_tag_count' => $remaining_tag_count,
                    'total_purchase_count'=> $get_total_purchase_count,
                    'used_tag_count'      => $total_used_tag_count
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

    public function TagBuyHistory(Request $request)
    {
        try {

            $authUser = Auth::user();

            // ✅ Get purchase history (latest first)
            $history = TagsPurchaseHistory::where('user_id', $authUser->id)
                        ->orderBy('id', 'desc')
                        ->get([
                            'id',
                            'package_name',
                            'tag_count',
                            'amount',
                            'date',
                            'status',
                            'payment_id',
                            'created_at'
                        ]);

            // ✅ Total purchased albums
            $total_purchase_count = TagsPurchaseHistory::where('user_id', $authUser->id)
                                        ->sum('tag_count');

            // ✅ Total used albums
            $total_used_tags_count = FamilyTagId::where('created_user_id', $authUser->id)->count();

            // ✅ Remaining albums
            $remaining_tag_count = $total_purchase_count - $total_used_tags_count;

            if ($remaining_tag_count < 0) {
                $remaining_tag_count = 0;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tags purchase history fetched successfully',
                'summary' => [
                    'total_purchased' => $total_purchase_count,
                    'total_used' => $total_used_tags_count,
                    'remaining' => $remaining_tag_count,
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

    public function deleteTags(Request $request)
    {
        DB::beginTransaction();

        try {

            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'tag_id' => 'required|integer|exists:family_tag_ids,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            // ✅ Fetch tag (owned by user & not deleted)
            $tag = FamilyTagId::where('id', $request->tag_id)
                        ->where('created_user_id', $authUser->id)
                        ->where('is_deleted', 0)
                        ->first();

            if (!$tag) {
                return response()->json([
                    'message' => "Tag not found or already deleted",
                    'status'  => 'failed'
                ], 404);
            }

            // ✅ Soft delete (mark as deleted)
            $tag->is_deleted = 1;
            $tag->save();

            DB::commit();

            return response()->json([
                'message' => "Tag deleted successfully",
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

    public function posttagdelete(Request $request)
    {
        DB::beginTransaction();

        try {

            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'tag_id' => 'required|integer|exists:family_tag_ids,id',
                'post_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();

            // ✅ Fetch tag (owned by user & not deleted)
            $tag = FamilyTagId::where('id', $request->tag_id)
                        // ->where('created_user_id', $authUser->id)
                        ->where('is_deleted', 0)
                        ->first();

            if (!$tag) {
                return response()->json([
                    'message' => "Tag not found or already deleted",
                    'status'  => 'failed'
                ], 404);
            }

            $post = Post::where('id',$request->post_id)->where('tag_id',$tag->id)->first();
            if($post)
            {
                SchedulingPost::where('post_id', $post->id)->delete();
                $post->delete();
            }

            DB::commit();

            return response()->json([
                'message' => "Tag Post Deleted successfully",
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

    public function physicalList(Request $request)
    {
        try {

            $limit  = (int) $request->get('limit', 10);
            $page   = (int) $request->get('page', 1);
            $search = $request->get('search');
            $sort   = in_array($request->get('sort'), ['asc', 'desc']) ? $request->get('sort') : 'desc';

            $authUser = Auth::user();
            $offset   = ($page - 1) * $limit;

            $query = Product::query();

            if (!empty($search)) {
                $query->where('name', 'LIKE', '%' . $search . '%');
            }

            $query->orderBy('price', $sort);

            $total = $query->count();

            $products = $query->offset($offset)->limit($limit)->get();

            // Get all product IDs the user already has in cart — single query
            $cartProductIds = Carts::where('user_id', $authUser->id)
                                ->whereIn('product_id', $products->pluck('id'))
                                ->pluck('product_id')
                                ->toArray();

            $productList = $products->map(function ($product) use ($cartProductIds) {
                return array_merge($product->toArray(), [
                    'is_cart_exist' => in_array($product->id, $cartProductIds),
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Physical tags list fetched successfully',
                'data'    => [
                    'count'       => $total,
                    'page'        => $page,
                    'limit'       => $limit,
                    'total_pages' => ceil($total / $limit),
                    'products'    => $productList,
                ],
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function physicalDetailsOLD(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:products,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $authUser = Auth::user();

            $product = Product::find($request->id);

            $is_cart_exist = Carts::where('user_id', $authUser->id)
                                ->where('product_id', $product->id)
                                ->exists();

            return response()->json([
                'status'  => 'success',
                'message' => 'Physical tag details fetched successfully',
                'data'    => array_merge($product->toArray(), [
                    'is_cart_exist' => $is_cart_exist,
                ]),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function physicalDetails(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:products,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $authUser = Auth::user();

            if (!$authUser) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $product = Product::find($request->id);

            // single query for cart
            $cart = Carts::select('id','quantity')->where('user_id', $authUser->id)
                            ->where('product_id', $product->id)
                            ->first();

            $is_cart_exist = $cart ? true : false;

            $cart_count = Carts::where('user_id', $authUser->id)->count();

            return response()->json([
                'status'  => 'success',
                'message' => 'Physical tag details fetched successfully',
                'data'    => array_merge(
                    $product->toArray(),
                    [
                        'is_cart_exist' => $is_cart_exist,
                        'cart_data' => $cart,
                        'cart_count' => $cart_count
                    ]
                ),
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }






}
