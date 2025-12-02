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

            if ($tag_type === 'collaborator') 
            {
                $query->select('family_tag_ids.*')
                    ->join('tag_users', 'tag_users.tag_id', '=', 'family_tag_ids.id')
                    ->where('tag_users.user_id', $authUser->id)
                    ->where('tag_users.role', $tag_type)
                    ->addSelect('tag_users.approval_status'); // Add approval status
            } 
            else 
            {
                // My family_tag_ids
                $query->where('family_tag_ids.user_id', $authUser->id);
            }

            $total     = $query->count();
            $tags      = $query->orderBy('id', 'desc')
                               ->skip($offset)
                               ->take($limit)
                               ->get();
                            
                            
            $data = [
                'count'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),
                'tags'        => $tags,
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
                'title'       => 'required',
                'description' => 'required',
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
                'title'       => 'required',
                'description' => 'required',
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
            if ($get_tag_data->image) {
                $get_tag_data->image_url = rtrim($s3BaseUrl, '/') . '/' . ltrim($get_tag_data->image, '/');
            }
            $get_tag_data->makeHidden(['image','avatar']);


            // Fetch tag users
            $tag_user_list = TagUser::with('user:id,first_name,last_name,email,username,image')
                                    ->where('tag_id', $get_tag_data->id)
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
            $posts = Post::withCount('like','comments')->where('tag_id',$get_tag_data->id)->get();
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
