<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\FinalWord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use App\Traits\FormatResponseTrait;
use DB;
use App\Services\UploadImage;

class FinalWordController extends Controller
{
    use OneSignalTrait;
    use FormatResponseTrait;

    protected $UploadImage;

    public function __construct(UploadImage $UploadImage)
    {
        $this->UploadImage = $UploadImage;
        
    }
    private function getFolderName($extension)
    {
        $videoExtensions = ['mp4', 'mov', 'avi','MP4','MOV','AVI'];
        if (in_array($extension, $videoExtensions)) {
            return 'videos';
        } else {
            return 'files';
        }
    }

    public function index(Request $request)
    {
        try {
            $authUser = Auth::user();

            
            $limit  = (int) $request->get('limit', 10); 
            $page   = (int) $request->get('page', 1); 
            $offset = ($page - 1) * $limit;

            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';
            $query = FinalWord::where('user_id', $authUser->id);
            $total = $query->count();

            $videos = $query->orderBy('id', 'desc')
                            ->skip($offset)
                            ->take($limit)
                            ->get()
                            ->map(function ($fw) use ($s3BaseUrl) {
                                return [
                                    'id'    => $fw->id,
                                    'video' => $fw->video_path ? $s3BaseUrl . '/' . ltrim($fw->video_path, '/') : null,
                                ];
                            });
                            
            $data = [
                'count'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),
                'videos'      => $videos,
            ];

            return response()->json([
                'message' => 'Final Words List fetched successfully',
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


    // View user’s Final Words
    public function showByOtherUser(Request $request, $user_id)
    {
        try {
            $user = User::findOrFail($user_id);

            // If user is alive → hide videos
            // if (!$user->is_dead) {
            //     return response()->json([
            //         'message' => 'Final Words are private until the user is marked as deceased',
            //         'status'  => 'failed',
            //         'data'    => [],
            //     ], 400);
            // }

            // 🔹 Custom pagination params
            $limit  = (int) $request->get('limit', 10); // default 10
            $page   = (int) $request->get('page', 1);   // default 1
            $offset = ($page - 1) * $limit;

            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

            
            $query = FinalWord::where('user_id', $user_id);

           
            $total = $query->count();

            
            $videos = $query->orderBy('id', 'desc')
                            ->skip($offset)
                            ->take($limit)
                            ->get()
                            ->map(function ($fw) use ($s3BaseUrl) {
                                return [
                                    'id'    => $fw->id,
                                    // 'title' => $fw->title ?? null,
                                    'video' => $fw->video_path 
                                                ? $s3BaseUrl . '/' . ltrim($fw->video_path, '/') 
                                                : null,
                                ];
                            });
           $userdata = [
        "id" => $user->id,
        "first_name" => $user->first_name,
        "last_name" => $user->last_name,
        "email" => $user->email,
        "role_id" => $user->role_id,
        "phone" => $user->phone,
        "image" => $user->image,
        "company_name" => $user->company_name,
        "company_address" => $user->company_address,
        "company_logo" => $user->company_logo,
        "is_approved" => $user->is_approved,
        "stripe_customer_id" => $user->stripe_customer_id,
        "agreed_terms" => $user->agreed_terms,
        "ban_user" => $user->ban_user,
        "deleted_at" => $user->deleted_at,
        "username" => $user->username,
        "gender" => $user->gender,
        "dob" => $user->dob,
        "agree_on_receiving" => $user->agree_on_receiving,
        "country_code" => $user->country_code,
        "is_private" => $user->is_private,
        "is_dead" => $user->is_dead ? true : false,   // ✅ always boolean
        "description" => $user->description,
    ];
           
            $data = [
                'user'        => $userdata,
                'videos'      => $videos,
                'count'       => $total,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($total / $limit),
            ];

            return response()->json([
                'message' => 'Final Words retrieved successfully',
                'status'  => 'success',
                'data'    => $data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed',
            ], 400);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'video' => 'required|file|mimes:mp4,mov,avi|max:51200',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $userId   = $authUser->id;
            $fileUploadSuccess = false;
            $videoPath = null;

            if ($request->hasFile('video') && $request->file('video')->isValid()) {
                $file = $request->file('video');
                $extension = $file->getClientOriginalExtension();
                $folder = $this->getFolderName($extension);

                try {
                    $res = $this->UploadImage->saveMedia($file, $userId);
                    if ($folder === 'videos') {
                        $videoPath = $res;
                        $fileUploadSuccess = true; // ✅ fix
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'File upload failed: ' . $e->getMessage(),
                        'status'  => 'failed'
                    ], 500);
                }
            }

            if ($fileUploadSuccess) {
                DB::beginTransaction();
                $insertData = [
                    'video_path' => $videoPath['compressed'],
                    'user_id'    => $userId
                ];

                
                $createData = FinalWord::create($insertData);
                DB::commit();

                return response()->json([
                    'message' => 'Final Words Created Successfully',
                    'status'  => 'success',
                    'data'    => $createData
                ], 200);
            }

            return response()->json([
                'message' => 'No valid video uploaded.',
                'status'  => 'failed'
            ], 400);

        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
            $authId = Auth::id();
            $videoId = $request->input('id'); // get id from POST body

            $video = FinalWord::where('id', $videoId)
                                        ->where('user_id', $authId)
                                        ->first();

            if (!$video) {
                return response()->json([
                    'message' => 'Video not found or unauthorized',
                    'status' => 'failed'
                ], 404);
            }

            // Delete file from S3 if exists
            $videoPath = $video->video_path;
            if (!empty($videoPath)) {
                $parsed = str_replace(env('AWS_URL') . '/', '', $videoPath);
                // If AWS_URL not set, fallback
                if ($parsed === $videoPath) {
                    $parsed = str_replace('https://famorys3.s3.amazonaws.com/', '', $videoPath);
                }

                try {
                    \Storage::disk('s3')->delete($parsed);
                } catch (\Exception $e) {
                    \Log::error("S3 delete failed: " . $e->getMessage());
                }
            }

            $video->delete();

            return response()->json([
                'message' => 'Final Word video deleted successfully',
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

}
