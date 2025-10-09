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
class PostCommentController extends Controller
{
    use OneSignalTrait;
    protected $post;
    protected $postComment;
    public function __construct(Post $post,Comment $comment)
    {
        $this->post = $post;
        $this->postComment = $comment;
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status'  => 'failed'
            ], 400);
        }

        try {
            $get_post = $this->post::with([
                'scheduling_post',
                'post_member.user_new:id,first_name,last_name,image', // post members with user info
                'comments' => function ($query) {
                    $query->whereNull('parent_id') // only top-level comments
                        ->select('id', 'post_id', 'user_id', 'comment', 'parent_id') // select required fields
                        ->with([
                            'user:id,first_name,last_name,image', // comment user info
                            'comment_replies' => function ($subQuery) {
                                $subQuery->select('id', 'post_id', 'user_id', 'parent_id', 'comment') // reply fields
                                        ->with('user:id,first_name,last_name,image')
                                        ->withCount('likeComment'); // reply user info
                            }
                        ])
                        ->withCount('likeComment')
                        ->orderBy('created_at', 'desc');
                }
            ])
            ->withCount('likes')
            ->withCount('comments')
            ->where('id', $request->post_id)
            ->first();

            if (empty($get_post)) {
                return response()->json([
                    'message' => 'Post Details not found',
                    'status' => 'failed'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Post Details with Comments',
                'data' => $get_post
            ], 200);

        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id'   => 'required|integer',
            'comment'   => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
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
            $userId = Auth::id();
            $authUser = Auth::user();
            $checkPost = $this->post::where('id',$request->post_id)->first();
            if(empty($checkPost))
            {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Post not found Please Pass correct Post ID",
                ], 400);
            }

            if ($request->parent_id) {
                $parent = $this->postComment::where('id',$request->parent_id)->first();
                // dd($parent,$request->all());
                if (!$parent || $parent->post_id != $request->post_id) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Invalid parent comment'
                    ], 400);
                }
            }

            $comment = $this->postComment::create([
                'post_id'   => $checkPost->id,
                'user_id'   => $userId,
                'parent_id' => $request->parent_id??null,
                'comment'   => $request->comment,
            ]);

          // eager load user for response
          $comment->load('user:id,first_name,last_name,image');
          
          $this->notifyMessage($authUser, $checkPost->user_id, $checkPost, "comment");
          DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment posted',
            'data' => $comment
        ], 201);
            
        } catch (\Exception $exception) {
            // dd($exception);
            DB::rollBack();
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }

    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id'   => 'required|integer',
            'comment'      => 'required|string|max:1000',
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
            $userId = Auth::id();
            $checkComment = $this->postComment::where('id',$request->comment_id)->first();
            if(empty($checkComment))
            {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Post not found Please Pass correct Post ID",
                ], 400);
            }

            if ($checkComment->user_id != $userId) 
            {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You can only update your own comment'
                ], 403);
            }

            $checkComment->comment = $request->comment;
            $checkComment->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Comment updated successfully',
                'data' => $checkComment
            ], 200);
            
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function destroy(Request $request)
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
        try 
        {
            $userId = Auth::id();
            $checkComment = $this->postComment::where('id',$request->comment_id)->first();
            if(empty($checkComment))
            {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Post not found Please Pass correct Post ID",
                ], 400);
            }

            if ($checkComment->user_id != $userId) 
            {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You can only Delete your own comment'
                ], 403);
            }

            $checkComment->comment_replies()->delete();
            $checkComment->delete();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Comment deleted successfully'
            ], 200);
            
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

}
