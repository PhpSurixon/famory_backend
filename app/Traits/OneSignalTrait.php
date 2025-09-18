<?php
namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Notification;
use App\Models\User;
use App\Models\DeviceDetail;

trait OneSignalTrait
{
    public function sendNotificationNew($heading, $message, $playerIds = [],$extraData = [])
    {
        $data = [
            'app_id' => env('ONESIGNAL_APP_ID'),
            'headings' => ["en" => $heading],
            'contents' => ["en" => $message],
        ];

        if (!empty($playerIds)) {
            $data['include_player_ids'] = $playerIds;
        } else {
            $data['included_segments'] = ['All']; // send to all users
        }

        if (!empty($extraData)) {
            $data['data'] = $extraData;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://onesignal.com/api/v1/notifications', $data);

        return $response->json();
    }

    function sendNotification($title, $message, $data, $external_id)
    {
        $heading = ["en" => $title];
        $content = ["en" => $message];
        $fields = [
            "app_id" => 'a0aa6cc6-86ce-4e06-9ae0-c03da994d352',
            'android_channel_id' => 'a5cbd6ee-988f-429e-a541-f1d6de1b6fb6',
            "include_player_ids" => $external_id,
            // "include_external_user_ids" => $external_id,
            "channel_for_external_user_ids" => "push",
            "data" => $data,
            'contents' => $content,
            'headings' => $heading,
        ];

        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic MThmNTFjNmUtNTFkNy00OTMwLWFhZjUtMTFjYmQyNzgzN2Uw',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        \Log::info("res:- ");
        \Log::info($result);

    }


    //send notification 

    public function notifyMessage($sender, $receiverId, $item, $type, $deceasedUser = null, $deceasedById = null, $customTitle = null, $customMessage = null)
    {
        // dd($receiverId);
        $message = null;
        $title = null;
        $jobId = null;
        $userJobId = null;
        $matchId = null;
        $data = [];

        //get token from DB
        // $getToken = DeviceDetail::where(['user_id' => $receiverId, 'is_user_loggedin' => 1])->pluck('device_token')->toArray();
        $getToken = DeviceDetail::where(['user_id' => $receiverId, 'is_user_loggedin' => 1])->first();
        $token = $getToken->device_token??null;
        $device_type = $getToken->platform??null;
        $senderDetails = [
            "user_name" => ($sender->first_name ?? '') . ' ' . ($sender->last_name ?? '') ?? null,
            "user_image" => $sender->image ?? null,
            // "user_id" => "$sender->id" ? "$sender->id" : null,
            "user_id" => isset($sender) ? $sender->id : null,
        ];

        $senderId = isset($sender) ? $sender->id : null;
        $senderName = $sender->first_name ?? null;
        $deceasedName = $deceasedUser ? ($deceasedUser->first_name . ' ' . $deceasedUser->last_name) : 'Deceased Member';

        switch ($type) {

            case "like":
                $title = "New Like";
                $message = "$senderName liked your post";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "post" => $item
                ];

                break;

            case "follow":
                $title = "New Follow";
                $message = "$senderName started following you.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "post" => $item
                ];

                break;

            case "invite":
                \Log::info($item);
                $title = "New Invited User";
                $message = "$senderName  invite to join family";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "group" => $item,

                ];
                break;
            case "post":
                $title = "New Posted Post";
                $message = "Posted this post.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "post" => $item
                ];
                break;
            case "deceased":
                $title = "$deceasedName deceased";
                $message = "$senderName has marked $deceasedName as deceased.Please confirm if this is true or false.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "deceased_user" => $item
                ];
                break;
            case "self":
                $title = "$deceasedName deceased";
                // $message = "$senderName has marked $deceasedName as deceased.";
                $message = "Please confirm if you wish to set your status as deceased";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "deceased_user" => $item
                ];
                break;

            case "accept":
                $title = "Accepted your request";
                $message = "Your request has been accepted by $senderName.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                ];
                break;

            case "invite_user":
                $title = "Accepted your invitation";
                $message = "Your invitation has been accepted by $senderName.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                ];
                break;

            case "custom_notification":
                $title = $customTitle;
                $message = $customMessage;
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                ];
                break;

            case "when-pass":
                $title = "$deceasedName post";
                $message = "This post was created by $deceasedName.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "deceased_user" => $item
                ];
                break;
            //New Add
            case "follow_request":
                $title = "New Follow Request";
                $message = "$senderName has requested to follow you.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "post" => $item
                ];
                break;
            case "follow_reject":
                $title = "Follow Request Rejected";
                $message = "Your follow request has been rejected by $senderName.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                ];
                break;
            case "follow_accept":
                $title = "Follow Request Accepted";
                $message = "$senderName accepted your follow request.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "post" => $item
                ];
                break;
            case "trust_request":
                $title = "New Trust Request";
                $message = "$senderName has sent you a trust request.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "item" => $item,
                ];
                break;

            case "trust_accept":
                $title = "Trust Request Accepted";
                $message = "$senderName has accepted your trust request.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "item" => $item,
                ];
                break;

            case "trust_reject":
                $title = "Trust Request Rejected";
                $message = "$senderName has rejected your trust request.";
                $data = [
                    "type" => $type,
                    "sender" => $senderDetails,
                    "item" => $item,
                ];
                break;

            default:
                return 1;
        }

        if (!empty($token)) {
            // $this->sendNotification($title, $message, $data, $token);
            if($device_type=='ios') {
                $sss = $this->sendNotificationNew($title, $message, [$token],$data);
            }
           
            if($device_type=='android') {
                $this->sendNotification($title, $message, $data, [$token]);
            }

        }
        $this->storeNotification($senderId, $receiverId, $title, $message, $type, $item, $deceasedById);

    }

    //store notification in notification table
    public function storeNotification($senderId, $receiverId, $title, $message, $type, $item, $deceasedById)
    {



        $noti = new Notification;
        $noti->sender_id = isset($senderId) ? $senderId : null;
        $noti->receiver_id = $receiverId;
        $noti->title = $title;
        $noti->message = $message;
        $noti->type = $type;
        $noti->marked_user_id = $deceasedById;
        if (is_object($item)) {
            $noti->item_id  =  $item->id;
            $noti->post_id  =  $item->id;
            $noti->group_id = $item->id; 
        } elseif (is_numeric($item)) {
            $noti->item_id = $item;  // if you passed just an ID instead of object
        } else {
            $noti->item_id = 0;      // default (system notification, no redirection)
        }
        $noti->save();

        if ($noti) {
            return true;
        }
    }

}



