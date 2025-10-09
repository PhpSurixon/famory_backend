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
use App\Models\Comment;
use App\Models\Like;
use App\Models\CommentLike;

class PostLikeController extends Controller
{
    use OneSignalTrait;
    protected $post;
    protected $postComment;
    protected $postLike;
    protected $commentLike;
    public function __construct(Post $post,Comment $comment,Like $like,CommentLike $commentLike)
    {
        $this->post        = $post;
        $this->postComment = $comment;
        $this->postLike    = $like;
        $this->commentLike    = $commentLike;
    }

    public function toggle(Request $request)
    {  
        try 
        {
            $validator = Validator::make($request->all(), [
                'post_id'   => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }
            DB::beginTransaction();

            $authUser  = Auth::user();
            $checkPost = $this->post::where('id',$request->post_id)->first();
            if(empty($checkPost))
            {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Post not found Please Pass correct Post ID",
                ], 400);
            }

            $existing = $this->postLike::where('post_id', $checkPost->id)
                                ->where('user_id', $authUser->id)
                                ->first();
            if($existing)
            {
                $existing->delete();
                $liked = false;

            }else{
                $this->postLike::create([
                    'post_id' => $checkPost->id,
                    'user_id' => $authUser->id
                ]);
                $liked = true;

                $this->notifyMessage($authUser, $checkPost->user_id, $checkPost, "like");
            }

            $likesCount = $checkPost->likes()->count();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'liked' => $liked,
                    'likes_count' => $likesCount
                ]
            ], 200);

            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function commentLikeUnlike(Request $request)
    {  
        try 
        {
            $validator = Validator::make($request->all(), [
                'comment_id'   => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }
            DB::beginTransaction();

            $authUser  = Auth::user();
            $checkComment = $this->postComment::where('id',$request->comment_id)->first();
            if(empty($checkComment))
            {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Comment not found Please Pass correct Comment ID",
                ], 400);
            }

            $existing = $this->commentLike::where('comment_id', $checkComment->id)
                                ->where('user_id', $authUser->id)
                                ->first();
            if($existing)
            {
                $existing->delete();
                $liked = false;

            }else{
                $this->commentLike::create([
                    'comment_id' => $checkComment->id,
                    'user_id' => $authUser->id
                ]);
                $liked = true;
                 // dd($checkComment->post_id);
                $this->notifyMessage($authUser, $checkComment->user_id, $checkComment->post_id, "comment_like");
            }

            $likesCount = $checkComment->likeComment()->count();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'liked' => $liked,
                    'likes_count' => $likesCount
                ]
            ], 200);

            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }
}
