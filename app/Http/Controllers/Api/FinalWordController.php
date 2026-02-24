<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\FinalWord;
use App\Models\TrustedUser;
use App\Models\DeathConfirmation;
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
                                    'isPotrait'    => $fw->isPotrait,
                                    // 'video' => $fw->video_path ? $s3BaseUrl . '/' . ltrim($fw->video_path, '/') : null,
                                    'video' => $fw->video_path ? $fw->video_path : null,
                                    // 'video_formats' => $fw->video_path ? json_decode($fw->video_formats) : null,
                                    'video_formats' => $fw->video_path ? $fw->video_formats : [],
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
            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';
            $authUserId = Auth::id();

            $prefixIfNeeded = function ($path) use ($s3BaseUrl) {
                if (empty($path)) return null;
                if (preg_match('/^https?:\/\//', $path)) return $path;
                return rtrim($s3BaseUrl, '/') . '/' . ltrim($path, '/');
            };

            // 🔹 Fetch user
            $user = User::where('id', $user_id)->first();

            if (!$user) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'User not found or deleted',
                ], 404);
            }

            // 🔹 Pagination
            $limit  = max((int) $request->get('limit', 10), 1);
            $page   = max((int) $request->get('page', 1), 1);
            $offset = ($page - 1) * $limit;

            // 🔹 Videos
            $query = FinalWord::where('user_id', $user_id);
            $total = $query->count();

            $videos = $query->orderByDesc('id')
                ->skip($offset)
                ->take($limit)
                ->get()
                ->map(fn($fw) => [
                    'id'    => $fw->id,
                    'isPotrait'    => $fw->isPotrait,
                    'video' => $fw->video_path ? $prefixIfNeeded($fw->video_path) : null,
                    // 'video_formats' => $fw->video_path ? json_decode($fw->video_formats) : null,
                    'video_formats' => $fw->video_path ? $fw->video_formats : [],
                ]);

            // 🔹 User info
            $userdata = [
                "id"                 => $user->id,
                "first_name"         => $user->first_name,
                "last_name"          => $user->last_name,
                "email"              => $user->email,
                "role_id"            => $user->role_id,
                "phone"              => $user->phone,
                "image"              => $prefixIfNeeded($user->image),
                "company_name"       => $user->company_name,
                "company_address"    => $user->company_address,
                "company_logo"       => $prefixIfNeeded($user->company_logo),
                "is_approved"        => $user->is_approved,
                "stripe_customer_id" => $user->stripe_customer_id,
                "agreed_terms"       => $user->agreed_terms,
                "ban_user"           => $user->ban_user,
                "deleted_at"         => $user->deleted_at,
                "username"           => $user->username,
                "gender"             => $user->gender,
                "dob"                => $user->dob,
                "agree_on_receiving" => $user->agree_on_receiving,
                "country_code"       => $user->country_code,
                "is_private"         => $user->is_private,
                "is_dead"            => (bool) $user->is_dead,
                "description"        => $user->description,
            ];

            // 🔹 Check if logged-in user has access (is a trusted user)
            $isTrustedUser = TrustedUser::where('user_id', $user->id)
                ->where('trusted_user_id', $authUserId)
                ->where('status', 'accepted')
                ->exists();

            $get_trust_user = TrustedUser::where('user_id', $user->id)
                ->where('trusted_user_id', $authUserId)
                ->first();

            // 🔹 Check if current user marked the deceased
            $isMarkedByAuth = DeathConfirmation::where('user_id', $user->id)
                ->where('trusted_user_id', $authUserId)
                ->exists();

            // 🔹 Get trusted user IDs
            $trustedUserIDs = TrustedUser::where('user_id', $user->id)
                ->where('status', 'accepted')
                ->pluck('trusted_user_id');

            if ($isMarkedByAuth) {
                // ✅ Scenario 1: Auth user marked deceased → show all admins
                $manage_user = User::whereNull('deleted_at')
                    ->where('role_id', 2)
                    ->whereIn('id', $trustedUserIDs)
                    ->get();
            } else {
                // ✅ Scenario 2: Someone else marked deceased → show only those who marked
                $markedUsers = DeathConfirmation::where('user_id', $user->id)
                    ->pluck('trusted_user_id');

                $manage_user = User::whereNull('deleted_at')
                    ->where('role_id', 2)
                    ->whereIn('id', $markedUsers)
                    ->get();
            }

            // 🔹 Prepare manage_user_list
            $manage_user_list = $manage_user->map(function ($trust_user) use ($s3BaseUrl, $user, $prefixIfNeeded) {
                $checkMarkdeath = DeathConfirmation::where('user_id', $user->id)
                    ->where('trusted_user_id', $trust_user->id)
                    ->first();

                if ($checkMarkdeath && $checkMarkdeath->status == 'confirmed') {
                    $mark_death_yes_not = 1;
                } elseif ($checkMarkdeath && $checkMarkdeath->status == 'not_confirmed') {
                    $mark_death_yes_not = 2;
                } else {
                    $mark_death_yes_not = 0;
                }

                return [
                    'user_id'                 => $trust_user->id,
                    'first_name'              => $trust_user->first_name,
                    'last_name'               => $trust_user->last_name,
                    'email'                   => $trust_user->email,
                    'image'                   => $prefixIfNeeded($trust_user->image),
                    'is_mark_death_or_not'    => $checkMarkdeath ? 1 : 0,
                    'death_confirmation_data' => $checkMarkdeath->status ?? null,
                    'mark_death_yes_not'      => $mark_death_yes_not,
                ];
            });

            // ✅ Final response
            if(isset($get_trust_user) && $get_trust_user->status == 'accepted')
            {
                return response()->json([
                    'message' => 'Final Words retrieved successfully',
                    'status'  => 'success',
                    'data'    => [
                        'user'             => $userdata,
                        'is_trusted_user'  => $isTrustedUser ? 1 : 0,
                        'manage_user_list' => $manage_user_list,
                        'videos'           => $videos,
                        'count'            => $total,
                        'page'             => $page,
                        'limit'            => $limit,
                        'total_pages'      => ceil($total / $limit),
                    ],
                ], 200);
            }else{
                return response()->json([
                    'message' => 'Final Words retrieved successfully',
                    'status'  => 'success',
                    'data'    => [],
                ], 200);
            }
            

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
                'isPotrait'=> 'nullable'
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
                    'video_formats' => $videoPath,
                    'user_id'    => $userId,
                    'isPotrait'    => $request->isPotrait?? true
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
