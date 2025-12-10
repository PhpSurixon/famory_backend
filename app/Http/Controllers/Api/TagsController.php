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

    public function index(Request $request)
    {
        try 
        {
            $authUser = Auth::user();
            $limit    = (int) $request->get('limit', 10); 
            $page     = (int) $request->get('page', 1); 
            $offset   = ($page - 1) * $limit;
            $tag_type = $request->get('tag_type');

            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';
            $query     = FamilyTagId::query();

            if ($tag_type === 'collaborator' || $tag_type === 'viewer') 
            {
                $query->select('family_tag_ids.*')
                    ->join('tag_users', 'tag_users.tag_id', '=', 'family_tag_ids.id')
                    ->where('tag_users.user_id', $authUser->id)
                    ->where('tag_users.role', $tag_type)
                    ->addSelect('tag_users.approval_status'); // Add approval status

                $get_tagIds =[];
            } 
            else 
            {
                // My family_tag_ids
                $query->where('family_tag_ids.created_user_id', $authUser->id);

                $get_tagIds = FamilyTagId::where('created_user_id',$authUser->id)->pluck('id')->toArray();
            }

            $total     = $query->count();
            $tags      = $query->orderBy('id', 'desc')
                               ->skip($offset)
                               ->take($limit)
                               ->get();

            $tag_request_user = TagUser::select('id','tag_id','role','user_id','approval_status','created_at')
                                         ->with('tags:id,family_tag_id,title,image','user:id,first_name,last_name,email,username,image')
                                         ->whereIn('tag_id',$get_tagIds)
                                         ->where('approval_status','pending')
                                         ->orderBy('id','DESC')
                                         ->take(8)
                                         ->get(); 
            $my_saved_tag = SavedTag::select('id','tag_id','created_at')
                                      ->with('tagData:id,family_tag_id,title,image')
                                      ->where('user_id',$authUser->id)
                                      ->orderBy('id','DESC')
                                      ->take(8)
                                      ->get();               
                            
            $data = [
                'count'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),
                'tags'        => $tags,
                'tag_request_user'        => $tag_request_user,
                'my_saved_tag'            => $my_saved_tag,
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
            $get_tag_data = FamilyTagId::with('createdUser:id,first_name,last_name')
                                       ->where('id', $id)
                                       ->first();

            if (!$get_tag_data) {
                return response()->json([
                    'message' => 'Tags Details not found',
                    'status'  => 'failed'
                ], 404);
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
                    'image'          => $user->image ? $s3BaseUrl . $user->image : null,
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
                        'tag_id' => $tag->id,
                        'user_id'  => $memberUserId,
                        'role'     => $role,
                        'approval_status'     => 'pending',
                    ]);
                    $added[] = $memberUserId;

                    $notifType = $role === 'collaborator'? 'tag_collaborator_request': 'tag_viewer_request';
                    $this->notifyMessage($authUser,$memberUserId,$tag->id,$notifType);
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
                'status'   => 'required|in:accepted,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed'
                ], 400);
            }

            $record = TagUser::where('tag_id', $request->tag_id)
                                ->where('user_id', $authUser->id)
                                ->first();

            if (!$record) {
                return response()->json([
                    'message' => 'You are not added to this Tag.',
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
            $tag = FamilyTagId::find($request->tag_id);
            $ownerId = $tag->created_user_id;

            $notifType = $request->status === 'accepted'? 'tag_member_approved':'tag_member_rejected';

            $this->notifyMessage($authUser, $ownerId, $tag->id, $notifType);
            DB::commit();
         

            return response()->json([
                'message' => "Tag Request {$request->status} successfully.",
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
                    'message' => 'User is not a member of this album.',
                    'status' => 'failed'
                ], 400);
            }

            // Delete the member
            $tagUser->delete();

            // Notify removed user
            $this->notifyMessage(
                $authUser,                 // sender (album owner)
                $request->user_id,         // receiver (removed user)
                $tag->id,                // album_id
                'remove_tag'             // notification type
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
            $authUser   = Auth::user();
            $get_tagIds = FamilyTagId::where('created_user_id',$authUser->id)->pluck('id')->toArray();
            $tag_request_user = TagUser::select('id','tag_id','role','user_id','approval_status','created_at')
                                         ->with('tags:id,family_tag_id,title,image','user:id,first_name,last_name,email,username,image')
                                         ->whereIn('tag_id',$get_tagIds)
                                         ->where('approval_status','pending')
                                         ->get();                
            return response()->json([
                'message' => 'My Tags Request List successfully',
                'status'  => 'success',
                'data'    => $tag_request_user
            ], 200);

        } catch (\Exception $e) {
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

            if($tag->privacy_type == 'Private')
            {
                return response()->json([
                    'message' => 'This tag is Private So Please send Request for tag owner as collaborator or viewer',
                    'status' => 'failed'
                ], 403);
            }

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
