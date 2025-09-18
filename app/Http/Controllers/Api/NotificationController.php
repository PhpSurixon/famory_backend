<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use App\Traits\FormatResponseTrait;
class NotificationController extends Controller
{
    use OneSignalTrait;
    use FormatResponseTrait;
    // public function notificationList(Request $request)
    // {
    //     try {
    //         $limit = (int) $request->get('limit', 30);
    //         $page = (int) $request->get('page', 1);
    //         $offset = ($page - 1) * $limit;
    //         $authUser = Auth::user();

    //         // Base query: notifications where I'm the receiver
    //         $query = Notification::where('receiver_id', $authUser->id)
    //             ->with([
    //                 'sender:id,first_name,last_name,username,image',
    //             ])
    //             ->orderBy('created_at', 'desc');

    //         // Optional: search filter by title/message/type
    //         if ($request->filled('search')) {
    //             $search = $request->search;
    //             $query->where(function ($q) use ($search) {
    //                 $q->where('title', 'like', "%$search%")
    //                 ->orWhere('message', 'like', "%$search%")
    //                 ->orWhere('type', 'like', "%$search%");
    //             });
    //         }

    //         $total = $query->count();

    //         $notifications = $query->skip($offset)->take($limit)->get();

    //         $data = $notifications->map(function ($n) {
    //             return [
    //                 'id'            => $n->id,
    //                 'title'         => $n->title,
    //                 'message'       => $n->message,
    //                 'type'          => $n->type,
    //                 'item_id'       => $n->item_id,
    //                 'group_id'      => $n->group_id,
    //                 'marked_user_id'=> $n->marked_user_id,
    //                 'has_actioned'  => $n->has_actioned,
    //                 'is_seen'       => $n->isSeen ? true : false,
    //                 'created_at'    => $n->created_at->diffForHumans(),
    //                 'sender'        => $n->sender ? [
    //                     'id'        => $n->sender->id,
    //                     'first_name'=> $n->sender->first_name,
    //                     'last_name' => $n->sender->last_name,
    //                     'username'  => $n->sender->username,
    //                     'image'     => $n->sender->image,
    //                 ] : null
    //             ];
    //         });

    //         return response()->json([
    //             'message' => 'Notifications fetched successfully',
    //             'status'  => 'success',
    //             'data'    => [
    //                 'count'       => $total,
    //                 'page'        => $page,
    //                 'limit'       => $limit,
    //                 'total_pages' => ceil($total / $limit),
    //                 'notifications' => $data
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => "Something Went Wrong! " . $e->getMessage(),
    //             'status' => 'failed'
    //         ], 400);
    //     }
    // }

    public function notificationListOLD(Request $request)
    {
        try {

            $limit = (int) $request->get('limit', 30);
            if ($limit <= 0) $limit = 30;              // avoid division by zero
            $limit = min($limit, 100);                 // optional cap

            $page  = (int) $request->get('page', 1);
            if ($page <= 0) $page = 1;

            $offset   = ($page - 1) * $limit;
            $authUser = Auth::user();

            // Base query
            $query = Notification::where('receiver_id', $authUser->id)
                ->with([
                    'sender:id,first_name,last_name,username,image',
                ])
                ->orderBy('created_at', 'desc');

            // ✅ Optional search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                      ->orWhere('message', 'like', "%$search%")
                      ->orWhere('type', 'like', "%$search%");
                });
            }

            $total = $query->count();
            $notifications = $query->skip($offset)->take($limit)->get();

            $data = $notifications->map(function ($n) {
                // ✅ Build structured redirect JSON
                $redirectTo = null;
                switch ($n->type) {
                    case 'follow':
                    case 'follow_request':
                    case 'follow_accept':
                    case 'follow_reject':
                    case 'trust_request':
                        $redirectTo = [
                            "screen" => "UserProfile",
                            "params" => ["user_id" => $n->item_id]
                        ];
                        break;

                    case 'like':
                    case 'post':
                        $redirectTo = [
                            "screen" => "PostDetail",
                            "params" => ["post_id" => $n->item_id]
                        ];
                        break;

                    case 'invite':
                    case 'invite_user':
                        $redirectTo = [
                            "screen" => "GroupDetail",
                            "params" => ["group_id" => $n->group_id]
                        ];
                        break;

                   
                    case 'self':
                        $redirectTo = [
                            "screen" => "UserProfile",
                            "params" => ["user_id" => $n->marked_user_id]
                        ];
                        break;
                    case 'deceased':
                        $redirectTo = [
                            "screen" => "UserProfile",
                            "params" => ["user_id" => $n->item_id]
                        ];
                        break;

                    default:
                        $redirectTo = null;
                }

                return [
                    'id'             => $n->id,
                    'title'          => $n->title,
                    'message'        => $n->message,
                    'type'           => $n->type,
                    'item_id'        => $n->item_id ?? null,
                    'group_id'       => $n->group_id ?? null,
                    'marked_user_id' => $n->marked_user_id ?? null,
                    'has_actioned'   => (bool) $n->has_actioned,
                    'is_seen'        => (bool) $n->is_seen,
                    'created_at'     => $n->created_at->toDateTimeString(),
                    'time_ago'       => $n->created_at->diffForHumans(),
                    'redirect_to'    => $redirectTo, // ✅ structured redirect
                    'sender'         => $n->sender ? [
                        'id'         => $n->sender->id,
                        'first_name' => $n->sender->first_name,
                        'last_name'  => $n->sender->last_name,
                        'username'   => $n->sender->username,
                        'image'      => $n->sender->image,
                    ] : null
                ];
            });

            return response()->json([
                'message' => 'Notifications fetched successfully',
                'status'  => 'success',
                'data'    => [
                    'count'         => $total,
                    'page'          => $page,
                    'limit'         => $limit,
                    'total_pages'   => ceil($total / $limit),
                    'notifications' => $data
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

    public function notificationList(Request $request)
    {
        try {
            $user = Auth::user();
            // $user = User::find(787);

            // ✅ Get timezone from headers
            $getHeaders = apache_request_headers();
            $timeZone = isset($getHeaders['Timezone']) ? $getHeaders['Timezone'] : 'UTC';

            // ✅ Pagination setup (default 10 per page, max 100)
            $limit = (int) $request->get('limit', 10);
            if ($limit <= 0) $limit = 10;
            $limit = min($limit, 100);

            $notis = Notification::where('receiver_id', $user->id)
                                    // ->where('isSeen', 0)
                                    ->orderBy('id', 'DESC')
                                    ->with('group')
                                    ->paginate($limit);

            foreach ($notis as $noti) {

                // ✅ For post/like notifications, attach post info
                if ($noti->type == "like" || $noti->type == "post") {
                    $getPost = Post::where('id', $noti->post_id)
                        ->with(['scheduling_post', 'user'])
                        ->first();

                    if ($getPost) {
                        $noti->post = $getPost;
                        $getPost->like_count = Like::where('post_id', $noti->post_id)->count();
                        $getPost->is_like = Like::where([
                            'post_id' => $noti->post_id,
                            'user_id' => $user->id
                        ])->exists();
                        $getPost->is_following = Follow::where([
                            'follower_id' => $user->id,
                            'following_id' => $getPost->user->id,
                            'status'       =>'approved'
                        ])->exists();
                        $getPost->created_date = date('Y-m-d', strtotime($getPost->scheduling_post->created_at));
                        $getPost->posted_date = $getPost->scheduling_post->schedule_type == "now"
                            ? date('Y-m-d', strtotime($getPost->scheduling_post->created_at))
                            : $getPost->scheduling_post->schedule_date;
                    }
                }

                // ✅ For deceased notifications, attach deceased user & burial info
                elseif ($noti->type == "deceased") {
                    $markedUser = User::find($noti->marked_user_id);
                    $burialInfo = BurialInfo::where('user_id', $noti->marked_user_id)->first();
                    $data['user_name'] = null;

                    if ($markedUser) {
                        $first_name = isset($markedUser->first_name) ? trim($markedUser->first_name) : '';
                        $last_name = isset($markedUser->last_name) ? trim($markedUser->last_name) : '';
                        if (!empty($first_name) || !empty($last_name)) {
                            $data['user_name'] = trim($first_name . ' ' . $last_name);
                        }
                    }

                    $data['user_image'] = $markedUser->image ?? null;
                    $data['user_id'] = $markedUser->id ?? null;
                    $data['burialinfo'] = $burialInfo ? $burialInfo->toArray() : null;
                    $noti->deceased_user = $data ?? null;
                }

                // ✅ Sender details
                $getSender = User::find($noti->sender_id);
                if ($getSender) {
                    $noti->sender = $getSender;
                }

                // ✅ Timezone-based created_at formatting
                $createdAt = Carbon::parse($noti->created_at)->timezone($timeZone);
                $noti->created_at = $createdAt->format('Y-m-d H:i:s');

                // ✅ Extra check for invite type
                if ($noti->type == "invite") {
                    $getData = ConnectionRequest::where([
                        'user_id'   => $noti->receiver_id,
                        'sender_id' => $noti->sender_id
                    ])->first();
                    $noti->is_connection_request = $getData ? true : false;
                }

                // ✅ Add redirect info (from your first function)
                $redirectTo = null;
                switch ($noti->type) {
                    case 'follow':
                    case 'follow_request':
                    case 'follow_accept':
                    case 'follow_reject':
                    case 'trust_request':
                        $redirectTo = [
                            "screen" => "UserProfile",
                            "params" => ["user_id" => $noti->item_id]
                        ];
                        break;
                    case 'like':
                    case 'post':
                        $redirectTo = [
                            "screen" => "PostDetail",
                            "params" => ["post_id" => $noti->item_id]
                        ];
                        break;
                    case 'invite':
                    case 'invite_user':
                        $redirectTo = [
                            "screen" => "GroupDetail",
                            "params" => ["group_id" => $noti->group_id]
                        ];
                        break;
                    case 'self':
                        $redirectTo = [
                            "screen" => "UserProfile",
                            "params" => ["user_id" => $noti->marked_user_id]
                        ];
                        break;
                    case 'deceased':
                        $redirectTo = [
                            "screen" => "UserProfile",
                            "params" => ["user_id" => $noti->item_id]
                        ];
                        break;
                }
                $noti->redirect_to = $redirectTo;
            }

            return $this->successResponse(
                "Notifications fetched successfully.",
                200,
                $notis->items(),
                $notis
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'internal_server_error', 500);
        }
    }



    public function markAllSeen()
    {
        try 
        {
            $authId = Auth::id();

            // Count unseen notifications
            $unseenCount = Notification::where('receiver_id', $authId)
                ->where('isSeen', 0)
                ->count();

            if ($unseenCount === 0) {
                return response()->json([
                    'message' => 'No unseen notifications found',
                    'status' => 'failed'
                ], 404);
            }

            // Update unseen → seen
            Notification::where('receiver_id', $authId)
                ->where('isSeen', 0)
                ->update(['isSeen' => 1]);

            return response()->json([
                'message' => "All $unseenCount notifications marked as seen",
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status' => 'failed'
            ], 400);
        }
    }

    public function markSingleSeen(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
            $authId = Auth::id();
            $notificationId = $request->input('id'); // get id from POST body

            // Find the notification belonging to logged-in user
            $notification = Notification::where('id', $notificationId)
                                        ->where('receiver_id', $authId)
                                        ->first();

            if (!$notification) {
                return response()->json([
                    'message' => 'Notification not found',
                    'status'  => 'failed'
                ], 404);
            }

            if ($notification->isSeen == 1) {
                return response()->json([
                    'message' => 'Notification already marked as seen',
                    'status'  => 'failed'
                ], 400);
            }

            // Update to seen
            $notification->isSeen = 1;
            $notification->save();

            return response()->json([
                'message' => 'Notification marked as seen successfully',
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

    public function notificationSingleDelete(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
            $authId = Auth::id();
            $notificationId = $request->input('id'); // get id from POST body

            // Find the notification belonging to logged-in user
            $notification = Notification::where('id', $notificationId)
                                        ->where('receiver_id', $authId)
                                        ->first();

            if (!$notification) {
                return response()->json([
                    'message' => 'Notification not found',
                    'status'  => 'failed'
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'message' => 'Notification Deleted successfully',
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

    public function sendToUser(Request $request)
    {
        
        

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'message' => 'required|string',
            'player_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

        $response = $this->sendNotificationNew(
            $request->title,
            $request->message,
            playerIds: [$request->player_id]
        );

        return response()->json($response);
    }

}
