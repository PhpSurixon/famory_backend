<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\SchedulingPost;
use App\Models\AlbumPost;
use App\Models\PostMember;
use App\Models\FamilyMember;
use App\Models\MemberGroup;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use App\Traits\FormatResponseTrait;
use DB;
use App\Services\UploadImage;
use Illuminate\Support\Carbon;

class PostController extends Controller
{
    use OneSignalTrait;
    use FormatResponseTrait;

    protected $UploadImage;

    public function __construct(UploadImage $UploadImage)
    {
        $this->UploadImage = $UploadImage;
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

    function getFileType($extension) {
        $extension = strtolower($extension);
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
            default:
                return 'unknown';
        }
    }


    // public function create(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'title'         => 'required',
    //         'media_type'    => 'required|in:audio,video,picture,note',
    //         'post_type'     => 'required|in:private,public,album',
    //         'schedule_type' => 'required|in:now,date-time,when-pass',
    //         'description'   => 'required',
    //         'tag_id'        => 'nullable',
    //         'reoccurring_type' => 'required|in:no,yes',
    //         'media'         => 'nullable|file',
    //         'video_formats' => 'nullable|file',
    //         'album_id'      => 'nullable|exists:albums,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
    //     }

    //     if ($request->post_type =='private') 
    //     {
    //         if(empty($request->following_user_id))
    //         {
    //              return response()->json(['message' => "Please Pass Follower User ID", 'status' => 'failed'], 400);
    //         }
    //     }

    //     if ($request->post_type =='album') 
    //     {
    //         if(!isset($request->album_id) && empty($request->album_id))
    //         {
    //              return response()->json(['message' => "Album ID required for Post Type Album", 'status' => 'failed'], 400);
    //         }
    //     }

    //     if ($request->schedule_type =='date-time'||$request->schedule_type =='now') 
    //     {
    //         if(empty($request->schedule_date) && empty($request->schedule_time))
    //         {
    //             return response()->json(['message' => "Required schedule date && schedule time", 'status' => 'failed'], 400);   
    //         }
    //     }

    //     if($request->reoccurring_type =='yes')
    //     {
    //         if(empty($request->reoccurring_time)){
    //             return response()->json(['message' => "Required Reoccurring Time (weekly,monthly,yearly)", 'status' => 'failed'], 400);
    //         }
    //     }

    //     DB::beginTransaction();
    //     try 
    //     {
    //       $getHeaders = apache_request_headers();
    //       $timezone = $getHeaders['time_zone'] ?? 'UTC';

    //       // Upload media if present
    //       $fileUploadSuccess = true;
    //       $filePath = null;
    //       $videoPath = null;  

          

    //       if ($request->hasFile('media') && $request->file('media')->isValid()) 
    //       {
    //             $file = $request->file('media');
    //             $extension = $file->getClientOriginalExtension();
    //             $folder = $this->getFolderName($extension);
    //             $userId = Auth::id();

    //             try {
    //                 $res = $this->UploadImage->saveMedia($file, $userId);

    //                 if ($folder === 'videos') {
    //                     $videoPath = $res;
    //                 } else {
    //                     $filePath = $res;
    //                 }
    //             } catch (\Exception $e) {
    //                 $fileUploadSuccess = false;
    //                 return response()->json(['message' => 'File upload failed: ' . $e->getMessage(), 'status' => 'failed'], 500);
    //             }
    //       }

    //       if ($fileUploadSuccess)
    //       {
    //              $post                      = new Post();
    //              $post->tag_id              = $request->tag_id??null;
    //              $post->title               = $request->title;
    //              $post->description         = $request->description;
    //              $post->media_type          = $request->media_type;
    //              $post->file                = $filePath;
    //              $post->video_formats       = $videoPath;
    //              $post->post_type           = $request->post_type;
    //              $post->album_id            = $request->album_id ?? null;
    //              $post->user_id             = Auth::id();
    //              $post->save();

    //              // Scheduling
    //             $scheduledDateTime = Carbon::parse($request->schedule_date . ' ' . $request->schedule_time, $timezone)->setTimezone('UTC');

    //             $schedule                   = new SchedulingPost();
    //             $schedule->post_id          = $post->id;
    //             $schedule->timezone         = $timezone;
    //             $schedule->schedule_type    = $request->schedule_type;
    //             $schedule->is_post          = ($request->schedule_type == "now") ? 1 : 0;
    //             $schedule->schedule_date    = $scheduledDateTime->toDateString();
    //             $schedule->schedule_time    = $scheduledDateTime->toTimeString();
    //             $schedule->reoccurring_type = $request->reoccurring_type;
    //             if ($request->reoccurring_type == "yes") {
    //                 $schedule->reoccurring_time = $request->reoccurring_time;
    //             }
    //             $schedule->save();

    //             if(isset($request->album_id)){
    //                 $albumPost = new AlbumPost();
    //                 $albumPost->album_id = $request->album_id;
    //                 $albumPost->post_id = $post->id;
    //                 $albumPost->user_id = Auth::id();
    //                 $albumPost->save();
    //             }
    //             if ($post->post_type == "private" && !empty($request->following_user_id)) 
    //             {
    //                $memberIdsArray = explode(',', $request->following_user_id);
    //                foreach ($memberIdsArray as $singleMemberId) 
    //                {
    //                     if (!empty($singleMemberId)) 
    //                     {
    //                         $newMember              = new PostMember();
    //                         $newMember->post_id     = $post->id;
    //                         $newMember->post_by     = $post->user_id;
    //                         $newMember->member_id   = intval($singleMemberId);
    //                         $newMember->save();
    //                         $this->notifyMessage(Auth::user(), $singleMemberId, null, 'post');
    //                     }
    //                 }
    //             }

    //             DB::commit();
    //             return response()->json(['message' => 'You have created a new post!', 'status' => 'success', 'data' => $post], 200);

    //       }

    //       return response()->json(['message' => 'No file uploaded, post not created', 'status' => 'failed'], 400);

    //     } catch (\Exception $exception) {
    //         DB::rollBack();
    //         return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
    //     }
    // }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'            => 'required|string|max:255',
            'media_type'       => 'required|in:audio,video,picture,note',
            'post_type'        => 'required|in:private,public,album',
            'schedule_type'    => 'required|in:now,date-time,when-pass',
            'description'      => 'required|string',
            'tag_id'           => 'nullable|integer',
            'reoccurring_type' => 'required|in:no,yes',
            'media'            => 'nullable|file',
            'video_formats'    => 'nullable|file',
            'album_id'         => 'nullable|exists:albums,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 'failed'
            ], 400);
        }

        // Extra conditions
        if ($request->post_type == 'private' && empty($request->following_user_id)) {
            return response()->json([
                'message' => "Please pass Following User ID for private posts",
                'status'  => 'failed'
            ], 400);
        }

        if ($request->post_type == 'album' && empty($request->album_id)) {
            return response()->json([
                'message' => "Album ID is required for Post Type Album",
                'status'  => 'failed'
            ], 400);
        }

        if (in_array($request->schedule_type, ['date-time', 'now'])) {
            if (empty($request->schedule_date) || empty($request->schedule_time)) {
                return response()->json([
                    'message' => "Schedule date and time are required",
                    'status'  => 'failed'
                ], 400);
            }
        }

        if ($request->reoccurring_type == 'yes' && empty($request->reoccurring_time)) {
            return response()->json([
                'message' => "Reoccurring time (weekly, monthly, yearly) is required",
                'status'  => 'failed'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $getHeaders = apache_request_headers();
            $timezone   = $getHeaders['time_zone'] ?? 'UTC';

            $filePath   = null;
            $videoPath  = null;

            // File Upload
            if ($request->hasFile('media') && $request->file('media')->isValid()) {
                $file      = $request->file('media');
                $extension = $file->getClientOriginalExtension();
                $folder    = $this->getFolderName($extension);
                $userId    = Auth::id();

                try {
                    $res = $this->UploadImage->saveMedia($file, $userId);

                    if ($folder === 'videos') {
                        $videoPath = $res;
                    } else {
                        $filePath  = $res;
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'File upload failed: ' . $e->getMessage(),
                        'status'  => 'failed'
                    ], 500);
                }
            }

            // Create Post
            $post                  = new Post();
            $post->tag_id          = $request->tag_id ?? null;
            $post->title           = $request->title;
            $post->description     = $request->description;
            $post->media_type      = $request->media_type;
            $post->file            = $filePath;
            $post->video_formats   = $videoPath;
            $post->post_type       = $request->post_type;
            $post->album_id        = $request->album_id ?? null;
            $post->user_id         = Auth::id();
            $post->save();

            // Schedule
            if ($request->schedule_type !== "when-pass") {
                $scheduledDateTime = Carbon::parse(
                    $request->schedule_date . ' ' . $request->schedule_time,
                    $timezone
                )->setTimezone('UTC');

                $schedule                   = new SchedulingPost();
                $schedule->post_id          = $post->id;
                $schedule->timezone         = $timezone;
                $schedule->schedule_type    = $request->schedule_type;
                $schedule->is_post          = ($request->schedule_type == "now") ? 1 : 0;
                $schedule->schedule_date    = $scheduledDateTime->toDateString();
                $schedule->schedule_time    = $scheduledDateTime->toTimeString();
                $schedule->reoccurring_type = $request->reoccurring_type;

                if ($request->reoccurring_type == "yes") {
                    $schedule->reoccurring_time = $request->reoccurring_time;
                }

                $schedule->save();
            }

            // Album post
            if ($request->post_type == "album" && $request->album_id) {
                $albumPost           = new AlbumPost();
                $albumPost->album_id = $request->album_id;
                $albumPost->post_id  = $post->id;
                $albumPost->user_id  = Auth::id();
                $albumPost->save();
            }

            // Private post members
            if ($post->post_type == "private" && !empty($request->following_user_id)) {
                $memberIdsArray = explode(',', $request->following_user_id);
                foreach ($memberIdsArray as $singleMemberId) {
                    if (!empty($singleMemberId)) {
                        $newMember            = new PostMember();
                        $newMember->post_id   = $post->id;
                        $newMember->post_by   = $post->user_id;
                        $newMember->member_id = intval($singleMemberId);
                        $newMember->save();

                        $this->notifyMessage(Auth::user(), $singleMemberId, null, 'post');
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'You have created a new post!',
                'status'  => 'success',
                'data'    => $post
            ], 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function edit(Request $request, $postId)
    {
        $validator = Validator::make($request->all(), [
            'title'            => 'sometimes|required|string|max:255',
            'media_type'       => 'sometimes|required|in:audio,video,picture,note',
            'post_type'        => 'sometimes|required|in:private,public,album',
            'schedule_type'    => 'sometimes|required|in:now,date-time,when-pass',
            'description'      => 'sometimes|required|string',
            'tag_id'           => 'nullable|integer',
            'reoccurring_type' => 'sometimes|required|in:no,yes',
            'media'            => 'nullable|file',
            'video_formats'    => 'nullable|file',
            'album_id'         => 'nullable|exists:albums,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 'failed'
            ], 400);
        }

        DB::beginTransaction();
        try 
        {
            $post = Post::findOrFail($postId);

            // Permission check (only owner can edit)
            if ($post->user_id !== Auth::id()) {
                return response()->json([
                    'message' => "You are not allowed to edit this post",
                    'status'  => 'failed'
                ], 403);
            }

            $getHeaders = apache_request_headers();
            $timezone   = $getHeaders['time_zone'] ?? 'UTC';

            $filePath   = $post->file;          // keep old if not updated
            $videoPath  = $post->video_formats; // keep old if not updated

            // File Upload
            if ($request->hasFile('media') && $request->file('media')->isValid()) {
                $file      = $request->file('media');
                $extension = $file->getClientOriginalExtension();
                $folder    = $this->getFolderName($extension);
                $userId    = Auth::id();

                try {
                    $res = $this->UploadImage->saveMedia($file, $userId);

                    if ($folder === 'videos') {
                        $videoPath = $res;
                        $filePath  = null;
                    } else {
                        $filePath  = $res;
                        $videoPath = null;
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'File upload failed: ' . $e->getMessage(),
                        'status'  => 'failed'
                    ], 500);
                }
            }

            // Update Post
            $post->tag_id        = $request->tag_id ?? $post->tag_id;
            $post->title         = $request->title ?? $post->title;
            $post->description   = $request->description ?? $post->description;
            $post->media_type    = $request->media_type ?? $post->media_type;
            $post->file          = $filePath;
            $post->video_formats = $videoPath;
            $post->post_type     = $request->post_type ?? $post->post_type;
            $post->album_id      = $request->album_id ?? $post->album_id;
            $post->save();

            // Update schedule if provided
            if ($request->has('schedule_type')) {
                // remove old schedule
                SchedulingPost::where('post_id', $post->id)->delete();

                if ($request->schedule_type !== "when-pass") {
                    $scheduledDateTime = Carbon::parse(
                        $request->schedule_date . ' ' . $request->schedule_time,
                        $timezone
                    )->setTimezone('UTC');

                    $schedule                   = new SchedulingPost();
                    $schedule->post_id          = $post->id;
                    $schedule->timezone         = $timezone;
                    $schedule->schedule_type    = $request->schedule_type;
                    $schedule->is_post          = ($request->schedule_type == "now") ? 1 : 0;
                    $schedule->schedule_date    = $scheduledDateTime->toDateString();
                    $schedule->schedule_time    = $scheduledDateTime->toTimeString();
                    $schedule->reoccurring_type = $request->reoccurring_type ?? 'no';

                    if ($request->reoccurring_type == "yes") {
                        $schedule->reoccurring_time = $request->reoccurring_time;
                    }

                    $schedule->save();
                }
            }

            // Update album post relation
            if ($post->post_type == "album" && $request->album_id) {
                AlbumPost::updateOrCreate(
                    ['post_id' => $post->id],
                    ['album_id' => $request->album_id, 'user_id' => Auth::id()]
                );
            }

            // Update private post members
            if ($post->post_type == "private" && $request->filled('following_user_id')) {
                // remove old members
                PostMember::where('post_id', $post->id)->delete();

                $memberIdsArray = explode(',', $request->following_user_id);
                foreach ($memberIdsArray as $singleMemberId) {
                    if (!empty($singleMemberId)) {
                        $newMember            = new PostMember();
                        $newMember->post_id   = $post->id;
                        $newMember->post_by   = $post->user_id;
                        $newMember->member_id = intval($singleMemberId);
                        $newMember->save();

                        $this->notifyMessage(Auth::user(), $singleMemberId, null, 'post');
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Post updated successfully!',
                'status'  => 'success',
                'data'    => $post
            ], 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function view(Request $request,$postId = null)
    {
        try 
        {
            if (empty($postId)) 
            {
                return response()->json([
                    'message' => "Post ID is required",
                    'status'  => 'failed'
                ], 500);
            }
            $currentUser = Auth::user();

            $getPost = Post::with('scheduling_post','post_member.user_new')
                           ->where('user_id', $currentUser->id)
                           ->where('id',$postId)
                           ->first();

            if($getPost)
            {
                return response()->json([
                    'message' => 'Post Details successfully!',
                    'status'  => 'success',
                    'data'    => $getPost
                ], 200);


            }else{
                return response()->json([
                    'message' => "Post not found",
                    'status'  => 'failed',
                    'data'    => []
                ], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

    public function list(Request $request)
    {
        try 
        {
            $currentUser = Auth::id();
            

        } catch (\Exception $exception) {
            return response()->json(['message'=>$exception->getMessage(),'status'=>'failed'],500);
        }
    }





}
