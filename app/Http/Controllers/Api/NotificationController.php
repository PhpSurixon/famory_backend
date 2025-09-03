<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;
use App\Models\Notification;
class NotificationController extends Controller
{
    public function notificationList(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 30);
            $page = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $authUser = Auth::user();

            // Base query: notifications where I'm the receiver
            $query = Notification::where('receiver_id', $authUser->id)
                ->with([
                    'sender:id,first_name,last_name,username,image',
                ])
                ->orderBy('created_at', 'desc');

            // Optional: search filter by title/message/type
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
                return [
                    'id'            => $n->id,
                    'title'         => $n->title,
                    'message'       => $n->message,
                    'type'          => $n->type,
                    'item_id'       => $n->item_id,
                    'group_id'      => $n->group_id,
                    'marked_user_id'=> $n->marked_user_id,
                    'has_actioned'  => $n->has_actioned,
                    'is_seen'       => $n->isSeen ? true : false,
                    'created_at'    => $n->created_at->diffForHumans(),
                    'sender'        => $n->sender ? [
                        'id'        => $n->sender->id,
                        'first_name'=> $n->sender->first_name,
                        'last_name' => $n->sender->last_name,
                        'username'  => $n->sender->username,
                        'image'     => $n->sender->image,
                    ] : null
                ];
            });

            return response()->json([
                'message' => 'Notifications fetched successfully',
                'status'  => 'success',
                'data'    => [
                    'count'       => $total,
                    'page'        => $page,
                    'limit'       => $limit,
                    'total_pages' => ceil($total / $limit),
                    'notifications' => $data
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status' => 'failed'
            ], 400);
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



}
