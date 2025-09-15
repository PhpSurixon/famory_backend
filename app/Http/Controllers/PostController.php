<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\FormatResponseTrait;
use App\Notifications\CommentReported;
// require '/home3/famcamb/public_html/backend/vendor/domPDF/autoload.php';

// require '../vendor/domPDF/autoload.php';
use App\Models\UserGroup;
use App\Models\FamilyMember;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\SchedulingPost;
use App\Models\FollowerUnfollwer;
use App\Models\AlbumPost;
use App\Models\Album;
use App\Models\BurialInfo;
use App\Models\PostMember;
use App\Models\Notification;
use App\Models\PostReport;
use App\Models\StopeSeekingPost;
use App\Models\Like;
use App\Models\FamilyTagId;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Report;
use App\Models\Follow;
use App\Notifications\CommentAddedNotification;
use App\Notifications\CommentReplyNotification;
use Illuminate\Support\Collection;

use App\Traits\OneSignalTrait;
// google cloud 
// require '/home3/famcamb/public_html/backend/vendor/vendor/autoload.php';

require '../vendor/autoload.php';
use App\Services\UploadImage;
use Symfony\Component\HttpKernel\Profiler\Profile;

class PostController extends Controller
{ 
    use OneSignalTrait;
    use FormatResponseTrait;


    public function addGroup(Request $request){
         try{
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            $exitsData = UserGroup::where('name',$request->name)->first();
            if(!$exitsData){
                $addGroup = new UserGroup;
                $addGroup->name = $request->name;
                $addGroup->save();
                if(!$addGroup){
                    return $this->errorResponse("Group not added", 'data_not_add', 400);
                }
                return $this->successResponse("Group Added successfully",200,$addGroup);
            }else{
                 return $this->errorResponse("Already exists", 'already_exists', 400);
            }

            
            
        }catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    public function search(Request $request){
         try{
            $validator = Validator::make($request->all(), [
                'search' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }

            $query = User::query();

            if ($request->search) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('first_name', 'like', $searchTerm)
                        ->orWhere('last_name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm);
                });
            }
            
            $users = $query->paginate(10);
            
            if(!$users){
                return $this->errorResponse("Not found any user", 'data_not_found', 400);
            }
            return $this->successResponse("User get successfully",200,$users->items(),$users);
            
        }catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    

    protected $storageClient;

    
    
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
 
     // google cloud stoorage to upload post images, videos , documents
    
    public function createPostOLD(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'post_type' => 'required',
            'tag_id' => 'nullable',
            'description' => 'required',
            'schedule_type' => 'required',
            'reoccurring_type' => 'required',
            'media' => 'nullable|file',
            'video_formats' => 'nullable|file',
            'album_id' => 'nullable|exists:albums,id',
            'media_type' => 'required|in:audio,video,picture,note',
        ]);
    
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
        DB::beginTransaction();
                try {
                    $getHeaders = apache_request_headers();
                    $timezone = isset($getHeaders['time_zone']) ? $getHeaders['time_zone'] : 'UTC';
                    
                    if($request->tag_id){
                        $isValid = FamilyTagId::where(['family_tag_id'=>$request->tag_id,'user_id'=>Auth::id()])->first();
                        if(!$isValid){
                            return $this->errorResponse("Famery Tag is not valid please check", 'id_not_valid', 400);
                        }
                    }
                    
                    
                    $post = new Post;
                    $post->tag_id = $request->tag_id;
                    $post->title = $request->title;
                    $post->description = $request->description;
                    $post->media_type = $request->media_type;
            
                    if ($request->hasFile('media') && $request->file('media')->isValid()) {
                        $file = $request->file('media');
                        $extension = $file->getClientOriginalExtension();
                        $folder = $this->getFolderName($extension);
                        $userId = Auth::id();
                        
                        $res = $this->UploadImage->saveMedia($file,$userId);
                        if($folder === 'videos'){
                             $post->video_formats = $res;
                        }else{
                             $post->file = $res;
                        }
 
                     
                    }
                    //aak //patasa //
                    $scheduledDateTime = Carbon::parse($request->schedule_date . ' ' . $request->schedule_time, $timezone)->setTimezone('UTC');
        
                    $post->post_type = $request->post_type;
                    $post->album_id = $request->has('album_id') ? $request->album_id : null;
                    $post->user_id = Auth::user()->id;
                    $post->save();
                    
                    $schedule = new SchedulingPost;
                    $schedule->post_id = $post->id;
                    $schedule->timezone = $timezone;
                    $schedule->schedule_type = $request->schedule_type;
                    $schedule->is_post = ($request->schedule_type == "now") ? 1 : 0 ;
                    // $schedule->schedule_time = $request->schedule_time;
                    // $schedule->schedule_date = date('Y-m-d', strtotime($request->schedule_date));
                    $schedule->schedule_date = $scheduledDateTime->toDateString();
                    $schedule->schedule_time = $scheduledDateTime->toTimeString();
                    $schedule->reoccurring_type = $request->reoccurring_type;
                
                    if ($request->reoccurring_type == "yes") {
                        $schedule->reoccurring_time = $request->reoccurring_time;
                    }
                
                    $schedule->save();
                    
                    // Save post in album
                    if($request->schedule_type == "now"){
                        if($request->album_id){
                            $albumPost = new AlbumPost();
                            $albumPost->album_id = $request->album_id;
                            $albumPost->post_id = $post->id;
                            $albumPost->user_id = Auth::user()->id;
                            $albumPost->save();
                            
                            
                            // Create Album Cover
                            $thumbnailPath = null;
                            $fileExtension = strtolower(pathinfo($post->file, PATHINFO_EXTENSION));
                            $imgExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg'];
                            $videoExtensions = ['mp4', 'mov', 'ogg'];
            
                           if (in_array($fileExtension, $videoExtensions)) {
                                $videoFilename = basename($post->file);
                                $thumbnailFilename = public_path('thumbnails/' . pathinfo($videoFilename, PATHINFO_FILENAME) . '.jpg');
            
                                if (!file_exists($thumbnailFilename)) {
                                    try {
                                        $command = "ffmpeg -i " . escapeshellarg($post->file) . " -ss 00:00:01.000 -vframes 1 " . escapeshellarg($thumbnailFilename);
                                        shell_exec($command);
                                    } catch (\Exception $e) {
                                        return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
                                    }
                                }
                            
                                $thumbnailPath = "https://admin.famoryapp.com/thumbnails/" . pathinfo($videoFilename, PATHINFO_FILENAME) . '.jpg';
                            } elseif (in_array($fileExtension, $imgExtensions)) {
                                $thumbnailPath = $post->file;
                            }
                            
                            if(!empty($thumbnailPath)){
                                $album = Album::find($post->album_id);
                                $album->album_cover = $thumbnailPath;
                                $album->save();
                            }
                        }
                    }
                    
                    if($post->post_type == "family"){
                        if (!empty($request->member_id)) {
                            $memberIds = $request->member_id;
                            foreach ($memberIds as $memberId) {
                                $memberIdsArray = explode(',', $memberId);
                                foreach ($memberIdsArray as $singleMemberId) {
                                    if (!empty($singleMemberId)) {
                                        $newMember = new PostMember;
                                        $newMember->post_id = $post->id;
                                        $newMember->post_by = $post->user_id;
                                        $newMember->member_id = intval($singleMemberId);
                                        $newMember->save();
                                        $this->notifyMessage(Auth::user(), $singleMemberId, null, 'post');
                                    }
                                }
                            }
                        }
                    }
                    
                    // Handle scheduling post logic (not shown for brevity)
                    DB::commit();
            
                    return response()->json(['message' => 'You have created a new post!', 'status' => 'success', 'data' => $post], 200);
            
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }

    public function createPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'post_type' => 'required',
            'tag_id' => 'nullable',
            'description' => 'required',
            'schedule_type' => 'required',
            'reoccurring_type' => 'required',
            'media' => 'nullable|file',
            'video_formats' => 'nullable|file',
            'album_id' => 'nullable|exists:albums,id',
            'media_type' => 'required|in:audio,video,picture,note',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
        }

        DB::beginTransaction();
        try {
            $getHeaders = apache_request_headers();
            $timezone = $getHeaders['time_zone'] ?? 'UTC';

            // Validate Tag
            if ($request->tag_id) {
                $isValid = FamilyTagId::where(['family_tag_id' => $request->tag_id, 'user_id' => Auth::id()])->first();
                if (!$isValid) {
                    return $this->errorResponse("Famery Tag is not valid, please check", 'id_not_valid', 400);
                }
            }

            // Upload media if present
            $fileUploadSuccess = true;
            $filePath = null;
            $videoPath = null;

            if ($request->hasFile('media') && $request->file('media')->isValid()) {
                
                $file = $request->file('media');
                $extension = $file->getClientOriginalExtension();
                $folder = $this->getFolderName($extension);
                $userId = Auth::id();

                try {
                    $res = $this->UploadImage->saveMedia($file, $userId);

                    if ($folder === 'videos') {
                        $videoPath = $res;
                    } else {
                        $filePath = $res;
                    }
                } catch (\Exception $e) {
                    $fileUploadSuccess = false;
                    return response()->json(['message' => 'File upload failed: ' . $e->getMessage(), 'status' => 'failed'], 500);
                }
            }

            // Only create post if file upload succeeded
            if ($fileUploadSuccess) {
                $post = new Post();
                $post->tag_id = $request->tag_id;
                $post->title = $request->title;
                $post->description = $request->description;
                $post->media_type = $request->media_type;
                $post->file = $filePath;
                $post->video_formats = $videoPath;
                $post->post_type = $request->post_type;
                $post->album_id = $request->album_id ?? null;
                $post->user_id = Auth::id();
                $post->save();

                // Scheduling
                $scheduledDateTime = Carbon::parse($request->schedule_date . ' ' . $request->schedule_time, $timezone)
                                          ->setTimezone('UTC');

                $schedule = new SchedulingPost();
                $schedule->post_id = $post->id;
                $schedule->timezone = $timezone;
                $schedule->schedule_type = $request->schedule_type;
                $schedule->is_post = ($request->schedule_type == "now") ? 1 : 0;
                $schedule->schedule_date = $scheduledDateTime->toDateString();
                $schedule->schedule_time = $scheduledDateTime->toTimeString();
                $schedule->reoccurring_type = $request->reoccurring_type;
                if ($request->reoccurring_type == "yes") {
                    $schedule->reoccurring_time = $request->reoccurring_time;
                }
                $schedule->save();

                // Album post logic
                if ($request->schedule_type == "now" && $request->album_id) {
                    $albumPost = new AlbumPost();
                    $albumPost->album_id = $request->album_id;
                    $albumPost->post_id = $post->id;
                    $albumPost->user_id = Auth::id();
                    $albumPost->save();
                }

                // Family post member logic
                if ($post->post_type == "family" && !empty($request->member_id)) {
                    foreach ($request->member_id as $memberId) {
                        $memberIdsArray = explode(',', $memberId);
                        foreach ($memberIdsArray as $singleMemberId) {
                            if (!empty($singleMemberId)) {
                                $newMember = new PostMember();
                                $newMember->post_id = $post->id;
                                $newMember->post_by = $post->user_id;
                                $newMember->member_id = intval($singleMemberId);
                                $newMember->save();
                                $this->notifyMessage(Auth::user(), $singleMemberId, null, 'post');
                            }
                        }
                    }
                }

                DB::commit();
                return response()->json(['message' => 'You have created a new post!', 'status' => 'success', 'data' => $post], 200);
            }

            return response()->json(['message' => 'No file uploaded, post not created', 'status' => 'failed'], 400);

        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }



    public function editPostOLD(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'post_type' => 'required',
            'tag_id' => 'nullable',
            'description' => 'required',
            'schedule_type' => 'required',
            'reoccurring_type' => 'required',
            'media' => 'nullable|file',
            'video_formats' => 'nullable|file',
            'media_type' => 'required|in:audio,video,picture,note',
            'album_id' => 'nullable|exists:albums,id', 
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return response()->json(['message' => $value, 'status' => 'failed'], 400);
            }
        }
    
        DB::beginTransaction();
        try {
            $getHeaders = apache_request_headers();
            $timezone = isset($getHeaders['time_zone']) ? $getHeaders['time_zone'] : 'UTC';
            
            if($request->tag_id){
                $isValid = FamilyTagId::where(['family_tag_id'=>$request->tag_id,'user_id'=>Auth::id()])->first();
                if(!$isValid){
                    return $this->errorResponse("Famery Tag is not valid please check", 'id_not_valid', 400);
                }
            }
            
            if ($request->post_type == "family") {
                $memberIds = $request->member_id;
            }
            
            $post = Post::findOrFail($id);
            $post->tag_id = $request->tag_id;
            $post->title = $request->title;
            $post->description = $request->description;
            
            if ($request->media_type === 'note') {
            $post->file = null;
            $post->video_formats = null; 
            } else if ($request->hasFile('media') && $request->file('media')->isValid()) {
                $file = $request->file('media');
                $extension = $file->getClientOriginalExtension();
                $folder = $this->getFolderName($extension);
                $userId = Auth::id();
                
                $res = $this->UploadImage->saveMedia($file,$userId);
                if($folder === 'videos'){
                     $post->video_formats = $res;
                      $post->file = null;
                }else{
                     $post->file = $res;
                     $post->video_formats = null;
                }
                
            }
            $scheduledDateTime = Carbon::parse($request->schedule_date . ' ' . $request->schedule_time, $timezone)->setTimezone('UTC');
            $post->media_type = $request->media_type;
            $post->post_type = $request->post_type;
            // $post->album_id = $request->album_id;
            $post->album_id = $request->has('album_id') ? $request->album_id : null;
            $post->user_id = Auth::user()->id;
            $post->save();
    
            $schedule = SchedulingPost::where('post_id', $post->id)->first();
            $schedule->timezone = $timezone;
            $schedule->schedule_type = $request->schedule_type;
            $schedule->is_post = ($request->schedule_type == "now") ? 1 : 0;
            $schedule->schedule_date = $scheduledDateTime->toDateString();
            $schedule->schedule_time = $scheduledDateTime->toTimeString();
            $schedule->reoccurring_type = $request->reoccurring_type;
    
            if ($request->reoccurring_type == "yes") {
                $schedule->reoccurring_time = $request->reoccurring_time;
            }
    
            $schedule->save();
    
            // Update post in album
            
            if ($request->schedule_type == "now") {
                if ($request->album_id) {
                    $albumPost = AlbumPost::where('post_id', $post->id)->first();
                    if (!$albumPost) {
                        $albumPost = new AlbumPost;
                        $albumPost->user_id = Auth::id();
                        $albumPost->post_id = $post->id;
                        $albumPost->album_id = $request->album_id;
                        $albumPost->save();
                    } else {
                        $albumPost->user_id = Auth::id();
                        $albumPost->album_id = $request->album_id;
                        $albumPost->save();
                    }
    
                    // Create Album Cover
                    $thumbnailPath = null;
                    $fileExtension = strtolower(pathinfo($post->file, PATHINFO_EXTENSION));
                    $imgExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg'];
                    $videoExtensions = ['mp4', 'mov', 'ogg'];
    
                    if (in_array($fileExtension, $videoExtensions)) {
                        $videoFilename = basename($post->file);
                        $thumbnailFilename = public_path('thumbnails/' . pathinfo($videoFilename, PATHINFO_FILENAME) . '.jpg');
    
                        if (!file_exists($thumbnailFilename)) {
                            try {
                                $command = "ffmpeg -i " . escapeshellarg($post->file) . " -ss 00:00:01.000 -vframes 1 " . escapeshellarg($thumbnailFilename);
                                shell_exec($command);
                            } catch (\Exception $e) {
                                return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
                            }
                        }
    
                        $thumbnailPath = "https://admin.famoryapp.com/thumbnails/" . pathinfo($videoFilename, PATHINFO_FILENAME) . '.jpg';
                    } elseif (in_array($fileExtension, $imgExtensions)) {
                        $thumbnailPath = $post->file;
                    }
    
                    if (!empty($thumbnailPath)) {
                        $album = Album::find($post->album_id);
                        $album->album_cover = $thumbnailPath;
                        $album->save();
                    }
                }
            }
    
            if ($post->post_type == "family") {
                $memberIds = $request->member_id;
                    PostMember::where(['post_id'=>$post->id,'post_by' => $post->user_id])->delete();
                    foreach ($memberIds as $memberId) {
                        $memberIds = explode(',', $memberId);
                        foreach ($memberIds as $memberId) {
                            $newMember = new PostMember;
                            $newMember->post_id = $post->id;
                            $newMember->post_by = $post->user_id;
                            $newMember->member_id = $memberId;
                            $newMember->save();
                        }
                    }
                
            }
    
            DB::commit();
    
            return response()->json(['message' => 'Post updated successfully', 'status' => 'success','error_type' => " ",'data' => $post], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return response()->json(['message' => 'Post not found', 'status' => 'failed', 'error_type' => " "], 404);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed', 'error_type' => ''], 500);
        }
    }

    public function editPost(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'post_type' => 'required',
            'tag_id' => 'nullable',
            'description' => 'required',
            'schedule_type' => 'required',
            'reoccurring_type' => 'required',
            'media' => 'nullable|file',
            'video_formats' => 'nullable|file',
            'media_type' => 'required|in:audio,video,picture,note',
            'album_id' => 'nullable|exists:albums,id', 
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return response()->json(['message' => $value, 'status' => 'failed'], 400);
            }
        }
    
        DB::beginTransaction();
        try {
            $getHeaders = apache_request_headers();
            $timezone = isset($getHeaders['time_zone']) ? $getHeaders['time_zone'] : 'UTC';
            
            if($request->tag_id){
                $isValid = FamilyTagId::where(['family_tag_id'=>$request->tag_id,'user_id'=>Auth::id()])->first();
                if(!$isValid){
                    return $this->errorResponse("Famery Tag is not valid please check", 'id_not_valid', 400);
                }
            }
            
            if ($request->post_type == "family") {
                $memberIds = $request->member_id;
            }
            
            $post = Post::findOrFail($id);
            $post->tag_id = $request->tag_id;
            $post->title = $request->title;
            $post->description = $request->description;
            
            if ($request->media_type === 'note') {
            $post->file = null;
            $post->video_formats = null; 
            } else if ($request->hasFile('media') && $request->file('media')->isValid()) {
                $file = $request->file('media');
                $extension = $file->getClientOriginalExtension();
                $folder = $this->getFolderName($extension);
                $userId = Auth::id();
                
                $res = $this->UploadImage->saveMedia($file,$userId);
                if($folder === 'videos'){
                     $post->video_formats = $res;
                      $post->file = null;
                }else{
                     $post->file = $res;
                     $post->video_formats = null;
                }
                
            }
            $scheduledDateTime = Carbon::parse($request->schedule_date . ' ' . $request->schedule_time, $timezone)->setTimezone('UTC');
            $post->media_type = $request->media_type;
            $post->post_type = $request->post_type;
            // $post->album_id = $request->album_id;
            $post->album_id = $request->has('album_id') ? $request->album_id : null;
            $post->user_id = Auth::user()->id;
            $post->save();
    
            $schedule = SchedulingPost::where('post_id', $post->id)->first();
            $schedule->timezone = $timezone;
            $schedule->schedule_type = $request->schedule_type;
            $schedule->is_post = ($request->schedule_type == "now") ? 1 : 0;
            $schedule->schedule_date = $scheduledDateTime->toDateString();
            $schedule->schedule_time = $scheduledDateTime->toTimeString();
            $schedule->reoccurring_type = $request->reoccurring_type;
    
            if ($request->reoccurring_type == "yes") {
                $schedule->reoccurring_time = $request->reoccurring_time;
            }
    
            $schedule->save();
    
            // Update post in album
            
            if ($request->schedule_type == "now") {
                if ($request->album_id) {
                    $albumPost = AlbumPost::where('post_id', $post->id)->first();
                    if (!$albumPost) {
                        $albumPost = new AlbumPost;
                        $albumPost->user_id = Auth::id();
                        $albumPost->post_id = $post->id;
                        $albumPost->album_id = $request->album_id;
                        $albumPost->save();
                    } else {
                        $albumPost->user_id = Auth::id();
                        $albumPost->album_id = $request->album_id;
                        $albumPost->save();
                    }
                }
            }
    
            if ($post->post_type == "family") {
                $memberIds = $request->member_id;
                    PostMember::where(['post_id'=>$post->id,'post_by' => $post->user_id])->delete();
                    foreach ($memberIds as $memberId) {
                        $memberIds = explode(',', $memberId);
                        foreach ($memberIds as $memberId) {
                            $newMember = new PostMember;
                            $newMember->post_id = $post->id;
                            $newMember->post_by = $post->user_id;
                            $newMember->member_id = $memberId;
                            $newMember->save();
                        }
                    }
                
            }
    
            DB::commit();
    
            return response()->json(['message' => 'Post updated successfully', 'status' => 'success','error_type' => " ",'data' => $post], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return response()->json(['message' => 'Post not found', 'status' => 'failed', 'error_type' => " "], 404);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed', 'error_type' => ''], 500);
        }
    }



    public function deletePost($id)
    {
            DB::beginTransaction();
            try {
                $post = Post::findOrFail($id);
                SchedulingPost::where('post_id', $post->id)->delete();
                AlbumPost::where('post_id', $post->id)->delete();
                PostMember::where('post_id', $post->id)->delete();
                Like::where('post_id', $post->id)->delete();
                Notification::where('post_id', $post->id)->delete();
                StopeSeekingPost::where('post_id', $post->id)->delete();
                PostReport::where('post_id', $post->id)->delete();
                $post->delete();
                DB::commit();
                return response()->json(['message' => 'Post deleted successfully', 'status' => 'success','error_type' => " "], 200);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
                 DB::rollBack();
                return response()->json(['message' => 'Post not found', 'status' => 'failed', 'error_type' => " "], 404);
            } catch (\Exception $exception) {
                 DB::rollBack();
                return response()->json(['message' => $exception->getMessage(), 'status' => 'failed', 'error_type' => ''], 500);
            }
   }
    private function sanitizeFileName($fileName) {
        // Replace any non-alphanumeric characters with an underscore
        $sanitizedFileName = preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName);
        return $sanitizedFileName;
    }

    
    public function getPost(Request $request)
    {
        try {
           
            $followedata = new Collection();
            $topPosts = new Collection();
            $viewTop = false;
            $currentUser = Auth::user()->id;
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            
            if ($request->type == "my-post") {
                $getPost = Post::where('user_id', $currentUser);
            }
            elseif ($request->filled('user_id')) {
                $userId = $request->user_id;
                $getPost = Post::where('user_id', $userId);
                if($request->type == "open-world"){
                    $getPost = $getPost->where('post_type', 'public');
                }else{
                    
                    $getPost = $getPost->where('post_type', 'private');
                    
                    $Posts = Post::where('post_type','=','family')->get();
                    foreach($Posts as $post){
                        
                        $isPosted =  SchedulingPost::where(['is_post' => 1, 'post_id' => $post->id])->first();
                        
                        if($isPosted){
                            $getData = PostMember::where(['post_id'=> $post->id,'post_by'=> $request->user_id])->get();
                            $user_id = $request->user_id;
                            if($getData->isNotEmpty()){
                                $getPost->orWhere(function ($query) use ($post, $user_id) {
                                    $query->where('id', $post->id);
                                });
                            }
                        }
                    }
                    
                }
                
            } elseif ($request->filled('group_id')) {
                $groupId = $request->group_id;
                
                if($groupId == 1){
                    $groupMemberIds = Familymember::where(['group_id'=> $groupId, 'user_id'=>$currentUser])->pluck('member_id');
                    $groupUserIds  = Familymember::where(['group_id'=> $groupId, 'member_id'=>$currentUser])->pluck('user_id');
                    $allMemberIds = $groupMemberIds->merge($groupUserIds)->unique();
                    $getPost = Post::whereIn('user_id', $allMemberIds);
                }else{
                
                    $groupMemberIds = MemberGroup::where(['group_id'=> $groupId, 'user_id'=>$currentUser])->pluck('member_id');
                    $groupUserIds  = MemberGroup::where(['group_id'=> $groupId, 'member_id'=>$currentUser])->pluck('user_id');
                    $allMemberIds = $groupMemberIds->merge($groupUserIds)->unique();
                    $currentuser = ($groupId == 2) ? $allMemberIds : $allMemberIds->merge($currentUser)->unique();
                    $getPost = Post::whereIn('user_id', $currentuser);
                    
                }
                
            } else {
                if ($request->type == "open-world") {
                    $getPost = Post::where('post_type', 'public');
                }elseif ($request->type == "scheduled") {
                    $getPost = Post::where('user_id', $currentUser)
                        ->whereHas('scheduling_post', function ($query) {
                            $query->where('schedule_type', 'date-time')
                                   ->where('schedule_date', '>=', now()->format('Y-m-d'));
                        });
                    
                }elseif($request->type == "when-pass"){
                    
                    $getPost = Post::where('user_id', $currentUser)
                            ->whereHas('scheduling_post', function ($query) {
                                $query->where('schedule_type', 'when-pass');
                            });
                }else {
                    $familyAsUser = FamilyMember::where('user_id', $currentUser)->pluck('member_id');
                    $familyAsMember = FamilyMember::where('member_id', $currentUser)->pluck('user_id');
                    
                    // Convert both collections to one collection, then call merge
                    $familyMemberIds = $familyAsUser->merge($familyAsMember)->unique(); // Still a collection
                    
                    $friends = MemberGroup::where('user_id', $currentUser)->where('group_id', '2')->pluck('member_id');
                    
                    // Merge with friends collection, keep it as a collection until the final step
                    $allRelatedMemberIds = $familyMemberIds->merge($friends)->unique()->toArray(); // Convert to array after merging

                    
                    $query = Post::query();
                    if (!empty($allRelatedMemberIds)) {
                        $query->whereIn('user_id', $allRelatedMemberIds)
                              ->whereIn('post_type', ['private', 'public'])
                              ->whereHas('scheduling_post', function ($q) {
                                  $q->where('is_post', 1); // Ensure only posts with is_post = 1 are included
                              });
                        
                        // Fetch 'family' posts
                        $familyPosts = Post::whereIn('user_id', $allRelatedMemberIds)
                                        ->where('post_type', 'family')
                                        ->whereHas('scheduling_post', function ($q) {
                                            $q->where('is_post', 1); // Ensure only posts with is_post = 1 are included
                                        })->get();
                        
                        foreach ($familyPosts as $post) {
                            $isPosted = SchedulingPost::where(['is_post' => 1, 'post_id' => $post->id])->first();
                        
                            if ($isPosted) {
                                $getData = PostMember::where(['post_id' => $post->id, 'member_id' => $currentUser])->get();
                        
                                if ($getData->isNotEmpty()) {
                                    $query->orWhere(function ($query) use ($post, $currentUser) {
                                        $query->where('id', $post->id)
                                              ->where('user_id', '!=', $currentUser);
                                    });
                                }
                            }
                        }
                    }
                    
                    // Always include the current user's own posts
                    $getPost  = $query->orWhere(function ($query) use ($currentUser) {
                        $query->where('user_id', $currentUser)
                              ->whereIn('post_type', ['private'])
                              ->whereHas('scheduling_post', function ($q) {
                                  $q->where('is_post', 1);
                              });
                    });
                    
                    // $query->orWhere('post_type', 'public')->whereHas('scheduling_post', function ($q) {
                    //     $q->where('is_post', 1); // Ensure only posts with is_post = 1 are included
                    // });
                    
                    // Fetch follower users and their posts
                    $getFollowerUsers = FollowerUnfollwer::where('user_id', $currentUser)->get();
                    foreach ($getFollowerUsers as $getFollowerUser) {
                        $followerPostsQuery = Post::where('user_id', $getFollowerUser->following_id)
                              ->whereIn('post_type', ['private', 'public'])
                              ->whereHas('scheduling_post', function ($q) {
                                  $q->where('is_post', 1); // Ensure only posts with is_post = 1 are included
                              })->with('user')->orderBy('updated_at', 'desc')->take(5)->get();
                        if ($getFollowerUser->status == 0) {
                            $viewTop = true;
                        }
                        $followedata = $followedata->merge($followerPostsQuery);
                    }
                    
                }
            }
                 
            if($request->type == "when-pass" || $request->type == "scheduled"){
                 $query = $getPost->whereHas('scheduling_post', function ($query) {
                    $query->where('is_post', 0);
                })->with('user')->orderBy('updated_at', 'desc');
                 
            }elseif( $request->type == "my-post"){
                 $query = $getPost->whereHas('scheduling_post', function ($query) {
                    $query->whereIn('is_post',['1','0']);
                })->with('user')->orderBy('updated_at', 'desc');
            }else{
                $query = $getPost->whereHas('scheduling_post', function ($query) {
                    $query->where('is_post', 1);
                })->with('user')->orderBy('updated_at', 'desc');
            }
            
            if (!empty($blockedUserIds)) {
                $query->whereNotIn('user_id', $blockedUserIds);
            }
            
            $getPost = $query->get();
            
            if (!empty($followedata)) {
                $getPost = $followedata->merge($getPost);
                $getPost = $getPost->unique('id');
                if (!$viewTop) {
                    $getPost = $getPost->whereNotIn('user_id', $blockedUserIds)->sortByDesc('updated_at')->values();
                }
                FollowerUnfollwer::where('user_id', $currentUser)->where('status', 0) ->update(['status' => 1]);
            }
            
            
            $getPost = $getPost->filter(function ($post) {
                return $post->user && !$post->user->deleted_at;
            });

            foreach ($getPost as $post) {

                
                
                $post->like_count = Like::where('post_id', $post->id)->count();
                $post->is_like = Like::where(['post_id' => $post->id, 'user_id' => $currentUser])->exists();
                // $post->is_following = FollowerUnfollwer::where(['user_id' => $currentUser, 'following_id' => $post->user_id])->exists();
                $post->is_following = Follow::where(['follower_id' => $currentUser, 'following_id' => $post->user_id,'status'=>"approved"])->exists();
                $created_at_in_timezone = Carbon::createFromFormat('Y-m-d H:i:s', $post->scheduling_post->created_at, 'UTC')->setTimezone($post->scheduling_post->timezone);
                $post->created_date = date('m/d/y', strtotime($created_at_in_timezone));
                $post->posted_date = $post->scheduling_post->schedule_type == "now" ? date('m/d/y', strtotime($created_at_in_timezone)) :  Carbon::parse($post->scheduling_post->schedule_date)->format('m/d/y');
                $post->scheduling_post->schedule_time = $post->scheduling_post->schedule_type == "now" ? date('h:i A', strtotime($created_at_in_timezone)) : date('h:i A', strtotime($post->scheduling_post->schedule_time));
                $post->scheduling_post->schedule_date =  Carbon::parse($post->scheduling_post->schedule_date)->format('m/d/y');
                
                 // Include member_ids for family posts
                if ($post->post_type == "family") {
                    $familyMemberIds = PostMember::where('post_id', $post->id)->pluck('member_id')->toArray();
                    $post->member_ids = $familyMemberIds;
                }
                if ($post->scheduling_post) {
                    $post->scheduling_post->makeHidden(['id', 'post_id']);
                }
                
            }
            if ($getPost->isEmpty()) {
                return $this->successResponse("No posts found", 200);
            }
            if ($request->media) {
                $filteredPosts = $getPost->where('media_type', '=', $request->media);
    
                if ($filteredPosts->isEmpty()) {
                    return $this->successResponse("No posts of type " . $request->media, 200);
                }
    
                // Add index to filtered posts
                $filteredPostsWithIndex = $filteredPosts->values()->map(function ($post, $index) {
                    $post->index = $index;
                    return $post;
                });
                
                $filterPosts = $this->filterPost($filteredPostsWithIndex);

                return $this->successResponse("Posts filtered successfully", 200, $filterPosts);
            }
            
            
            $filterPosts = $this->filterPost($getPost);
            
            
            
            $perPage = $request->input('per_page', 10);
            $currentPage = $request->input('page', 1);
            if (!$filterPosts instanceof \Illuminate\Support\Collection) {
                $filterPosts = collect($filterPosts);
            }
            
            
            $slicedGroups = $filterPosts->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
                $slicedGroups,
                count($filterPosts),
                $perPage,
                $currentPage
            );
    
            return response()->json([
                "message" => 'Posts fetched successfully',
                "status" => "success",
                "error_type" => "",
                "data" => $paginatedGroups->items(),
                'total_records' => $paginatedGroups->total(),
                'total_pages' => $paginatedGroups->lastPage(),
                'current_page' => $paginatedGroups->currentPage(),
                'per_page' => $paginatedGroups->perPage(),
            ]);
    
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    public function getFamoryTagPost(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'famory_tag_id' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            $currentUser = Auth::user();
            $tag = $request->famory_tag_id;
            
            $isValid = FamilyTagId::where('id',$tag)->first();
            if(!$isValid){
                return $this->errorResponse("Famery-ID is not valid please check", 'id_not_valid', 400);
            }
            
            $query = Post::where(['user_id'=> $currentUser->id,'tag_id'=> $isValid->family_tag_id])->whereHas('scheduling_post', function($query) {
                        $query->where('is_post', 1);
                    })->with(['user', 'scheduling_post'])->orderBy('updated_at', 'desc')->paginate(10);
            
           
            foreach ($query as $post) {
                $post->like_count = Like::where('post_id', $post->id)->count();
                $post->is_like = Like::where('post_id', $post->id)->where('user_id', $currentUser->id)->exists();
                $post->is_following = FollowerUnfollwer::where(['user_id' => $currentUser->id, 'following_id' => $post->user_id])->exists();
                $created_at_in_timezone = Carbon::createFromFormat('Y-m-d H:i:s', $post->scheduling_post->created_at, 'UTC')->setTimezone($post->scheduling_post->timezone);
                $post->created_date = date('Y-m-d', strtotime($created_at_in_timezone));
                $post->posted_date = $post->scheduling_post->schedule_type == "now" ? date('Y-m-d', strtotime($created_at_in_timezone)) : $post->scheduling_post->schedule_date;
                $post->scheduling_post->schedule_time = $post->scheduling_post->schedule_type == "now" ? date('h:i A', strtotime($created_at_in_timezone)) : date('h:i A', strtotime($post->scheduling_post->schedule_time));
                
                if ($post->post_type == "family") {
                    $familyMemberIds = PostMember::where('post_id', $post->id)->pluck('member_id')->toArray();
                    $post->member_ids = $familyMemberIds;
                }
                if ($post->scheduling_post) {
                    $post->scheduling_post->makeHidden(['id', 'post_id']);
                }
                
            }
            
            if ($query->isEmpty()) {
                return $this->successResponse("No posts found", 200);
            }
            
           return $this->successResponse("Posts fetched successfully", 200,$query->items(), $query);
            
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    

    public function filterPost($getPost){
        $posts = [];
        foreach ($getPost as $post) {
            $getStopeSeekingdata = StopeSeekingPost::where(['user_id'=>Auth::id(),'post_id'=>$post->id])->first();
            if(!$getStopeSeekingdata){
                $posts[] =  $post;
            }
         }
         return $posts;
    }
    
    
    public function followUnfollow(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'following_id' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            $currentUser = Auth::user();
            $isfollowing = FollowerUnfollwer::where(['user_id'=>$currentUser->id,'following_id'=> $request->following_id])->first();
            if(!$isfollowing){
                $createFollower = new FollowerUnfollwer;
                $createFollower->user_id = $currentUser->id;
                $createFollower->following_id = $request->following_id;
                $createFollower->save();
                $type = "follow";
                $this->notifyMessage($currentUser, $request->following_id, null, $type);
                
                $familyAsUser = new MemberGroup;
                $familyAsUser->user_id = $currentUser->id;
                $familyAsUser->member_id = $request->following_id;
                $familyAsUser->group_id = 2;
                $familyAsUser->save();
                
                return $this->successResponse("Follow successfully",200);
            }else{
                
                $isUser = MemberGroup::where(['user_id'=>$currentUser->id,'member_id'=> $request->following_id,'group_id'=>'2'])->delete();
                $isfollowing = FollowerUnfollwer::where(['user_id'=>$currentUser->id,'following_id'=> $request->following_id])->delete();
                return $this->successResponse("Unfollow successfully",200);
            }
        
            
        }catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function getAllFollowingUser(Request $request){
        try{
            $currentUser = Auth::user()->id;
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            
            $isfollowing = FollowerUnfollwer::where(['user_id'=>$currentUser])->whereNotIn('following_id', $blockedUserIds) ->whereHas('user', function ($query) {
                            $query->whereNull('deleted_at'); // Exclude soft-deleted users
                        })->with(['user'])
                ->when($request->search, function ($query) use ($request) {
                    $searchTerm = '%' . $request->search . '%';
                    $query->whereHas('user', function ($query) use ($searchTerm) {
                        $query->where('first_name', 'like', $searchTerm)
                            ->orWhere('last_name', 'like', $searchTerm);
                    });
                })->orderBy('id','desc')->paginate(20);
            if($isfollowing->isEmpty()){
                return $this->successResponse("not get any user",200);
            }
            return $this->successResponse("List of users who following your",200,$isfollowing->items(),$isfollowing);
            
        }catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function likeUnlike(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
            $currentUser = Auth::user();
            $isLike = Like::where(['user_id' => $currentUser->id, 'post_id' => $request->post_id])->first();
            $post = Post::with('user', 'scheduling_post')->find($request->post_id);
            if ($post && $post->scheduling_post) {
                $post->created_date = date('Y-m-d', strtotime($post->scheduling_post->created_at));
                if ($post->scheduling_post->schedule_type == "now") {
                    $post->posted_date = date('Y-m-d', strtotime($post->scheduling_post->created_at));
                } else {
                    $post->posted_date = $post->scheduling_post->schedule_date;
                }
                // $post->like_count = Like::where('post_id', $post->id)->count();
            }  
             if (!$post) {
                return response()->json(['message' => 'Post not found', 'status' => 'failed'], 404);
            }
            if (!$isLike) {
                $createLike = new Like;
                $createLike->user_id = $currentUser->id;
                $createLike->post_id = $request->post_id;
                $createLike->save();
                
                if($post->user_id != $currentUser->id){
                    $type = "like";
                    $this->notifyMessage($currentUser, $post->user_id, $post, $type);
                }
                $post->refresh();
                $post->like_count = Like::where('post_id', $post->id)->count();
                return response()->json(['message' => 'Like saved successfully', 'status' => 'success'], 200);
            } else {
                $isLike->delete();
                return response()->json(['message' => 'Unlike successfully', 'status' => 'success'], 200);
            }
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }


    public function getallLikeuser(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'post_id' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            $getLike = Like::where(['post_id'=> $request->post_id])->whereNotIn('user_id', $blockedUserIds)->with('user')->orderBy('id','desc')->paginate(20);
            
            if($getLike->isEmpty()){
                
                return $this->successResponse("not get any user",200);
            }
            return $this->successResponse("List of users who liked your posts",200,$getLike->items(),$getLike);
            
        }catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function addReportPost(Request $request){
       try{
            $validator = Validator::make($request->all(), [
                'post_id' => 'required',
                'email' => 'required',
                'phone' => 'nullable | numeric',
                'message' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            
            $savePostReport = new PostReport;
            $savePostReport->user_id = Auth::id();
            $savePostReport->post_id = $request->post_id;
            if($request->email){
                $isExist = User::where('email',$request->email)->first();
                if(!$isExist){
                    return $this->successResponse("Email does not exist",200);
                }
                $savePostReport->email = $request->email;
            }
            $savePostReport->phone = $request->phone;
            $savePostReport->message = $request->message;
            $savePostReport->save();
            
            if(!$savePostReport){
                return $this->successResponse("Something is wrong Post report not saved",200);
            }
            
            return $this->successResponse("Post report saved successfully",200,$savePostReport);
            
            
            
        }catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function addStopSeekingPost(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'post_id' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            
            $isExist = StopeSeekingPost::where(['user_id'=>Auth::id(),'post_id'=>$request->post_id])->first();
            if(!$isExist){
                $data = new StopeSeekingPost;
                $data->user_id = Auth::id();
                $data->post_id = $request->post_id;
                $data->save();
                
                if(!$data){
                    return $this->successResponse("Something is wrong",200);
                }
                return $this->successResponse("Stop Seeking request save successfully",200,$data);
            }else{
                return $this->successResponse("Stop seeking request already exists",200);
            }
            
        }catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
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
    
    
    //This function is used to send posts for scheduled time
    public function updatePostSchedule(){

        $currentTimeUTC = Carbon::now()->format('H:i:s');
        $currentDateUTC = Carbon::now()->format('Y-m-d');
        
        $getAllPost = SchedulingPost::where('schedule_type','date-time')
                                    ->where('is_post','0')
                                    ->where('schedule_date', $currentDateUTC)
                                    ->where('schedule_time', '<=', $currentTimeUTC)
                                    ->get();
                                    
        // \Log::info($getAllPost);
        foreach($getAllPost as $post) {
            // \Log::info("run cron");
            $post->update(['is_post' => 1]);
            $getAlbum = Post::where('id',$post->post_id)->first();
            $type = "post"; 
            $this->notifyMessage(null, $getAlbum->user_id, $getAlbum, $type);
            if($getAlbum->album_id){
                $albumPost = new AlbumPost();
                $albumPost->album_id = $getAlbum->album_id;
                $albumPost->post_id = $getAlbum->id;
                $albumPost->user_id = $getAlbum->user_id;
                $albumPost->save();
                
                $thumbnailPath = null;
                $fileExtension = strtolower(pathinfo(
                    $getAlbum->video_formats['original'] ?? $getAlbum->file, PATHINFO_EXTENSION
                ));
                $fileType = $this->getFileType($fileExtension);

                if ($fileType === 'videos') {
                    $videoFilename = basename($getAlbum->video_formats['original']);
                    $thumbnailFilename = public_path('thumbnails/' . pathinfo($videoFilename, PATHINFO_FILENAME) . '.jpg');

                    if (!file_exists($thumbnailFilename)) {
                        try {
                            $command = "ffmpeg -i " . escapeshellarg($getAlbum->video_formats['original']) . " -ss 00:00:01.000 -vframes 1 " . escapeshellarg($thumbnailFilename);
                            shell_exec($command);
                        } catch (\Exception $e) {
                            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
                        }
                    }
                
                    $thumbnailPath = "https://admin.famoryapp.com/thumbnails/" . pathinfo($videoFilename, PATHINFO_FILENAME) . '.jpg';
                } elseif ($fileType === 'images'){
                    $thumbnailPath = $getAlbum->file;
                } elseif ($fileType === 'audio'){
                     $thumbnailPath = "https://admin.famoryapp.com/assets/img/audio_bg.webp";
                }
                // \Log::info($thumbnailPath);
                if(!empty($thumbnailPath)){
                    $album = Album::find($getAlbum->album_id);
                    $album->album_cover = $thumbnailPath;
                    $album->save();
                    // \Log::info($album);
                }else{
                    $album = Album::find($getAlbum->album_id);
                    $album->album_cover = null;
                    $album->save();
                    // \Log::info($album);
                }
            }
        }
    }
    
    
    // This function is used to Reoccurring post
    public function scheduleReoccurring() {
        // \Log::info("run schedule Reoccurring");
        $now = Carbon::now();
        $posts = SchedulingPost::where('reoccurring_type', 'yes')->get();
        foreach ($posts as $post) {
            $updatedAt = $post->updated_at;
            $nextOccurrence = null;
            switch ($post->reoccurring_time) {
                case 'weekly':
                    $nextOccurrence = $updatedAt->copy()->addWeek();
                    break;
                case 'monthly':
                    $nextOccurrence = $updatedAt->copy()->addMonth();
                    break;
                case 'yearly':
                    $nextOccurrence = $updatedAt->copy()->addYear();
                    break;
                case 'Weekly':
                    $nextOccurrence = $updatedAt->copy()->addWeek();
                    break;    
                default:
                    break;
            }
            if ($nextOccurrence && ($now->greaterThanOrEqualTo($nextOccurrence))) {
                $post->updated_at = $nextOccurrence;
                $post->save();
                $getPost = Post::where('id',$post->post_id)->first();
                $getPost->updated_at = $nextOccurrence;
                $getPost->save();
            }
        }
    }
    
    
    // Cron job function
    public function runCronJobPost() {
        //  \Log::info("run Cron JOB");
        $this->updatePostSchedule();
        $this->scheduleReoccurring();
    }



    public function getBurailpdf(Request $request)
    {
        $current_user = Auth::id();
        $burialInfo = BurialInfo::where('user_id', $current_user)->firstOrFail();
        $user = User::find($current_user);
        $username = trim($user->first_name . ' ' . $user->last_name);
        $latitude = $burialInfo->latitude;
        $longitude = $burialInfo->longitude;
        $mapUrl = "https://static-maps.yandex.ru/1.x/?ll=$longitude,$latitude&size=600,300&z=15&l=map&pt=$longitude,$latitude,pm2rdm";
       
        $mapImage = file_get_contents($mapUrl);
        $mapBase64 = base64_encode($mapImage);
        $mapImgSrc = "data:image/png;base64,$mapBase64";
        $data = [
            'burialInfo' => $burialInfo,
            'username' => $username,
            'userImage' => $user->image,
            'mapImgSrc' => $mapImgSrc,
        ];
        $pdf = new \Dompdf\Dompdf();
        $pdf->setPaper('A4', 'portrait');
        $view = view('admin.burialInfo', $data)->render();
        $pdf->loadHtml($view);
        $pdf->render();
        
        
        
        $canvas = $pdf->getCanvas(); 
         
        // Get height and width of page 
        $w = $canvas->get_width(); 
        $h = $canvas->get_height(); 
         
        // Specify watermark image 
        $imageURL = 'https://admin.famoryapp.com/assets/img/fam-cam-logo.png'; 
        $imgWidth = 300; 
        $imgHeight = 300; 
         
        // Set image opacity 
        $canvas->set_opacity(.3); 
         
        // Specify horizontal and vertical position 
        $x = (($w-$imgWidth)/2); 
        $y = (($h-$imgHeight)/2); 
         
        // Add an image to the pdf 
        $canvas->image($imageURL, $x, $y, $imgWidth, $imgHeight); 
        $pdfContent = $pdf->output();
        // $app_url = config('app.url');
        $app_url = "https://admin.famoryapp.com";
        $pdfDirectory = public_path('report');
        
        $pdfFilename = 'burial_info_' . time() . '.pdf';
    
        if (!is_dir($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }
    
        file_put_contents($pdfDirectory . '/' . $pdfFilename, $pdfContent);
        $pdfUrl = $app_url . '/report/' . $pdfFilename;
        return response()->json(["message" => "PDF generated successfully", "status" => "success", "data" => ['pdf_url' => $pdfUrl, 'mapUrl'=> $mapUrl]], 200);
    }
    
    
    public function test(Request $request){
    try {
            $followedata = new Collection();
            $topPosts = new Collection();
            $viewTop = false;
            $currentUser = Auth::user()->id;
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            
            
            if ($request->type == "my-post") {
                $getPost = Post::where('user_id', $currentUser);
            }
            elseif ($request->filled('user_id')) {
                $userId = $request->user_id;
                $getPost = Post::where('user_id', $userId);
                if($request->type == "open-world"){
                    $getPost = $getPost->where('post_type', 'public');
                }else{
                    
                    $getPost = $getPost->where('post_type', 'private');
                    
                    $Posts = Post::where('post_type','=','family')->get();
                    foreach($Posts as $post){
                        
                        $isPosted =  SchedulingPost::where(['is_post' => 1, 'post_id' => $post->id])->first();
                        
                        if($isPosted){
                            $getData = PostMember::where(['post_id'=> $post->id,'post_by'=> $request->user_id])->get();
                            $user_id = $request->user_id;
                            if($getData->isNotEmpty()){
                                $getPost->orWhere(function ($query) use ($post, $user_id) {
                                    $query->where('id', $post->id);
                                });
                            }
                        }
                    }
                    
                }
                
            } elseif ($request->filled('group_id')) {
                $groupId = $request->group_id;
                $groupMemberIds = MemberGroup::where(['group_id'=> $groupId, 'user_id'=>$currentUser])->pluck('member_id');
                $getPost = Post::whereIn('user_id', $groupMemberIds);
                
            } else {
                if ($request->type == "open-world") {
                    $getPost = Post::where('post_type', 'public');
                }elseif ($request->type == "scheduled") {
                    
                    $getPost = Post::where('user_id', $currentUser)
                        ->whereHas('scheduling_post', function ($query) {
                            $query->where('schedule_type', 'date-time')
                                   ->where('schedule_date', '>=', now()->format('Y-m-d'));
                        });
                        
                }elseif($request->type == "when-pass"){
                    
                    $getPost = Post::where('user_id', $currentUser)
                            ->whereHas('scheduling_post', function ($query) {
                                $query->where('schedule_type', 'when-pass');
                            });
                }else {
                    $familyAsUser = FamilyMember::where('user_id', $currentUser)->pluck('member_id');
                    $familyAsMember = FamilyMember::where('member_id', $currentUser)->pluck('user_id');
                    $familyMemberIds = $familyAsUser->merge($familyAsMember)->unique()->toArray();
                    
                    $query = Post::query();
                    if (!empty($familyMemberIds)) {
                        $query->whereIn('user_id', $familyMemberIds)
                              ->whereIn('post_type', ['private', 'public']);
                        
                        // Fetch 'family' posts
                        $familyPosts = Post::whereIn('user_id', $familyMemberIds)->where('post_type', 'family')->get();
                        
                        foreach ($familyPosts as $post) {
                            $isPosted = SchedulingPost::where(['is_post' => 1, 'post_id' => $post->id])->first();
                        
                            if ($isPosted) {
                                $getData = PostMember::where(['post_id' => $post->id, 'member_id' => $currentUser])->get();
                        
                                if ($getData->isNotEmpty()) {
                                    $query->orWhere(function ($query) use ($post, $currentUser) {
                                        $query->where('id', $post->id)
                                              ->where('user_id', '!=', $currentUser);
                                    });
                                }
                            }
                        }
                    }
                    
                    // Always include the current user's own posts
                    $getPost  = $query->orWhere(function ($query) use ($currentUser) {
                        $query->where('user_id', $currentUser)
                              ->whereIn('post_type', ['private']);
                    });
                    
                    $query->orWhere('post_type', 'public');
                    
                    // Fetch follower users and their posts
                    $getFollowerUsers = FollowerUnfollwer::where('user_id', $currentUser)->get();
                    foreach ($getFollowerUsers as $getFollowerUser) {
                        $followerPostsQuery = Post::where('user_id', $getFollowerUser->following_id)->whereIn('post_type', ['private', 'public'])->with('user')->orderBy('updated_at', 'desc')->take(5)->get();
                        if ($getFollowerUser->status == 0) {
                            $viewTop = true;
                        }
                        $followedata = $followedata->merge($followerPostsQuery);
                    }
                    
                }
            }
                 
      
            if($request->type == "when-pass" || $request->type == "scheduled" || $request->type == "my-post"){
                 
                 $query = $getPost->whereHas('scheduling_post', function ($query) {
                    $query->where('is_post', 0);
                })->with('user')->orderBy('updated_at', 'desc');
                 
            }else{
                $query = $getPost->whereHas('scheduling_post', function ($query) {
                    $query->where('is_post', 1);
                })->with('user')->orderBy('updated_at', 'desc');
            }
            
            if (!empty($blockedUserIds)) {
                $query->whereNotIn('user_id', $blockedUserIds);
            }
            
            $getPost = $query->get();
            
            if (!empty($followedata)) {
                $getPost = $followedata->merge($getPost);
                $getPost = $getPost->unique('id');
                if (!$viewTop) {
                    $getPost = $getPost->whereNotIn('user_id', $blockedUserIds)->sortByDesc('updated_at')->values();
                }
                FollowerUnfollwer::where('user_id', $currentUser)->where('status', 0) ->update(['status' => 1]);
            }

            foreach ($getPost as $post) {
                $post->like_count = Like::where('post_id', $post->id)->count();
                $post->is_like = Like::where(['post_id' => $post->id, 'user_id' => $currentUser])->exists();
                $post->is_following = FollowerUnfollwer::where(['user_id' => $currentUser, 'following_id' => $post->user_id])->exists();
                $created_at_in_timezone = Carbon::createFromFormat('Y-m-d H:i:s', $post->scheduling_post->created_at, 'UTC')->setTimezone($post->scheduling_post->timezone);
                $post->created_date = date('Y-m-d', strtotime($created_at_in_timezone));
                $post->posted_date = $post->scheduling_post->schedule_type == "now" ? date('Y-m-d', strtotime($created_at_in_timezone)) : $post->scheduling_post->schedule_date;
                $post->scheduling_post->schedule_time = $post->scheduling_post->schedule_type == "now" ? date('h:i A', strtotime($created_at_in_timezone)) : date('h:i A', strtotime($post->scheduling_post->schedule_time));
                
                
                 // Include member_ids for family posts
                if ($post->post_type == "family") {
                    $familyMemberIds = PostMember::where('post_id', $post->id)->pluck('member_id')->toArray();
                    $post->member_ids = $familyMemberIds;
                }
                if ($post->scheduling_post) {
                    $post->scheduling_post->makeHidden(['id', 'post_id']);
                }
                
            }
            
            if ($getPost->isEmpty()) {
                return $this->successResponse("No posts found", 200);
            }
            
            
            if ($request->media) {
                $filteredPosts = $getPost->where('media_type', '=', $request->media);
    
                if ($filteredPosts->isEmpty()) {
                    return $this->successResponse("No posts of type " . $request->media, 200);
                }
    
                // Add index to filtered posts
                $filteredPostsWithIndex = $filteredPosts->values()->map(function ($post, $index) {
                    $post->index = $index;
                    return $post;
                });
                
                $filterPosts = $this->filterPost($filteredPostsWithIndex);
                
                return $this->successResponse("Posts filtered successfully", 200, $filterPosts);
            }
            
            
            
            $filterPosts = $this->filterPost($getPost);
            $perPage = $request->input('per_page', 10);
            $currentPage = $request->input('page', 1);
            if (!$filterPosts instanceof \Illuminate\Support\Collection) {
                $filterPosts = collect($filterPosts);
            }
            
            $sortedPosts = $filterPosts->sortByDesc('updated_at');
            $slicedGroups = $sortedPosts->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
                $slicedGroups,
                count($filterPosts),
                $perPage,
                $currentPage
            );
    
            return response()->json([
                "message" => 'Posts fetched successfully',
                "status" => "success",
                "error_type" => "",
                "data" => $paginatedGroups->items(),
                'total_records' => $paginatedGroups->total(),
                'total_pages' => $paginatedGroups->lastPage(),
                'current_page' => $paginatedGroups->currentPage(),
                'per_page' => $paginatedGroups->perPage(),
            ]);
    
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }

    public function commentPost(Request $request ,$post_id)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'exists:posts,id',
            'comment' => 'required|string'
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return response()->json(['message' => $value, 'status' => 'failed'], 400);
            }
        }
        $post = Post::find($post_id);

        if (!$post) {
            return $this->errorResponse('The specified post does not exist.','db_error', 400);
        }
        $existingComment = $post->comments()->where('user_id', auth()->user()->id)
                                        ->where('comment', $request->comment)
                                        ->first();

        // If the comment exists, return an error message
        if ($existingComment) {
            return $this->errorResponse('You have already posted this comment.','db_error', 400);
        }
        $post_comment=   $post->comments()->create([
            'post_id' => $post_id,
            'user_id' => auth()->user()->id,
            'comment' => $request->comment,
        ]);

         // Send a notification to the post owner (author of the post)
        $postOwner = $post->user; // Assuming `user` is the relationship to the post owner
        $postOwner->notify(new CommentAddedNotification($post_comment, $post));
        return $this->successResponse("Comment added successfully", 200);
    }

    public function getCommentPost(Request $request ,$post_id)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'exists:posts,id',
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return response()->json(['message' => $value, 'status' => 'failed'], 400);
            }
        }
        $post = Post::find($post_id);

        if (!$post) {
            return $this->errorResponse('The specified post does not exist.','db_error', 400);
        }
        $existingComment = $post->comments()->where('user_id', auth()->user()->id)->get();

        return $this->successResponse("Comment fetch successfully", 200,$existingComment);
    }

    public function deleteCommentPost($post_id, $comment_id)
    {
        $post = Post::find($post_id);

        if (!$post) {
            return $this->errorResponse('The specified post does not exist.','db_error', 400);
        }
        $comment = Comment::where('post_id', $post->id)
                          ->where('id', $comment_id)
                          ->first();

        // Check if the comment exists and if the user is authorized to delete it (optional)
        if (!$comment) {
            return $this->errorResponse('Comment not found or does not belong to this post.','db_error', 400);
        }
        if ($comment->user_id !== auth()->user()->id) {
            return $this->errorResponse('You are not authorized to delete this comment.','db_error', 400);
        }

        // Delete the comment
        $comment->delete();

        return $this->successResponse("Comment deleted successfully", 200);
    }

    public function addCommentReply(Request $request, $post_id, $comment_id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:500',
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return response()->json(['message' => $value, 'status' => 'failed'], 400);
            }
        }

        // Find the post by ID
        $post = Post::find($post_id);
        if (!$post) {
            return $this->errorResponse('The specified post does not exist.','db_error', 400);
        }

         // Find the parent comment
         $parentComment = Comment::find($comment_id);
         if (!$parentComment) {
            return $this->errorResponse('Comment not found.','db_error', 400);
        }


         // Check if the comment is already a reply (i.e., it has a parent_id)
         if ($parentComment->parent_id !== null) {
            return $this->errorResponse('You cannot reply to a reply.','db_error', 400);
        }

        // Check if the comment already has replies
        if ($parentComment->replies()->exists()) {
            return $this->errorResponse('This comment already has replies.','db_error', 400);
        }

        // Create a new comment (reply)
        $reply = new Comment();
        $reply->post_id = $post->id;
        $reply->user_id = auth()->user()->id;
        $reply->parent_id = $parentComment->id; // Link the reply to the parent comment
        $reply->comment = $request->comment;
        $reply->save();

         // Notify the user who made the parent comment
        $parentCommentUser = $parentComment->user; // Assuming `user` is the relationship to the parent comment's author
        $parentCommentUser->notify(new CommentReplyNotification($reply, $parentComment));

        return $this->successResponse("Your reply has been posted!", 200);
    }

    public function getCommentReply($post_id)
    {
        // Find the post by ID
        $post_replies = Post::with('comments.replies.user')->findOrFail($post_id);
        return $this->successResponse("Success!", 200,$post_replies);
    }


    public function reportComment(Request $request, $comment_id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return response()->json(['message' => $value, 'status' => 'failed'], 400);
            }
        }

        // Find the comment by ID
        $comment = Comment::findOrFail($comment_id);

        // Create a new report for the comment
        Report::create([
            'comment_id' => $comment->id,
            'user_id' => auth()->user()->id,
            'reason' => $request->reason,
        ]);

        // Optionally, mark the comment as reported in the comment table (if you need to track it)
        $comment->reported = true;
        $comment->save();

         // Send a notification to the admin (replace with actual admin user)
        // $admin = User::where('is_admin', true)->first(); // Example query to get the admin
        // Notification::send($admin, new CommentReported($comment));
        return $this->successResponse("Comment reported successfully.", 200);
    }

    //==========================ADMIN END===========================================
    //at admin side
    //Route::get('admin/reported-comments', [AdminController::class, 'reportedComments'])->name('admin.reportedComments');
    public function reportedComments()
    {
        // Fetch all reported comments with reasons
        $reportedComments = Report::with('comment.user')->get();
        
        return view('admin.reported_comments', compact('reportedComments'));
    }

   

    //admin remove comment after report

    //Route::delete('admin/comment/{comment_id}/remove', [AdminController::class, 'removeComment'])->name('comment.remove');
        public function removeComment($comment_id)
    {
        $comment = Comment::findOrFail($comment_id);
        $comment->delete();
        
        return redirect()->back()->with('success', 'Comment removed successfully.');
    }

    public function getReportedComments()
    {
        // Fetch all reports
        $reportedComments = Report::with(['comment', 'user'])->get();

      

        return $this->successResponse("Get Comment reported successfully.", 200,$reportedComments);
    }
    //==================END ADMIN==========================

    public function likeComment($comment_id)
    {
        // Ensure the comment exists
        $comment = Comment::findOrFail($comment_id);

        // Check if the user has already liked this comment
        $existingLike = CommentLike::where('user_id', auth()->user()->id)
                            ->where('comment_id', $comment_id)
                            ->first();

        if ($existingLike) {
            // If the user already liked the comment, they are unliking it
            $existingLike->delete();
            return response()->json(['message' => 'Like removed successfully.'], 200);
        } else {
            // Otherwise, create a new like
            Like::create([
                'user_id' => auth()->id(),
                'comment_id' => $comment_id
            ]);
            return response()->json(['message' => 'Comment liked successfully.'], 200);
        }
    }

    public function getLikes($comment_id)
    {
        // Fetch the comment and its likes
        $comment = Comment::with('usersWhoLiked')->findOrFail($comment_id);

        // Return the users who liked the comment
        return response()->json([
            'comment' => $comment,
            'likes_count' => $comment->likeComment->count(),
            'users_who_liked' => $comment->usersWhoLiked
        ], 200);
    }

    public function savePost(Request $request, $post_id, $comment_id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:500',
        ]);
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return response()->json(['message' => $value, 'status' => 'failed'], 400);
            }
        }

        // Find the post by ID
        $post = Post::find($post_id);
        if (!$post) {
            return $this->errorResponse('The specified post does not exist.','db_error', 400);
        }

         // Find the parent comment
         $parentComment = Comment::find($comment_id);
         if (!$parentComment) {
            return $this->errorResponse('Comment not found.','db_error', 400);
        }


         // Check if the comment is already a reply (i.e., it has a parent_id)
         if ($parentComment->parent_id !== null) {
            return $this->errorResponse('You cannot reply to a reply.','db_error', 400);
        }

        // Check if the comment already has replies
        if ($parentComment->replies()->exists()) {
            return $this->errorResponse('This comment already has replies.','db_error', 400);
        }

        // Create a new comment (reply)
        $reply = new Comment();
        $reply->post_id = $post->id;
        $reply->user_id = auth()->user()->id;
        $reply->parent_id = $parentComment->id; // Link the reply to the parent comment
        $reply->comment = $request->comment;
        $reply->save();

         // Notify the user who made the parent comment
        $parentCommentUser = $parentComment->user; // Assuming `user` is the relationship to the parent comment's author
        $parentCommentUser->notify(new CommentReplyNotification($reply, $parentComment));

        return $this->successResponse("Your reply has been posted!", 200);
    }
    


}
