<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\Rule;
use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Mail;
use App\Traits\FormatResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use ReceiptValidator\iTunes\Validator as iTunesValidator;

use App\Models\UserProfile;
use App\Models\PasswordReset;
use App\Models\Contact;
use App\Models\UserGroup;
use App\Models\AssignUserGroup;
use App\Models\AboutUs;
use App\Models\Post;
use App\Models\Album;
use App\Models\BlockUser;
use App\Models\AlbumPost;
use App\Models\MemberGroup;
use App\Models\DeviceDetail;
use App\Models\FamilyMember;
use App\Models\Like;
use App\Models\BurialInfo;
use App\Models\UserLiveStatus;
use App\Models\FollowerUnfollwer;
use App\Models\FamilyTagId;
use App\Models\InfoPage;
use App\Models\Tutorial;
use App\Models\ConnectionRequest;
use App\Models\Advertisement;
use App\Models\AdsSee;
use App\Models\Subscription;
use App\Models\Category;
use App\Models\AdsPrice;
use App\Models\TransactionHistory;
use App\Models\DeceasedReport;
use App\Models\BuyNewTag;
use App\Models\TagCollaborator;
use App\Models\SchedulingPost;
use App\Models\SavedTag;

use App\Mail\AdsRenewalReminder;
use App\Mail\RenewalAdPaymentProcess;
use App\Mail\ContactMail;
use App\Mail\SendMailreset;
use App\Traits\OneSignalTrait;
use App\Mail\DeleteAccountRequestEmail;
use App\Mail\DeleteAccountRequestSendAdmin;
use App\Mail\UserSendRequestByAdmin;
use App\Models\DeleteAccountOTP;
use App\Models\DeleteAccountRequest;
use App\Models\Notification;
use App\Models\TrustedPartners;
use App\Models\SubscribedPartner;
use App\Models\FeaturedCompanyPrice;
use App\Models\SubscriptionSetting;
use App\Models\InviteGuestUser;

use App\Services\StripeService;
use App\Services\UploadImage;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
// require '/home/ubuntu/public_html/backend/vendor/vendor/autoload.php';

require '../vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;
use Google\Client;
use Google\Service\AndroidPublisher;

// require '/home/ubuntu/public_html/backend/vendor/domPDF/autoload.php';

 //require '../vendor/domPDF/autoload.php';
use Dompdf\Options;
use Dompdf\Dompdf;
use App\Models\FQA;

use ReceiptValidator\GooglePlay\Validator as GooglePlayValidator;

class ApiController extends Controller
{ 
    use OneSignalTrait;
    use FormatResponseTrait;
    
    protected $StripeService;
    protected $storageClient;

    
    
    public function __construct(StripeService $StripeService,UploadImage $UploadImage)
    {
        $this->StripeService = $StripeService;
        $this->UploadImage = $UploadImage;
        
    }
    
    // lets check its working
    
    // signup
    public function signup(Request $request)
    {
        Validator::extend('lowercase_email', function ($attribute, $value, $parameters, $validator) {
            return strtolower($value) === $value; 
        });
    
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'email' => 'required|email|email:rfc,dns|lowercase_email',
            'password' => 'required',
            'role_id' => 'required',
        ], [
            'lowercase_email' => 'Please send the email in lowercase.', 
        ]);
    
        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first(), "status" => "failed", "data" => null], 403);
        }

        
        
        $sUser = DeleteAccountRequest::where('email', $request->email)->where('status','0')->first();
        if($sUser){
            return response()->json(['message' => 'Your account deletion request is currently being processed. An admin will be in touch with you shortly', 'status' => 'failed'], 400);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                "message" => "User already exists in this app", 
                "status" => "failed", 
                "data" => null,
            ], 400);
        }

        //send email verification mail
        $otp = rand(10000, 99999);

        DB::table('password_resets')->insert([
            'email' => $request->email, 'otp' => $otp,'type'=>'signup','created_at' => now()
        ]);

        // Send OTP to user's email
        Mail::to($request->email)->send(new OtpVerificationMail($otp));
       // return response()->json(["message" => "OTP sent to your email please check your email", "status" => "success"], 200);
        
    
        $checkEmail = User::where('email', $request->email)->withTrashed()->first();
        
        if ($checkEmail && $checkEmail->trashed()) {
            $userId = $checkEmail->id;
            $suffix = rand(100, 999);
            $username = strstr($checkEmail->email, '@', true);
            $domain = strstr($checkEmail->email, '@');
            $modifiedEmail = $username . '_deleted_' . $suffix . $domain;
            
            $checkEmail->email = $modifiedEmail;
            $checkEmail->save();
    
            // Modify the email in delete request account
            DeleteAccountRequest::where('user_id', $userId)->update(['email' => $modifiedEmail]);
        }
        
       
    
        try {
            // Create User
            $user = User::create([
                'first_name' => Str::ucfirst($request->first_name),
                'last_name' => Str::ucfirst($request->last_name),
                'role_id' => $request->role_id,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            $this->assignUserGroups($user->id, [1, 2]);
            
            $this->handleInvitations($user);
            
            $this->addDefaultAlbum($user->id);
            
            if($user){
                $data = User::find($user->id);
                $token = JWTAuth::attempt(['email' => $user->email, 'password' => $request->password]);
                if($token){
                    $data->token = $token;
                }
                $is_exist = DeviceDetail::where('user_id',$user->id)->first();
                $data['is_first_login'] = ($is_exist) ? false : true ;
        
                return response()->json(["message" => "Welcome to Famory, your account has been created successfully", "status" => "success", "data" => $data], 200);
            }else{
                return response()->json(["message" => "Welcome to Famory, your account has been created successfully", "status" => "success", "data" => $user], 200);
            }
    
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "data" => []], 500);
        }
    }

    public function register(Request $request)
    {
        Validator::extend('lowercase_email', function ($attribute, $value, $parameters, $validator) {
            return strtolower($value) === $value; 
        });
    
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username|max:255',
            'email' => 'required|email|unique:users,email|email:rfc,dns|lowercase_email',
            'password' => 'required',
            'role_id' => 'required|in:1,2',
        ], [
            'lowercase_email' => 'Please send the email in lowercase.', 
        ]);
    
        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first(), "status" => "failed", "data" => null], 403);
        }
        
        $sUser = DeleteAccountRequest::where('email', $request->email)->where('status','0')->first();
        if($sUser){
            return response()->json(['message' => 'Your account deletion request is currently being processed. An admin will be in touch with you shortly', 'status' => 'failed'], 400);
        }


        //send email verification mail
         $otp = rand(10000, 99999);

         DB::table('password_resets')->insert([
             'email' => $request->email, 'otp' => $otp,'type'=>'signup','created_at' => now()
         ]);


        // Send OTP to user's email
        Mail::to($request->email)->send(new OtpVerificationMail($otp));
       // return response()->json(["message" => "OTP sent to your email please check your email", "status" => "success"], 200);
        
    
        $checkEmail = User::where('email', $request->email)->withTrashed()->first();
        
        if ($checkEmail && $checkEmail->trashed()) {
            $userId = $checkEmail->id;
            $suffix = rand(100, 999);
            $username = strstr($checkEmail->email, '@', true);
            $domain = strstr($checkEmail->email, '@');
            $modifiedEmail = $username . '_deleted_' . $suffix . $domain;
            
            $checkEmail->email = $modifiedEmail;
            $checkEmail->save();
    
            // Modify the email in delete request account
            DeleteAccountRequest::where('user_id', $userId)->update(['email' => $modifiedEmail]);
        }
        
       
    
        try {
            // Create User
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
            ]);
    
            $this->assignUserGroups($user->id, [1, 2]);
            
            $this->handleInvitations($user);
            
            $this->addDefaultAlbum($user->id);
            
            if($user){
//                $data = User::find($user->id);
//                $token = JWTAuth::attempt(['email' => $user->email, 'password' => $request->password]);
//                if($token){
//                    $data->token = $token;
//                }
//                $is_exist = DeviceDetail::where('user_id',$user->id)->first();
//                $data['is_first_login'] = ($is_exist) ? false : true ;
        
                return response()->json(["message" => "Welcome to Famory, your account has been created successfully", "status" => "success", "data" => null], 200);
            }else{
                return response()->json(["message" => "Welcome to Famory, your account has been created successfully", "status" => "success", "data" => $user], 200);
            }
    
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "data" => []], 500);
        }
    }

    public function verifyEmailOTP(Request $request) {
        $email = $request->email;
        $getEmail = DeleteAccountRequest::where('email',$email)->first();
        if($getEmail){
            return response()->json(['message'=>'Your Request already exists.','status' => 404]);
        }else{
        
            $validator = Validator::make($request->all(), [
                'email' => [
                    'required',
                    'email:rfc,dns',
                    'string',
                    // Conditionally apply 'exists:users' if the 'type' is 'forgot_pass'
                    // function ($attribute, $value, $fail) use ($request) {
                    //     if ($request->type === 'forgot_pass' && !\App\Models\User::where('email', $value)->exists()) {
                    //         return $fail('The selected email does not exist.');
                    //     }
                    // }
                ],
               // 'type' => ['required', 'in:signup,forgot_pass'],
                'otp' => [
                    'required',
                    Rule::exists('password_resets')->where(function ($query) use ($request) {
                        return $query->where([
                            'email' => $request->email,
                            'otp' => $request->otp,
                            //'type'=>$request->type
                        ])
                       ->where('created_at', '>=', Carbon::now()->subMinutes(5));
                    })
                ]
            ], [
                'email.required' => 'Email is required.',
                'email.exists' => 'The email you entered was not found.',
                'otp.required' => 'OTP is required.',
                'otp.exists' => 'The OTP you entered was not correct, please try again.'
            ]);
        
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 'validation_error', 400);
            }
        
            DB::beginTransaction();
            try {
                DB::table('password_resets')
                    ->where([
                        'email' => $request->email,
                        'otp' => $request->otp
                    ])
                    ->update(['verified_at' => Carbon::now()]);
        
                DB::commit();
        
                $msg = 'OTP has been verified successfully!.';
        
                
                $data = User::where('email', $request->email)->first();

                $token = JWTAuth::fromUser($data);
                if($token){
                    $data->token = $token;
                }
                $is_exist = DeviceDetail::where('user_id',$data->id)->first();
                $data['is_first_login'] = ($is_exist) ? false : true ;
            
                return response()->json(["message" => $msg, "status" => "success", "data" => $data], 200);
                
                return $this->successResponse($msg, 200);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->errorResponse($e->getMessage(), 'internal_server_error', 500);
            }
        }
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
    
        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first(), "status" => "failed", "data" => null], 403);
        }

        $otp = rand(10000, 99999);
        
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp' => $otp,
                'type' => 'signup',
                'created_at' => now()
            ]
        );


        // Send the OTP via email (or SMS, depending on your preference)
        Mail::to($request->email)->send(new OtpVerificationMail($otp));

        return response()->json(['message' => 'OTP resent successfully.', "status" => "success"], 200);
    }

    protected function assignUserGroups($userId, array $groupIds)
    {
        foreach ($groupIds as $groupId) {
            AssignUserGroup::create([
                'user_id' => $userId,
                'user_group_id' => $groupId,
                'is_notify' => false,
            ]);
        }
    }
    
    protected function handleInvitations($user)
    {
        $inviteEmails = InviteGuestUser::where('email', $user->email)->get();
        foreach ($inviteEmails as $invitedUser) {
            $exists = FamilyMember::where('user_id', $user->id)
                ->where('member_id', $invitedUser->sender_id)
                ->where('group_id', 1) 
                ->exists();
    
            if (!$exists) {
                FamilyMember::create([
                    'user_id' => $user->id,
                    'group_id' => 1,
                    'member_id' => $invitedUser->sender_id,
                ]);
            }
            
            $this->notifyMessage($user, $invitedUser->sender_id, $user, "invite_user", null, null);
            
            $getData = ConnectionRequest::where(['sender_id'=>$invitedUser->sender_id, 'guest_email'=>$user->email])->first();
            $getData->status = "Invitation accepted";
            $getData->user_id = $user->id;
            $getData->is_verify = "1";
            $getData->save();
            
        }

        
        
        
        
    }
    
    public function addDefaultAlbum($userId){
        $album = new Album();
        $album->album_name = "Saved Posts";
        $album->user_id = $userId;
        $album->save();
    }



    public function deleteAccount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'required'
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return $this->errorResponse(ucfirst($value), 'validation_error', 400);
                }
            }
            
            $current_user = Auth::guard('api')->user();
            $getHeaders = apache_request_headers();
            $trLanguage = isset($getHeaders['lang']) ? $getHeaders['lang'] : 'en';
            
            $getEmail = DeleteAccountRequest::where('email', $current_user->email)->first();
            if ($getEmail) {
                $msg = 'The email has already been used.';
                return response()->json(['message' => $msg, 'status' => '404']);
            }
             $name = trim($current_user->first_name . ' ' . $current_user->last_name);
            // Save delete account request
            $data = new DeleteAccountRequest;
            $data->name = $current_user->first_name;
            $data->email = $current_user->email;
            $data->phone_number = null;
            $data->reason_for_deletion = $request->reason;
            $data->source = 'Mobile';
            $data->user_id = $current_user->id;
            $data->status = 0;
            $data->save();
            $adminEmails = User::where('role_id', 1)->get();
            if ($adminEmails->isNotEmpty()) {
                foreach($adminEmails as $adminEmail){
                    Mail::to($adminEmail->email)->send(new DeleteAccountRequestSendAdmin($data->name, $data->email, $data->reason_for_deletion));
                }
                
            }
            
            $msg = 'Your delete account request has been sent successfully.';
            return response()->json(['message' => $msg, 'status' => "success","error_type" => "",]);
            
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 'internal_server_error', 500);
        }
    }

    // Login 
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return response()->json(['message' => $value, 'status' => 'failed', 'isEmailVerfied' => null], 400);
            }
        }
        $data = $request->all();
        try {
            
            $deleteRequest = DeleteAccountRequest::where('email', $data['email'])->first();

            if ($deleteRequest) {
                return response()->json(['message' => 'Your account deletion request is currently being processed. An admin will be in touch with you shortly', 'status' => 'failed', 'isEmailVerfied' => null], 400);
            }

            if (!$token = JWTAuth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                return response()->json(["message" => "Invalid Credentials !", "status" => "failed", "data" => NULL, 'isEmailVerfied' => null], 400);
            }

//            $oldOTP = DB::table('password_resets')->where('email', $request->email)->where('type', 'signup')->first();
            $oldOTP = DB::table('password_resets')->where('email', $request->email)->first();
            
            if(is_null($oldOTP)) {
                    return $this->generateAndSendOTP($request->email);
            } else {
                if(is_null($oldOTP->verified_at)) {
                    return $this->generateAndSendOTP($request->email);
                }
            }
            $user = Auth::user();
            
            if ($user->ban_user == 1) {
                return response()->json(['message' => 'Your account has been banned.', "status" => "failed", "data" => NULL, 'isEmailVerfied' => null], 400);
            }

            
            $user['token'] = $token;
            $is_exist = DeviceDetail::where('user_id',$user->id)->first();
            $user['is_first_login'] = ($is_exist) ? false : true ;
            $user['is_verified'] = (is_null($oldOTP->verified_at)) ? false : true ;
            return response()->json(["message" => "Login Successful, welcome to the Famory", "status" => "success", "data" => $user, 'isEmailVerfied' => null], 200);
        } catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "data" => [], 'isEmailVerfied' => null], 500);
        }
    }
    
    protected function generateAndSendOTP($email, $type = 'signup') {
        //send email verification mail
         $otp = rand(10000, 99999);

        DB::table('password_resets')->updateOrInsert(
         ['email' => $email],
            [
                'otp' => $otp,
                'created_at' => now()
            ]
        );

        // Send OTP to user's email
        Mail::to($email)->send(new OtpVerificationMail($otp));
        
        return response()->json(['message' => 'Your email is not verified yet.', "status" => "failed", "data" => NULL, 'isEmailVerfied' => 0], 400);
    }
    
    public function getProfile(Request $request)
    {
        try {
        
            $current_user = Auth::user()->id;
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
           
            $data =User::where('id', $current_user)->with([
                'group',
                'group.group_name',
                'burialinfo',
                'last_will_url',
                'post',
                'album' => function($query) {
                    $query->orderBy('created_at', 'desc')->take(5)
                    ->withCount('posts'); // Add count for posts in each album
                },
                'album.posts',
            ])
            // ->addSelect([
            //     // Add the count of saved posts (assuming `savedPosts` relationship exists)
            //     'saved_posts_count' => User::where('id', $current_user)
            //         ->join('post_user', 'users.id', '=', 'post_user.user_id') // Adjust table name if needed
            //         ->where('post_user.saved', true) // Assuming there is a 'saved' column indicating saved posts
            //         ->where('post_user.user_id', $current_user) // Filter for current user
            //         ->count(),
        
            //     // Add the count of saved albums (assuming `savedAlbums` relationship exists)
            //     'saved_albums_count' => User::where('id', $current_user)
            //         ->join('album_user', 'users.id', '=', 'album_user.user_id') // Adjust table name if needed
            //         ->where('album_user.saved', true) // Assuming there is a 'saved' column indicating saved albums
            //         ->where('album_user.user_id', $current_user) // Filter for current user
            //         ->count(),
            // ])
            ->first();
            
            $data->saved_post_count = 0;
            $data->saved_album_count = 0;

            $data->last_will = $data->last_will_url ? $data->last_will_url->video : null;
            $data->last_will_updated_at= $data->last_will_url ? $data->last_will_url->updated_at : null;
           
            // $data->subscribed = $this->checksubscription($current_user); 
 
            
            $familyMembers = FamilyMember::where('user_id', $current_user)->orWhere('member_id', $current_user)->orderBy('id', 'desc')->limit(20)->get();
            $simplifiedData = $familyMembers->map(function ($familyMember) {
                
                if($familyMember->member_id == Auth::id()){
                    if(empty($familyMember->member)){
                        return;
                    }else{
                        $userId = $familyMember->member_id;
                        $memberId = $familyMember->user_id;
                        $user = [
                            'id' => $familyMember->member->id,
                            'first_name' => $familyMember->member->first_name,
                            'last_name' => $familyMember->member->last_name,
                            'image' => $familyMember->member->image,
                        ];
                    }
                    
                }else{
                    if(empty($familyMember->user)){
                         return;
                    }else{
                        $userId = $familyMember->user_id;
                        $memberId = $familyMember->member_id;
                        $user = [
                            'id' => $familyMember->user->id,
                            'first_name' => $familyMember->user->first_name,
                            'last_name' => $familyMember->user->last_name,
                            'image' => $familyMember->user->image,
                        ];
                    }
                }
                
                return [
                    'id' => $familyMember->id,
                    'user_id' => $userId,
                    'member_id' => $memberId,
                    'user' => $user,
                ];
            })->whereNotIn('user.id', $blockedUserIds)->filter()->values();
            $data->family = $simplifiedData;
            unset($data->last_will_url); // removing last_will key forcefully
            
            $subscribed_user = Subscription::where('user_id', $current_user)->first();
            
            if($subscribed_user){
                if($subscribed_user->subscription == 'free'){
                    $data['is_subscription'] = "true";
                }else{
                    $data['is_subscription'] = $this->subscribe_validation()->original['data'] == "" ? "false" : "true" ;
                }
            }else{
                $data['is_subscription'] = $this->subscribe_validation()->original['data'] == "" ? "false" : "true" ;
            }
            
            $data['subscribed'] = $this->subscribe_validation()->original['data'] == "" ? null : $this->subscribe_validation()->original['data'];
            
            
            $isExist = UserLiveStatus::where('user_id',$current_user)->orderBy('id','DESC')->first();
            if($isExist){
                if($isExist->is_alive == 0){
                    $data->is_live = false;
                }else{
                    $data->is_live = true;
                }
            }else{
                $data->is_live = true;
            }
            if ($data) {
                return response()->json(["message" => "profile retrieve successfully", "status" => "success","data" => $data], 200);
            } else {
                return response()->json(["message" => "Oops!, profile not get", "status" => "failed", "data" => []], 400);
            }
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed', 'error_type' => $exception->getMessage(), "data" => []], 401);
        }
    }


    
    public function updateProfile(Request $request){
        try{
            $current_user = Auth::user()->id;
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:255',
                'phone' => 'nullable|numeric|digits:10',
            ]);
    
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    $response = $value;
                    return response()->json(["message" => $response, "status" => "failed", "data" => []], 400);
                }
            }
            
            $getUser = User::where('id',$current_user)->first();
            $getUser->first_name = Str::ucfirst($request->first_name);
            $getUser->last_name = Str::ucfirst($request->last_name);
            $getUser->phone   = $request->phone;
            if($request->dob){
             $getUser->dob = $request->dob;
            }
            if($request->gender){
                $getUser->gender = $request->gender;
            }
            if($request->is_private){
                $getUser->is_private = $request->is_private;
            }
            if($request->description){
                $getUser->description = $request->description;
            }
            if($request->image){
                
                $file = $request->file('image');
                $res = $this->UploadImage->saveMedia($file,$current_user);
                $getUser->image = $res;
                
            }
            
            $getUser->save();
            
            
            if(!$getUser){
                return response()->json(["message" => "Profile not update successfully", "status" => "failed", "data" => []], 400);
            }
            
            return response()->json(["message" => "Profile update successfully", "status" => "success", "data" =>$getUser], 200);
            
        } catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "data" => []], 500);
        }
    }
    
    
    
    public function uploadMedia($file, $media)
    {
        $image_ext = array('bmp', 'gif', 'ico', 'jpeg', 'jpg', 'png', 'svg', 'tif', 'tiff', 'webp');
        if ($media) {
            $extension = $media->extension();
            if (in_array($extension, $image_ext)) {
                if ($media->isValid()) {
                    $fileName = time() . '_' . $media->getClientOriginalName();
                    $destinationPath = public_path('/assets/'.$file);
                    $media->move($destinationPath, $fileName);
                    $imageURL = asset('/assets/'.$file.'/'.$fileName);
                    return $imageURL;
                } else {
                    return response()->json(["message" => "Uploaded file is not valid", "status" => "failed"], 400);
                }
            } else {
                return response()->json(["message" => "Image extension not match", "status" => "failed"], 400);
            }
        } else {
            return response()->json(["message" => "No media file provided", "status" => "failed"], 400);
        }
    }
    
    
     // Function for Forgot Password
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc,dns', 'string', 'exists:users']
        ], [
            'required' => ':attribute is required.',
            'exists' => ':attribute is not registered'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return $this->errorResponse(ucfirst($value), 'validation_error', 400);
            }
        }


        DB::beginTransaction();
        try {
//            $oldOTP = DB::table('password_resets')->where('email', $request->email)->first();
//            if ($oldOTP) {
//                DB::table('password_resets')->where('email', $request->email)->delete();
//            }
            $CODE = rand(10000, 99999);
//            DB::table('password_resets')->insert([
//                'email' => $request->email, 'otp' => $CODE,'type' => 'signup', 'created_at' => now()
//            ]);
            DB::table('password_resets')
                ->where([
                    'email' => $request->email
                ])
                ->update(['otp' => $CODE,'created_at' => now()]);
           
            $user = DB::table('users')->where('email', $request->email)->first(['first_name', 'last_name']);
            // OTP sent in email
             Mail::to($request->email)->send(new SendMailreset($CODE, $request->email, $user->first_name, $user->last_name));

            DB::commit();

            $msg = 'Please enter the code that was sent to the email associated with your account.';


            return $this->successResponse($msg, 200);
        } catch (Exception $exception) {
            DB::rollBack();

            $msg = 'An unexpected error occurred. Please try again later.';
            return $this->errorResponse($msg, 'internal_server_error', 500);
        }
    }
    // // verify otp 
    
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email:rfc,dns',
                'string',
                // Conditionally apply 'exists:users' if the 'type' is 'forgot_pass'
                // function ($attribute, $value, $fail) use ($request) {
                //     if ($request->type === 'forgot_pass' && !\App\Models\User::where('email', $value)->exists()) {
                //         return $fail('The selected email does not exist.');
                //     }
                // }
            ],
           // 'type' => ['required', 'in:signup,forgot_pass'],
            'otp' => [
                'required',
                Rule::exists('password_resets')->where(function ($query) use ($request) {
                    return $query->where([
                        'email' => $request->email,
                        'otp' => $request->otp,
                        //'type'=>$request->type
                    ])->whereNull('verified_at');
                })
            ]
        ], [
            'email.required' => 'Email is required.',
            'email.exists' => 'The email you entered was not found.',
            'otp.required' => 'OTP is required.',
            'otp.exists' => 'The OTP you entered was not correct, please try again.'
        ]);
    
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 'validation_error', 400);
        }
    
        DB::beginTransaction();
        try {
            DB::table('password_resets')
                ->where([
                    'email' => $request->email,
                    'otp' => $request->otp
                ])
                ->update(['verified_at' => Carbon::now()]);
    
            DB::commit();
    
            $msg = 'OTP has been verified successfully!.';
    
            return $this->successResponse($msg, 200);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 'internal_server_error', 500);
        }
    }

        //Deleted User
    public function deleteUserNew(Request $request){
        $user = User::find($request->user_id);
        if($user){
            $address = Address::where('user_id',$user->id)->delete();
            $availability = Availability::where('user_id',$user->id)->delete();
            $bookingsession = Bookingsession::where('user_id',$user->id)->orWhere('pract_id',$user->id)->delete();
            $bookingSessionTemp = BookingSessionTemp::where('user_id',$user->id)->delete();
            $contact = Contact::where('user_id',$user->id)->delete();
            $deviceDetail = DeviceDetail::where('user_id',$user->id)->delete();
            $favorite = Favorite::where('user_id',$user->id)->orWhere('fav_user_id',$user->id)->delete();
            $note = Note::where('user_id',$user->id)->orWhere('pract_id',$user->id)->delete();
            $notification = Notification::where('sender_id',$user->id)->orWhere('reciver_id',$user->id)->delete();
            //$payment = Payment::where('user_id',$user->id)->orWhere('pract_id',$user->id)->delete();
            $price = Price::where('user_id',$user->id)->delete();
            $rating = Rating::where('from_user_id',$user->id)->orWhere('to_user_id',$user->id)->delete();
            $servicesDetail = ServicesDetail::where('user_id',$user->id)->delete();
            $subscription = Subscription::where('user_id',$user->id)->delete();
            $transactionHistory = TransactionHistory::where('user_id',$user->id)->delete();
            $userAvailability = UserAvailability::where('user_id',$user->id)->delete();
            $userCertification = Price::where('user_id',$user->id)->delete();
           // $user->delete();
            return $this->successResponse('User deleted successfully', 200);
        }else{
            return $this->errorResponse('user not found', 'user_not_found', 400);
        }
        
    }
     // this function is used to update password
    
    public function resetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email:rfc,dns', 'string', 'exists:users'],
//            'otp'     => ['required', Rule::exists('password_resets')->where(function ($query) use ($request) { return $query->where([ 'email' => $request->email, 'otp' => $request->otp])->whereNotNull('verified_at')->first(); })],
//            'otp'     => ['required', Rule::exists('password_resets')->where(function ($query) use ($request) { return $query->where([ 'email' => $request->email, 'otp' => $request->otp])->first(); })],
            'password' => ['required', 'confirmed']
        ], [
            'required' => ':attribute is required.',
//            'otp.exists' => 'Invalid Otp, please double-check the code you enter and try again.'
        ]);
        if($validator->fails()){
            $errors = $validator->errors();
            foreach($errors->all() as $key => $value){
               return $this->errorResponse($value, 'validation_error', 400);
            }
        }
        
        // Message translated into another language
        
        $getHeaders = apache_request_headers();
        $trLanguage = isset($getHeaders['lang']) ? $getHeaders['lang'] : 'en';
        DB::beginTransaction();
        try{
            $user = User::whereEmail($request->email)->update(['password' => bcrypt($request->password)]);
//            DB::table('password_resets')->where([ 'email' => $request->email, 'otp' => $request->otp])->delete();
            DB::commit();
        }catch (Exception $exception) {
            DB::rollBack();
            return $this->errorResponse($exception-> getMessage(), 'internal_server_error', 500);
        }
        
        $msg = "Your password has been changed, you may now login with your new credentials.";
        // $message = $this->translationData($trLanguage,$msg);
        
        return $this->successResponse($msg,200);
    }
    
    
    public function logout(){
      try {
            $current_user = Auth::user()->id;
            DeviceDetail::where('user_id', $current_user)->update(['is_user_loggedin' => 0]);
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out', "status" => "success"],200);
        } catch (JWTException $exception) {
            return response()->json(['error' => 'Sorry, the user cannot be logged out'], 500);
        }
    }
    
    public function contactUs(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'phone' => 'nullable|numeric',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            
            $contact = new Contact();
            $contact->name = Auth::user()->first_name;
            $contact->phone = $request->phone;
            $contact->email = $request->email;
            $contact->message = $request->message;
            $contact->user_id = Auth::user()->id;
            $contact->save();
            if($contact){
                return response()->json(['message' => 'Thank you for contacting us. Famory will review your request and get back to you as soon as we can.', "status" => "success","data" => $contact],200);
            }
        }catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
            
    }
    
    public function verifyPassword(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'password' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            
            $currentUser = Auth::user();
            $checkPass = User::where('id',$currentUser->id)->first();
            $password = $request->password;
            if(Hash::check($password, $checkPass->password)) {
                if($request->new_password){
                    $checkPass->password = Hash::make($request->new_password);
                    $checkPass->save();
                    if($checkPass){
                         return response()->json(['message' => 'Password updated successfully.', "status" => "success","data" => null],200);
                    }
                }
                return response()->json(['message' => 'Password verified successfully', "status" => "success","data" => null],200);
            } else {
                return response()->json(['message'=>'Password is not matched with current password.','status'=>'false',"data" => null],400);
            }
        }catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    
    public function aboutUs(){
        try{
            $data = AboutUs::find('1');
            return response()->json(['message' => 'About-us get successfully', "status" => "success","data" => $data],200);
        }catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function tutorial(){
        try{
            $data = Tutorial::orderBy('id','Desc')->get();
            return response()->json(['message' => 'Tutorial get successfully', "status" => "success","data" => $data],200);
        }catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
   
    
    public function faq(){
        try{
            $data = FQA::orderBy('id','DESC')->paginate(10);
            return $this->successResponse(" FAQ get successfully", 200,$data->items(), $data);
            // return response()->json(['message' => 'FAQ get successfully', "status" => "success","data" => $data],200);
        }catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function terms(){
        try{
            $data = InfoPage::where('page_url','terms-and-conditions')->first();
            return response()->json(['message' => 'Terms & Condition get successfully', "status" => "success","data" => $data],200);
        }catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function privacyPolicy(){
        try{
            
            $data = InfoPage::where('page_url','privacy-policy')->first();
            // $data = new \stdClass();
            // $data->id = '1';
            // $data->title = "Privacy-Policy";
            // $data->details = "We collect and store your data to enhance your experience. Your information may be shared with third parties for service improvement. We implement security measures but cannot guarantee complete safety. By using the app, you consent to our data practices. Review periodically for updates. Contact us for concerns.";
            return response()->json(['message' => 'Privacy-Policy get successfully', "status" => "success","data" => $data],200);
        }catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
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

            $addGroup = new UserGroup;
            $addGroup->name = $request->name;
            $addGroup->save();
            
            if(!$addGroup){
                return response()->json(['message' => 'Group not added', "status" => "fail","data" => null],200);
            }
            return response()->json(['message' => 'Group Added successfully', "status" => "success","data" => $addGroup],200);
        }catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function addAboutUs(Request $request){
            $addGroup = AboutUs::find('1');
            $addGroup->title = $request->title;
             if($request->image){
                $file = 'about-us';
                $imageURL = $this->uploadMedia($file, $request->image);
                $addGroup->image = $imageURL;
            }
            $addGroup->details = $request->details;
            $addGroup->save();
            return response()->json(['message' => 'data save', "status" => "success","data" =>$addGroup ],200);
    }
    
    // public function storeDeviceDetails(Req
        public function storeDeviceDetails(Request $request){
        $validator = Validator::make($request->all(), [
            'device_token' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
                return response()->json(['message' => $value, 'status' => 'failed'], 400);
            }
        }
        
        try{
            
            $user_id = Auth::user()->id;
            $deviceData = DeviceDetail::where('user_id',$user_id)->first();
            if(empty($deviceData)){
                $response =  new DeviceDetail;
                $response->user_id = $user_id;
                $response->device_token = $request->device_token;
                $response->platform = $request->platform;
                $response->app_version = $request->app_version;
                $response->is_user_loggedin = '1';
                $response->time_zone = $request->time_zone;
                $response->is_prod  = $request->is_prod ;
                $response->uuid = $request->uuid;
                $response->lat  = $request->lat ;
                $response->long = $request->long;
                $response->save();  
            }else{
                $deviceId = $deviceData->id;
                $response = DeviceDetail::find($deviceId);
                $response->user_id = $user_id;
                $response->device_token = $request->device_token;
                $response->platform = $request->platform;
                $response->app_version = $request->app_version;
                $response->is_user_loggedin = '1';
                $response->time_zone = $request->time_zone;
                $response->is_prod  = $request->is_prod;
                $response->uuid = $request->uuid;
                $response->lat  = $request->lat ;
                $response->long = $request->long;
                $response->save();
            }
            if($response){
                return $this->successResponse("Device details were saved successfully",200, $response);  
            }else{
                return $this->errorResponse("Device details Not added", 'device_details_not_found', 200);
            }
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 'internal_server_error', 500);
        }
       
    }

    public function blockedUser(Request $request) {
        try {
            $validatedData = $request->validate([
                'marked_user_id' => 'required|integer',
                'is_live' => 'boolean',
                'block' => 'required|boolean',
            ]);
    
            $user_id = Auth::id();
            $marked_user_id = $request->marked_user_id;
            
            
            
            
            if($request->is_live == "1"){
                $liveStatus = new UserLiveStatus();
                $liveStatus->user_id = $user_id;
                $liveStatus->is_alive = 1;
                $liveStatus->alive_by = $user_id;
                $liveStatus->save();
                $updateResult = UserLiveStatus::where(['user_id' => $user_id, 'is_alive' => 0])->update(['notify' => 1]);
            }
            
            $lastNotification = Notification::where('receiver_id', $user_id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($lastNotification && ($lastNotification->type == "self" || $lastNotification->type == "deceased")) {
                $lastNotification->has_actioned = 1;
                $lastNotification->save();
            }
            
            $blockUser = BlockUser::where('user_id', $user_id)->where('marked_user_id', $marked_user_id)->first();
    
            if ($blockUser) {
                
                if ($blockUser->block == $request->block) {
                    return response()->json(["message" => "User is already " . ($request->block ? "blocked" : "unblocked"), "status" => "success", "data" => $blockUser], 200);
                }
                
                $blockUser->block = $request->block;
                
                if ($request->has('is_live')) {
                    $blockUser->is_live = $request->is_live;
                }
                
                $blockUser->save();
                $message = "User block status updated successfully";
            } else {
                
                
                // If the record does not exist, create a new one
                $blockUser = new BlockUser;
                $blockUser->user_id = $user_id;
                $blockUser->marked_user_id = $marked_user_id;
                $blockUser->is_live = $request->is_live ?? false; // Default to false if is_live is not provided
                $blockUser->block = $request->block;
                $blockUser->save();
                
                // Also create the reverse block record
                $reverseBlockUser = new BlockUser;
                $reverseBlockUser->user_id = $marked_user_id;
                $reverseBlockUser->marked_user_id = $user_id;
                $reverseBlockUser->is_live = $request->is_live ?? false; // Default to false if is_live is not provided
                $reverseBlockUser->block = $request->block;
                $reverseBlockUser->save();
                
                
                
                $message = "Thank you for confirming that you are alive. we will update your status shortly.";
                $blockUser = BlockUser::where('id', $blockUser->id)->first();
                
            }

            
            return response()->json(["message" => $message, "status" => "success", "data" => $blockUser], 200);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed', "data" => []], 500);
        }
    }




    public function getBlockedUsers(Request $request)
    {
        try {
             $user_id = Auth::id();
            // $blockedUsers = BlockUser::with('blockedUser')->where('user_id', $user_id)->get();
             $blockedUsers = BlockUser::with('blockedUser')
                    ->when($request->search, function ($query) use ($request) {
                        $searchTerm = '%' . $request->search . '%';
                        $query->whereHas('blockedUser', function ($query) use ($searchTerm) {
                            $query->where('first_name', 'like', $searchTerm)
                                ->orWhere('last_name', 'like', $searchTerm);
                        });
                    })
                ->where('user_id', $user_id)
                ->where('block', 1)
                ->paginate(10);
            
            return $this->successResponse(" Blocked users retrieved successfully", 200,$blockedUsers->items(), $blockedUsers);
            // return response()->json(["message" => "Blocked users retrieved successfully", "status" => "success", "data" => $blockedUsers], 200);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed', "data" => []], 500);
        }
    }
    
    public function getUserById(Request $request, $user_id)
    {
        // $user = User::find($user_id);
        $user = User::with(['burialinfo', 'last_will_url', 'userLiveStatus'])
                    ->find($user_id);
                    
        if($user) {
            $user->is_live = true;
            $user->passed_date = null;
            $isExist = UserLiveStatus::where('user_id',$user_id)->orderBy('id','DESC')->first();
            if($isExist){
                if($isExist->is_alive == 0){
                    // $update_time = $isExist->created_at->addMinutes(2)->toDateTimeString();
                    $update_time = $isExist->created_at->addHours(72)->toDateTimeString();
                    $current_time = Carbon::now()->toDateTimeString();
                    if($current_time >= $update_time){
                        $user->is_live = false;
                        $user->passed_date = $isExist->created_at->format('m/d/y');
                    }else{
                        $user->is_live = true;
                    }
                }else{
                    $user->is_live = true;
                }
            }else{
                $user->is_live = null;
            }
            
            
            $isFollowing = FollowerUnfollwer::where(['user_id' => Auth::id(), 'following_id' => $user_id])->exists();
            $user->is_following = $isFollowing;
            unset($user->userLiveStatus);
            $member = FamilyMember::where(function ($query) use ($user_id) {
                $query->where(['user_id' => Auth::id(), 'member_id' => $user_id])
                      ->orWhere(function ($query) use ($user_id) {
                          $query->where(['user_id' => $user_id, 'member_id' => Auth::id()]);
                      });
            })->first();

            $user->is_family_member = (!empty($member)) ? true : false;
            
            return response()->json(['message' => 'Successfully retrieved user data', 'status' => 'success','data' => $user], 200);
        } else {
            return response()->json(['message' => 'User not found','status' => 'error','data' => null], 404);
        }
    }


    public function createAlbum(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'album_name' => 'required|string',
            ]);

            $user_id = Auth::id();
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }


            $album = new Album();
            $album->album_name = $request->album_name;
            $album->user_id = $user_id;
            $album->save();

            return $this->successResponse("Album created successfully", 200, $album);

        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    

    public function getAllAlbums(Request $request)
        {
            try {
                $perPage = $request->input('per_page', 10); 
                $user = Auth::user(); 
                $albums =Album::where('user_id', $user->id)->orderBy('created_at', 'asc')->paginate($perPage);
                if(!$albums){
                    return $this->successResponse(" Not any albums retrieved successfully", 200,$albums->items(), $albums);
                }
      
               return $this->successResponse(" All Albums retrieved successfully", 200,$albums->items(), $albums);
        
            } catch (\Exception $exception) {
                return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
            }
        }


    public function getAlbum($id, Request $request)
    {
        try {
            $user = Auth::user();
            $album = Album::findOrFail($id);
            $perPage = $request->input('per_page', 10);
            $albumPosts = AlbumPost::where('album_id', $id)->with('post','post.user:id,first_name,last_name')->orderBy('created_at', 'desc')->paginate($perPage);
            
            foreach($albumPosts as $albumPost){
                $albumPost->post->like_count = Like::where('post_id', $albumPost->post->id)->count() ?? 0;
                $albumPost->post->is_like = Like::where(['post_id' => $albumPost->post->id, 'user_id' => $user->id])->exists();
                $albumPost->post->is_following = FollowerUnfollwer::where(['user_id' => $user->id, 'following_id' => $albumPost->post->user_id])->exists();
                $albumPost->post->created_date = date('m/d/y', strtotime($albumPost->post->scheduling_post->created_at));
                $albumPost->post->posted_date = $albumPost->post->scheduling_post->schedule_type == "now" ? date('m/d/y', strtotime($albumPost->post->scheduling_post->created_at)) : Carbon::parse($albumPost->post->scheduling_post->schedule_date)->format('m/d/y');
                $albumPost->post->scheduling_post->schedule_date =  Carbon::parse($albumPost->post->scheduling_post->schedule_date)->format('m/d/y');
                $albumPost->post->scheduling_post->schedule_time = $albumPost->post->scheduling_post->schedule_type == "now" ? date('h:i A', strtotime($albumPost->post->scheduling_post->created_at)) : date('h:i A', strtotime($albumPost->post->scheduling_post->schedule_time));
                if ($albumPost->post->scheduling_post) {
                    $albumPost->post->scheduling_post->makeHidden(['id', 'post_id']);
                }
            }
            return $this->successResponse("Album retrieved successfully", 200, $albumPosts->items(), $albumPosts);
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
                // return $this->errorResponse("Album not found",'error',"status" => "success" 404);
                return response()->json(["message" => 'Album Not Found', "status" => "success", "error_type" => "",]);
                
        }catch (\Exception $exception) {
                return $this->errorResponse("Internal Server Error",'error', 500);
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

    public function addAlbumPost(Request $request)
    {
        try {
            // Validate the incoming request data
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
                'post_id' => 'required|exists:posts,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            // Get the authenticated user's ID
            $user_id = Auth::id();
            // Check if the combination of album_id, post_id, and user_id already exists
             
            $existingAlbumPost = AlbumPost::where('album_id', $request->album_id)
                ->where('post_id', $request->post_id)
                ->where('user_id', $user_id)
                ->first();
    
            if ($existingAlbumPost) {
                return response()->json(['message' => 'The post has already been added to an album by this user', 'status' => 'failed'], 400);
            }
            
            // Create a new AlbumPost instance
            $albumPost = new AlbumPost();
            $albumPost->album_id = $request->album_id;
            $albumPost->post_id = $request->post_id;
            $albumPost->user_id = $user_id;
            $albumPost->save();
 
            $post = Post::find($request->post_id);
            $thumbnailPath = null;
            $fileExtension = strtolower(pathinfo(
                $post->video_formats['original'] ?? $post->file, PATHINFO_EXTENSION
            ));
            $fileType = $this->getFileType($fileExtension);
            
            
            

            if ($fileType === 'videos') {

                $videoFilename = $post->video_formats['thumbnails']['medium'];
                $urlComponents = parse_url($videoFilename);
                $pathOnly = $urlComponents['path'];
                $thumbnailPath = $pathOnly;
                
            } elseif ($fileType === 'images'){
                
                // $thumbnailPath = $post->file;
                $urlComponents = parse_url($post->file);
                $pathOnly = $urlComponents['path'];
                
                $thumbnailPath = $pathOnly;
                
            } elseif ($fileType === 'audio'){
                 $thumbnailPath = config('app.url') . '/assets/img/audio_bg.webp';
                 
            }
            
            if(!empty($thumbnailPath)){
                $album = Album::find($request->album_id);
                $album->album_cover = $thumbnailPath;
                $album->save();
            }else{
                $album = Album::find($request->album_id);
                $album->album_cover = null;
                $album->save();
            }

            return response()->json(['message' => 'Post added to album successfully', 'status' => 'success', 'data' => $albumPost], 200);

        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function userInformationForDeleteAC(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required',
                'phone' => 'nullable',
                'reason' => 'required',
                
            ]);

            if ($validator->fails()){
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return $this->errorResponse(ucfirst($value), 'validation_error', 400);
                }
            }
            
            $otp = $request->verification;
            $getOTPInDB = DB::table('password_resets')->where('email', $request->email)->first();
            if($getOTPInDB){
                    if ($otp == $getOTPInDB->otp) {
                         $existingRequest = DeleteAccountRequest::where('email', $request->email)->first();
                    if($existingRequest) {
                        return response()->json(['message'=>'A delete account request has already been submitted for this email','status' => 400]);
                    }
                    $data = new DeleteAccountRequest;
                    $data->name = $request->name;
                    $data->email = $request->email;
                    $data->phone_number = $request->phone;
                    $data->reason_for_deletion = $request->reason;
                    $data->source = 'Web';
                    $userData = User::where('email',$request->email)->first();
                    $data->user_id = $userData->id;
                    $data->status = 0;
                    $data->save();
                    if($data){
                        $adminEmails = User::where('role_id',1)->get();
                        foreach($adminEmails as $adminEmail){
                            Mail::to($adminEmail->email)->send(new DeleteAccountRequestSendAdmin($data->name,$data->email,$data->reason));
                        }

                        
                        return response()->json(['message'=>'Your delete account request has been sent successfully','status' => 200]);
                    }
                } else {
                    return response()->json(['message'=>'OTP Does Not Correct , Please Check Again','status' => 404]);
                }
            }else{
                return response()->json(['message'=>'Email Does Not Correct, Please Check Again','status' => 404]); 
            }
            
        }catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 'internal_server_error', 500);
        }
    }
	
	
	    //  function is use to verify email and send otp in email.
    public function verifyEmail(Request $request){
        try{
            $email = $request->email;
            $getEmail = DeleteAccountRequest::where('email',$email)->first();
            if($getEmail){
                return response()->json(['message'=>'Your Request already exists.','status' => 404]);
            }else{
                $userEmail = User::where('email',$email)->first();
                if($userEmail){
                    $oldOTP = DB::table('password_resets')->where('email', $request->email)->first();
                    if($oldOTP) {
                        DB::table('password_resets')->where('email', $request->email)->delete();
                    }
                    $CODE = rand(10000, 99999);
                    DB::table('password_resets')->insert(['email' => $request->email, 'otp' => $CODE, 'created_at' => now()]);
                    
                    Mail::to($email)->send(new DeleteAccountRequestEmail($CODE, $email));
                    return response()->json(['message'=>'OTP send, please check e-mail','status' => 200]);
                }else{
                    return response()->json(['message'=>'Email does Not exist, Please Try Again','status' => 404]);
                }
            }
        }catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 'internal_server_error', 500);
        }
    }
    
    
    public function editAlbum(Request $request, $albumId)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'album_name' => 'required|string',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    return response()->json(['message' => $error, 'status' => 'failed', "error_type" => "",], 400);
                }
            }

            // Get the authenticated user's ID
            $user_id = Auth::id();

            // Find the album by its ID and user ID to ensure ownership
            $album = Album::where('id', $albumId)
                         ->where('user_id', $user_id)
                         ->first();

            // If album not found, return error response
            if (!$album) {
                return response()->json(['message' => 'Album not found or you do not have permission', 'status' => 'failed'], 404);
            }

            // Update album name
            $album->album_name = $request->album_name;
            $album->save();

            // Return success response with updated album data
            return response()->json(['message' => 'Album updated successfully', 'status' => 'success', "error_type" => "", 'album' => $album], 200);

        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    
    public function getNotificationList() {
        try {
            $user = Auth::user();
            //$user = User::find(787);
            
            
            $getHeaders = apache_request_headers();
            $timeZone = isset($getHeaders['Timezone']) ? $getHeaders['Timezone'] : 'UTC';
    
            $notis = Notification::where('receiver_id', $user->id)->where('has_actioned',0)
                                 ->orderBy('id', 'DESC')
                                 ->with('group')
                                 ->paginate(10);
         
            
            foreach ($notis as $noti) {
              
    
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
                        $getPost->is_following = FollowerUnfollwer::where([
                            'user_id' => $user->id,
                            'following_id' => $getPost->user->id
                        ])->exists();
                        $getPost->created_date = date('Y-m-d', strtotime($getPost->scheduling_post->created_at));
                        $getPost->posted_date = $getPost->scheduling_post->schedule_type == "now" 
                            ? date('Y-m-d', strtotime($getPost->scheduling_post->created_at)) 
                            : $getPost->scheduling_post->schedule_date;
                    }
                } elseif ($noti->type == "deceased") {
                    $markedUser = User::find($noti->marked_user_id); // Assuming marked_user_id exists in your Notification model
                    $burialInfo = BurialInfo::where('user_id', $noti->marked_user_id)->first();
                    // $data['user_name'] = trim(($markedUser->first_name ?? '') . ' ' . ($markedUser->last_name ?? '') ?? null);
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
    
                $getSender = User::where('id', $noti->sender_id)->first();
                if ($getSender) {
                    $noti->sender = $getSender;
                }
                
                $createdAt = Carbon::parse($noti->created_at)->timezone($timeZone);
                $noti->created_at = $createdAt->format('Y-m-d H:i:s');
                
                if($noti->type == "invite"){
                    $getData = ConnectionRequest::where(['user_id'=>$noti->receiver_id, 'sender_id'=>$noti->sender_id])->first();
                    if(!$getData){
                        $noti->is_connection_request = false;
                    }else{
                        $noti->is_connection_request = true;
                    }
                }
                
            }
            
            return $this->successResponse("Notification fetched successfully.", 200, $notis->items(), $notis);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 'internal_server_error', 500);
        }
    }
    
    
    public function countNotification(Request $request){
        try{
            $user = Auth::user();
            $count = Notification::where('receiver_id',$user->id)->where('isSeen','0')->count();
            if($request->isSeen){
                Notification::where('receiver_id', $user->id)->where('isSeen', '0')->update(['isSeen' => '1']);
                return $this->successResponse("Get counts notification Successfully.",200,null);
            }
            return $this->successResponse("Get counts notification Successfully.",200,$count);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 'internal_server_error', 500);
        }
    }

    public function deleteNotification(Request $request){
        try{
            $user = Auth::user();
            if($request->id){
                Notification::where('id',$request->id)->delete();
            }else{
                Notification::where('receiver_id',$user->id)->delete();
            }
            return $this->successResponse("Notification Deleted Successfully.",200,null);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 'internal_server_error', 500);
        }
    } 
    
    
    
    
    public function userLiveStatus(Request $request) {
        try {
            $validatedData = $request->validate([
                'user_id' => 'nullable|numeric|exists:users,id',
                'is_alive' => 'required|boolean',
                'deceased_by' => 'nullable|numeric',
                'alive_by' => 'nullable|numeric',
            ]);
    
            $user = auth()->user();
            
            if($request->type === "self"){
                if ($validatedData['is_alive']) {
                   UserLiveStatus::where(['user_id'=>$user->id,'deceased_by'=>$user->id])->delete();
                   return response()->json(['message' => 'User live successfully','status' => 'success', "error_type" => "",'data' => null,], 200);
                } else {
                    $liveStatus = new UserLiveStatus();
                    $liveStatus->user_id = $user->id;
                    $liveStatus->is_alive = $validatedData['is_alive'];
                    $liveStatus->deceased_by = $user->id;
                    $liveStatus->save();
                }
            }else{
                $liveStatus = new UserLiveStatus();
                $liveStatus->user_id = $validatedData['user_id'];
                $liveStatus->is_alive = $validatedData['is_alive'];
                
                if ($validatedData['is_alive']) {
                    $liveStatus->alive_by = $user->id;
                } else {
                    $liveStatus->deceased_by = $user->id;
                }
                
                $liveStatus->save();
                $liveStatus->refresh();
            }
            // Check if user is deceased and send notifications
            $data = [];
            $burialInfo = null;
            if (!$validatedData['is_alive']) {
                $deceasedById = $liveStatus->user_id;
                $deceasedUser = User::find($deceasedById);
                $burialInfo = BurialInfo::where('user_id', $deceasedById)->first(); 
                // $familyMembers = FamilyMember::where('user_id', $deceasedById)->get();
                $data['user_name'] = trim(($deceasedUser->first_name ?? '') . ' ' . ($deceasedUser->last_name ?? '') ?? null);
                $data['user_image'] = $deceasedUser->image ?? null;
                $data['user_id'] = $deceasedUser->id ?? null;
                $data['burialinfo'] = $burialInfo ? $burialInfo->toArray() : null;
                $type = 'self';
                if($request->type !== "self"){
                    $this->notifyMessage($user, $deceasedById, $data, $type, $deceasedUser,$deceasedById);
                }
            }
            $liveStatus = UserLiveStatus::find($liveStatus->id);
            return response()->json([
                'message' => 'User live status saved successfully',
                'status' => 'success',
                "error_type" => "",
                'data' => $liveStatus,
            ], 201);
    
        } catch (QueryException $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "error_type" => "", 'data' => []], 500);
    
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "error_type" => "", 'data' => []], 500);
        }
    }
    
    public function sendDeceasedNotifications()
    {
        $now = Carbon::now();
        // Fetch all users who are marked as deceased
        $deceasedUsers = UserLiveStatus::where(['is_alive' => false,'notify' => 0])->get();
        
        foreach ($deceasedUsers as $liveStatus) {

            $createdAt = $liveStatus->created_at;
            $nextNotificationTime = $createdAt->copy()->addHours(72);
        // $nextNotificationTime = $createdAt->copy()->addMinutes(2);
            if($now->greaterThanOrEqualTo($nextNotificationTime)){
                $deceasedUserId = $liveStatus->user_id;
                $deceasedUser = User::find($deceasedUserId);
                $burialInfo = BurialInfo::where('user_id', $deceasedUserId)->first();
                $deceasedByUser = User::find($liveStatus->deceased_by);
                
                $familyMembers = FamilyMember::where('user_id', $deceasedUserId)->orWhere('member_id', $deceasedUserId)->get();

                
                $getWhennPassPosts = Post::where('user_id',$deceasedUserId)->with('scheduling_post')->get();
                
                foreach($getWhennPassPosts as $post){
                    if($post->scheduling_post->schedule_type == 'when-pass'){
                        $post->scheduling_post->is_post = 1;
                        $post->scheduling_post->save();
                    } 
                }
                
                $data = [
                    'user_name' => trim(($deceasedUser->first_name ?? '') . ' ' . ($deceasedUser->last_name ?? '') ?? null),
                    'user_image' => $deceasedUser->image ?? null,
                    'user_id' => $deceasedUser->id ?? null,
                    'burial_info' => $burialInfo ? $burialInfo->toArray() : null,
                ];
        
                foreach ($familyMembers as $familyMember) {
                    // Initialize memberId to null
                    $memberId = null;
                
                    // Determine the memberId
                    if ($familyMember->user_id == $deceasedUserId) {
                        $memberId = $familyMember->member_id;
                    } else if ($familyMember->member_id == $deceasedUserId) {
                        $memberId = $familyMember->user_id;
                    }
                
                    // Proceed only if memberId is set and not equal to deceasedByUser->id
                    if ($memberId && $memberId != $deceasedByUser->id) {
                        try {
                            $this->notifyMessage($deceasedByUser, $memberId, $data, 'deceased', $deceasedUser, $deceasedUserId);
                            $getWhennPass = Post::where('user_id', $deceasedUserId)
                                        ->whereHas('scheduling_post', function ($query) {
                                            $query->where('is_post', 0)
                                                  ->where('schedule_type', 'when-pass');
                                        })->first();
                
                            if ($getWhennPass) {
                                $this->notifyMessage($deceasedByUser, $memberId, $data, 'when-pass', $deceasedUser, $deceasedUserId);
                            }
                        } catch (\Exception $e) {
                            \Log::error('Notification Error: ' . $e->getMessage());
                        }
                    }
                }
                
            $liveStatus->notify = 1;
            $liveStatus->save();
            }
        }
    }
    
    public function subscriptionCancelAfterExpireyDate(){
        try{
            $subscribedDatas = Subscription::where('platform','web')->get();
            foreach($subscribedDatas as $subscribedData){
                $expiryDate = $subscribedData->expiry_date;
                $getCurrentDate =  date('Y-m-d');
                if($getCurrentDate > $expiryDate){
                    $subscribedData->delete();
                }
            }
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "error_type" => "", 'data' => []], 500);
        }
    }
    
    
    public function freeAdsCancelAfterExpiryDate()
    {
        try {
            $currentDate = Carbon::now()->toDateString();
            $expiredAds = Advertisement::where('free_expiration_date', '<', $currentDate)->whereNotNull('free_expiration_date')->get();
    
            foreach ($expiredAds as $ad) {
                $ad->free_expiration_date = null;
                $ad->show_ads_status = 0;
                $ad->save();
            }
    
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'error_type' => 'Exception',  'data' => []], 500);
        }
    }

    
    public function cronCheckUserLive() {
         $this->sendDeceasedNotifications();
         $this->subscriptionCancelAfterExpireyDate();
         $this->freeAdsCancelAfterExpiryDate();
         $this->renewalAdsSubscription();
    }
    
    
    //-----------------------------------------------------------------------------------------------------------------






    // public function createFamoryTag(Request $request){
    //     try{
    //         $validator = Validator::make($request->all(), [
    //             'family_tag_id' => "required|regex:/^[0-9A-Za-z.\s,'-]*$/|size:7",
    //             'image' => 'required',
    //         ]);

    //         // Check if validation fails
    //         if ($validator->fails()) {
    //             $errors = $validator->errors();
    //             foreach ($errors->all() as $error) {
    //                 return response()->json(['message' => $error, 'status' => 'failed', "error_type" => "",], 400);
    //             }
    //         }
    //         $currentuser = Auth::user();

    //         $isExist = FamilyTagId::where("family_tag_id",$request->family_tag_id)->first();
    //         if($isExist){
    //             return $this->successResponse("Family Tag Id already exists",200, null);  
    //         }
    //         $saveTag = new FamilyTagId;
    //         $saveTag->family_tag_id = $request->family_tag_id;
    //         $saveTag->created_user_id = $currentuser->id; // Tag ID that created it
    //         $saveTag->user_id = $currentuser->id; // The created tag is assigned
    //         if($request->image){

    //             $file = $request->file('image');
    //             $res = $this->UploadImage->saveMedia($file,$currentuser->id);
    //             $saveTag->image = $res;

    //         }
    //         $saveTag->save();

    //         if(!$saveTag){
    //             return $this->successResponse("Famory Tag Id not save successfully",200, null);  
    //         }

    //         $saveTag = FamilyTagId::where("id",$saveTag->id)->first();
    //         return $this->successResponse("Famory Tag ID saved successfully",200, $saveTag);  


    //     } catch (\Exception $e) {
    //         return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "error_type" => "", 'data' => []], 500);
    //     }
    // }

    //get all users
    public function getAllUsers(Request $request)
{
    try {
        // Get the currently logged-in user ID
        $loggedInUserId = Auth::id();

        // Initialize query to fetch all users excluding the logged-in user
        $query = User::query()->where('id', '!=', $loggedInUserId);

        // Apply search filter if `search` parameter is provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Fetch all users excluding the logged-in user
        $allUsers = $query->get(['id', 'first_name', 'last_name', 'email']);

        return response()->json([
            'message' => 'Users fetched successfully.',
            'status' => 'success',
            'data' => $allUsers,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'status' => 'failed',
            'data' => [],
        ], 500);
    }
}

public function checkFamilyTag(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'family_tag_id' => ['required', 'regex:/^(AA[0-4][0-9]{4}|HA00[0-4][0-9]{3})$/'],
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'message' => $errors->first(),
                    'status' => 'failed',
                    'error_type' => 'validation_error'
                ], 400);
            }

            // Check if the family_tag_id already exists
            $isExist = FamilyTagId::where("family_tag_id", $request->family_tag_id)->exists();

            if ($isExist) {
                return response()->json([
                    'message' => 'Family Tag ID already exists',
                    'status' => 'success'
                ], 200);
            }

            return response()->json([
                'message' => 'Family Tag ID is available to be created',
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'failed',
                'error_type' => 'server_error',
                'data' => []
            ], 500);
        }
    }

    //lets try if it works
    public function createFamoryTag(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'family_tag_id' => ['required', 'regex:/^(AA[0-4][0-9]{4}|HA00[0-4][0-9]{3})$/'],
                'image' => 'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    return response()->json(['message' => $error, 'status' => 'failed', 'error_type' => 'validation_error'], 400);
                }
            }

            $currentuser = Auth::user();
            $isExist = FamilyTagId::where("family_tag_id", $request->family_tag_id)->first();

            if ($isExist) {
                return response()->json(['message' => 'Family Tag ID already exists', 'status' => 'failed'], 200);
            }

            $saveTag = new FamilyTagId;
            $saveTag->family_tag_id = $request->family_tag_id;
            $saveTag->created_user_id = $currentuser->id; // Tag ID that created it
            $saveTag->user_id = $currentuser->id; // The created tag is assigned

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $res = $this->UploadImage->saveMedia($file, $currentuser->id);
                $saveTag->image = $res;
            }

            $saveTag->save();

            if (!$saveTag) {
                return response()->json(['message' => 'Family Tag ID not saved successfully', 'status' => 'failed'], 200);
            }

            return response()->json(['message' => 'Family Tag ID saved successfully', 'status' => 'success', 'data' => $saveTag], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'error_type' => '', 'data' => []], 500);
        }
    }

    public function createFamoryTagV2(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'family_tag_id' => ['required', 'regex:/^(AA[0-4][0-9]{4}|HA00[0-4][0-9]{3})$/'],
                'image' => 'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    return response()->json(['message' => $error, 'status' => 'failed', 'error_type' => 'validation_error'], 400);
                }
            }

            $currentuser = Auth::user();
            $isExist = FamilyTagId::where("family_tag_id", $request->family_tag_id)->first();

            if ($isExist) {
                return response()->json(['message' => 'Family Tag ID already exists', 'status' => 'success'], 200);
            }

            $saveTag = new FamilyTagId;
            $saveTag->family_tag_id = $request->family_tag_id;
            $saveTag->created_user_id = $currentuser->id;
            $saveTag->user_id = $currentuser->id;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $res = $this->UploadImage->saveMedia($file, $currentuser->id);
                $saveTag->image = $res;
            }

            $saveTag->save();

            if (!$saveTag) {
                return response()->json(['message' => 'Family Tag ID not saved successfully', 'status' => 'failed'], 200);
            }

            return response()->json(['message' => 'Family Tag ID saved successfully', 'status' => 'success', 'data' => $saveTag], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'error_type' => '', 'data' => []], 500);
        }
    }

    public function createFamoryTagV3(Request $request)
    {
        try {
            // Validate input fields, making sure all except avatar are required
            $validator = Validator::make($request->all(), [
                'family_tag_id' => ['required', 'regex:/^(AA[0-4][0-9]{4}|HA00[0-4][0-9]{3})$/'],
                'image' => 'required|image', // Assuming image must be an actual image file
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:500',
                'privacy_type' => 'required|in:Public,Private', // Assuming 'privacy_type' can only be 'Public' or 'Private'
                'avatar' => 'nullable|image', // Avatar is optional
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    return response()->json(['message' => $error, 'status' => 'failed', 'error_type' => 'validation_error'], 400);
                }
            }

            $currentuser = Auth::user();
            $isExist = FamilyTagId::where("family_tag_id", $request->family_tag_id)->first();

            if ($isExist) {
                return response()->json(['message' => 'Family Tag ID already exists', 'status' => 'success'], 200);
            }

            // Create a new FamilyTagId entry
            $saveTag = new FamilyTagId;
            $saveTag->family_tag_id = $request->family_tag_id;
            $saveTag->created_user_id = $currentuser->id;
            $saveTag->user_id = $currentuser->id;
            $saveTag->title = $request->title;
            $saveTag->description = $request->description;
            $saveTag->privacy_type = $request->privacy_type;

            // Handle image upload if exists
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                // Ensure the file is being processed by the saveMedia method and its path is returned correctly
                $res = $this->UploadImage->saveMedia($file, $currentuser->id);
                $saveTag->image = $res;
            }

            // Handle avatar upload if exists
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                // Ensure the avatar file is processed correctly
                $avatarRes = $this->UploadImage->saveMedia($avatar, $currentuser->id);
                $saveTag->avatar = $avatarRes;
            }

            // Save the FamilyTagId
            $saveTag->save();

            if (!$saveTag) {
                return response()->json(['message' => 'Family Tag ID not saved successfully', 'status' => 'failed'], 200);
            }

            return response()->json(['message' => 'Family Tag ID saved successfully', 'status' => 'success', 'data' => $saveTag], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'error_type' => 'server_error', 'data' => []], 500);
        }
    }

	
	public function createFamoryTagWithPost(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate Family Tag and Post fields
            $validator = Validator::make($request->all(), [
                'family_tag_id' => ['required', 'regex:/^(AA[0-4][0-9]{4}|HA00[0-4][0-9]{3})$/'],
                'tag_image' => 'required|image', // Renamed for clarity
                'title' => 'required|string|max:255',
                'tag_description' => 'required|string|max:500', // Renamed for clarity
                'privacy_type' => 'required|in:Public,Private',
                'avatar' => 'nullable|image',

                // Post-specific fields
                'post_title' => 'required|string|max:255',
                'post_description' => 'required|string', // Unique name for post description
                'post_type' => 'required',
                'schedule_type' => 'required',
                'reoccurring_type' => 'required',
                'post_media' => 'nullable|file', // Renamed for clarity
                'media_type' => 'required|in:audio,video,picture,note',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 'failed',
                    'error_type' => 'validation_error'
                ], 400);
            }

            $currentUser = Auth::user();

            // Check if the Family Tag ID already exists
            $isExist = FamilyTagId::where("family_tag_id", $request->family_tag_id)->first();
            if ($isExist) {
                return response()->json(['message' => 'Family Tag ID already exists', 'status' => 'failed'], 400);
            }

            // Create Family Tag
            $saveTag = new FamilyTagId;
            $saveTag->family_tag_id = $request->family_tag_id;
            $saveTag->created_user_id = $currentUser->id;
            $saveTag->user_id = $currentUser->id;
            $saveTag->title = $request->title;
            $saveTag->description = $request->tag_description; // Updated field name
            $saveTag->privacy_type = $request->privacy_type;

            if ($request->hasFile('tag_image')) { // Updated field name
                $saveTag->image = $this->UploadImage->saveMedia($request->file('tag_image'), $currentUser->id);
            }

            if ($request->hasFile('avatar')) {
                $saveTag->avatar = $this->UploadImage->saveMedia($request->file('avatar'), $currentUser->id);
            }

            $saveTag->save();

            // Create Post
            $post = new Post;
            $post->tag_id = $saveTag->id; // Associate with created Family Tag
            $post->title = $request->post_title;
            $post->description = $request->post_description; // Updated field name
            $post->media_type = $request->media_type;

            if ($request->hasFile('post_media') && $request->file('post_media')->isValid()) { // Updated field name
                $file = $request->file('post_media');
                $post->file = $this->UploadImage->saveMedia($file, $currentUser->id);
            }

            $post->post_type = $request->post_type;
            $post->user_id = $currentUser->id;
            $post->save();

            // Handle scheduling
            $schedule = new SchedulingPost;
            $timezone = $request->header('time_zone', 'UTC');
            $scheduledDateTime = Carbon::parse($request->schedule_date . ' ' . $request->schedule_time, $timezone)->setTimezone('UTC');

            $schedule->post_id = $post->id;
            $schedule->timezone = $timezone;
            $schedule->schedule_type = $request->schedule_type;
            $schedule->is_post = ($request->schedule_type === 'now') ? 1 : 0;
            $schedule->schedule_date = $scheduledDateTime->toDateString();
            $schedule->schedule_time = $scheduledDateTime->toTimeString();
            $schedule->reoccurring_type = $request->reoccurring_type;

            if ($request->reoccurring_type === 'yes') {
                $schedule->reoccurring_time = $request->reoccurring_time;
            }

            $schedule->save();

            DB::commit();

            return response()->json([
                'message' => 'Family Tag and Post created successfully',
                'status' => 'success',
                'data' => [
                    'family_tag' => $saveTag,
                    'post' => $post
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'failed',
                'error_type' => 'server_error'
            ], 500);
        }
    }

    public function createTagWithCollaborators_ios(Request $request)
{
    DB::beginTransaction();

    try {
        // Set the timezone and current date/time
        $timezone = 'UTC';
        $currentDateTime = Carbon::now($timezone);

        // Validate input
        $validator = Validator::make($request->all(), [
            'family_tag_id' => ['required', 'regex:/^(AA[0-4][0-9]{4}|HA00[0-4][0-9]{3})$/'],
            'tag_image' => 'required|image',
            'title' => 'required|string|max:255',
            'tag_description' => 'required|string|max:500',
            'privacy_type' => 'required|in:Public,Private',
            'avatar' => 'nullable|image',
            'post_title' => 'required|string|max:255',
            'post_description' => 'required|string',
            'post_media' => 'nullable|file',
            'media_type' => 'required|in:audio,video,picture,note',
            'collaborators' => 'nullable|array',
            'collaborators.*.user_id' => 'nullable|exists:users,id',
            'collaborators.*.permissions_level' => 'nullable|in:view,add',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 'failed',
                'error_type' => 'validation_error'
            ], 400);
        }

        $currentUser = Auth::user();
        $current_user_id = $currentUser->id;

        // Check if the Family Tag ID already exists
        $isExist = FamilyTagId::where("family_tag_id", $request->family_tag_id)->first();
        if ($isExist) {
            return response()->json(['message' => 'Family Tag ID already exists', 'status' => 'failed'], 400);
        }

        // Create Family Tag
        $saveTag = new FamilyTagId;
        $saveTag->family_tag_id = $request->family_tag_id;
        $saveTag->created_user_id = $currentUser->id;
        $saveTag->user_id = $currentUser->id;
        $saveTag->title = $request->title;
        $saveTag->description = $request->tag_description;
        $saveTag->privacy_type = $request->privacy_type;

        // Upload Tag Image
        if ($request->hasFile('tag_image')) {
            $saveTag->image = $this->UploadImage->saveMedia($request->file('tag_image'), $currentUser->id);
        }

        // Upload Avatar Image
        if ($request->hasFile('avatar')) {
            $saveTag->avatar = $this->UploadImage->saveMedia($request->file('avatar'), $currentUser->id);
        }

        $saveTag->save();

        // Create Post
        $post = new Post;
        $post->tag_id = $saveTag->id;
        $post->title = $request->post_title;
        $post->description = $request->post_description;
        $post->media_type = $request->media_type;

        // Upload Post Media
        if ($request->hasFile('post_media') && $request->file('post_media')->isValid()) {
            $file = $request->file('post_media');
            $post->file = $this->UploadImage->saveMedia($file, $currentUser->id);
        }

        $post->post_type = 'Public';
        $post->user_id = $currentUser->id;
        $post->save();

        // Handle scheduling
        $schedule = new SchedulingPost;
        $schedule->post_id = $post->id;
        $schedule->timezone = $timezone;
        $schedule->schedule_type = 'now';
        $schedule->is_post = 1;
        $schedule->schedule_date = $currentDateTime->toDateString();
        $schedule->schedule_time = $currentDateTime->toTimeString();
        $schedule->reoccurring_type = 'no';

        $schedule->save();

        // Handle collaborators
        if ($request->has('collaborators')) {
            foreach ($request->collaborators as $collaborator) {
                if (!empty($collaborator['user_id']) && !empty($collaborator['permissions_level'])) {
                    TagCollaborator::create([
                        'family_tag_id' => $request->family_tag_id,
                        'user_id' => $collaborator['user_id'],
                        'invited_by' => $current_user_id,
                        'request_type' => 'invitation',
                        'permissions_level' => $collaborator['permissions_level'],
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Family Tag, Post, and Collaborators created successfully',
            'status' => 'success',
            'data' => [
                'family_tag' => $saveTag,
                'post' => $post
            ]
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => $e->getMessage(),
            'status' => 'failed',
            'error_type' => 'server_error'
        ], 500);
    }
}


public function createTagWithCollaborators(Request $request)
{
    $currentUser = Auth::user();
    $invited_by = $currentUser->id;
    $validator = Validator::make($request->all(), [
        'family_tag_id' => 'required|string',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'privacy_type' => 'required|in:Public,Private',
        'image' => 'nullable|file|mimes:jpg,jpeg,png',
        'media' => 'nullable|file',
        'media_type' => 'nullable|string|in:picture,note,video,audio',
        'collaborators' => 'nullable|array',
        'collaborators.*.user_id' => 'required_with:collaborators|exists:users,id',
        'collaborators.*.permissions_level' => 'required_with:collaborators|in:view,add',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'message' => $validator->errors()->first(),
            'data' => []
        ]);
    }

    try {
        // Step 1: Handle Tag Creation
        $familyTag = FamilyTagId::create([
            'family_tag_id' => $request->family_tag_id,
            'title' => $request->title,
            'description' => $request->description,
            'privacy_type' => $request->privacy_type,
            'image_path' => $request->file('image') ? $request->file('image')->store('images') : null,
            'media_path' => $request->file('media') ? $request->file('media')->store('media') : null,
            'media_type' => $request->media_type,
        ]);
        

        // Step 2: Add Collaborators
        if ($request->has('collaborators')) {
            foreach ($request->collaborators as $collaborator) {
                $collaborator_user_id = $collaborator['user_id'];
                TagCollaborator::create([
                    'family_tag_id' => $request->family_tag_id,
                    'user_id' => $collaborator_user_id,
                    'invited_by' => $invited_by,
                    'request_type' => 'invitation',
                    'permissions_level' => $collaborator['permissions_level'],
                ]);
            }
        }

        return response()->json([
            'status' => 201,
            'message' => 'Family Tag created successfully with collaborators.',
            'data' => $familyTag->load('collaborators') // Include collaborators in the response
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'An error occurred: ' . $e->getMessage(),
            'data' => []
        ]);
    }
}

    public function updateFamoryTag(Request $request, $family_tag_id)
    {
        try {
            // Validation for update fields
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'privacy_type' => 'sometimes|in:Public,Private',
                'image' => 'sometimes|image',
                'avatar' => 'sometimes|image',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    return response()->json(['message' => $error, 'status' => 'failed', 'error_type' => 'validation_error'], 400);
                }
            }

            $currentuser = Auth::user();

            // Retrieve the FamilyTagId instance by family_tag_id
            $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();
            if (!$familyTag) {
                return response()->json(['message' => 'Family Tag ID not found', 'status' => 'failed'], 404);
            }

            // Update only allowed fields
            if ($request->has('title'))
                $familyTag->title = $request->title;
            if ($request->has('description'))
                $familyTag->description = $request->description;
            if ($request->has('privacy_type'))
                $familyTag->privacy_type = $request->privacy_type;

            // Handle image upload if present
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $res = $this->UploadImage->saveMedia($file, $currentuser->id);
                $familyTag->image = $res;
            }

            // Handle avatar upload if present
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $res = $this->UploadImage->saveMedia($file, $currentuser->id);
                $familyTag->avatar = $res;
            }

            // Save updates
            $familyTag->save();

            return response()->json(['message' => 'Family Tag ID updated successfully', 'status' => 'success', 'data' => $familyTag], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'error_type' => '', 'data' => []], 500);
        }
    }

    public function createBuyNewTag(Request $request)
    {
        try {
            // Validate input fields
            $validator = Validator::make($request->all(), [
                'tag_id' => 'required|exists:family_tag_ids,family_tag_id',
                'buyer_user_id' => 'nullable|integer|exists:users,id',
                'buyer_user_email' => 'nullable|email',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    return response()->json(['message' => $error, 'status' => 'failed', 'error_type' => 'validation_error'], 400);
                }
            }

            // Ensure either buyer_user_id or buyer_user_email is provided
            if (empty($request->buyer_user_id) && empty($request->buyer_user_email)) {
                return response()->json(['message' => 'Either buyer_user_id or buyer_user_email must be provided', 'status' => 'failed'], 400);
            }

            // Check if a record with the same tag_id, buyer_user_id, and buyer_user_email already exists
            $existingTag = BuyNewTag::where('tag_id', $request->tag_id)
                ->where('buyer_user_id', $request->buyer_user_id)
                ->where('buyer_user_email', $request->buyer_user_email)
                ->first();

            if ($existingTag) {
                return response()->json(['message' => 'This Buy New Tag entry already exists', 'status' => 'failed'], 409);
            }

            // Create new BuyNewTag entry
            $buyNewTag = new BuyNewTag();
            $buyNewTag->tag_id = $request->tag_id;
            $buyNewTag->buyer_user_id = $request->buyer_user_id;
            $buyNewTag->buyer_user_email = $request->buyer_user_email;
            $buyNewTag->save();

            return response()->json(['message' => 'Buy New Tag entry created successfully', 'status' => 'success', 'data' => $buyNewTag], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'error_type' => 'server_error', 'data' => []], 500);
        }
    }




    public function createFamoryTagV2_old(Request $request)
    {
        try {
            // Validate the image field
            $validator = Validator::make($request->all(), [
                'image' => 'nullable',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    return response()->json(['message' => $error, 'status' => 'failed', "error_type" => ""], 400);
                }
            }

            $currentuser = Auth::user();

            // Extract family_tag_id and url from the scanned QR code result
            $decodedQR = json_decode($request->qr_code_result, true);
            $family_tag_id = isset($decodedQR['family_tag_id']) ? $decodedQR['family_tag_id'] : null;
            $url = isset($decodedQR['url']) ? $decodedQR['url'] : null; // Extracted but not stored

            // Validate the family_tag_id based on the specified series
            if (!preg_match('/^(AA[0-4][0-9]{4}|HA00[0-4][0-9]{3})$/', $family_tag_id)) {
                return response()->json(['message' => 'Invalid Family Tag ID format or out of valid range', 'status' => 'failed'], 400);
            }

            // Check if the Family Tag ID already exists
            $isExist = FamilyTagId::where("family_tag_id", $family_tag_id)->first();
            if ($isExist) {
                return $this->successResponse("Family Tag ID already exists", 200, null);
            }

            // Save the Family Tag ID
            $saveTag = new FamilyTagId;
            $saveTag->family_tag_id = $family_tag_id;
            $saveTag->created_user_id = $currentuser->id;
            $saveTag->user_id = $currentuser->id;

            // Handle the image
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $res = $this->UploadImage->saveMedia($file, $currentuser->id);
                $saveTag->image = $res;
            }

            $saveTag->save();

            if (!$saveTag) {
                return $this->successResponse("Family Tag ID not saved successfully", 200, null);
            }

            $saveTag = FamilyTagId::where("id", $saveTag->id)->first();
            return $this->successResponse("Family Tag ID saved successfully", 200, $saveTag);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "error_type" => "", 'data' => []], 500);
        }
    }


    /**
     * Function to extract the family tag ID and URL from the QR code result.
     */
    private function getFamilyTagIdFromQR($qrCodeResult)
    {
        // Assuming qrCodeResult is a JSON string with the family_tag_id and a URL
        $decodedData = json_decode($qrCodeResult, true);
        return [
            'family_tag_id' => $decodedData['family_tag_id'] ?? null,
            'url' => $decodedData['url'] ?? null, // Extract URL if available
        ];
    }


    public function getFamoryTag(Request $request) 
{
    try {
        $currentuser = Auth::user();

        // Ensure that the query is scoped to the current user
        $query = FamilyTagId::query()->where('user_id', $currentuser->id);

        // Apply the search condition if provided
        if ($request->search) {
            $query->where('family_tag_id', 'LIKE', '%' . $request->search . '%');
        }

        // Fetch and paginate the tags
        $getTag = $query->orderBy('id', 'DESC')->paginate(10);

        // Check if any tags were found
        if ($getTag->isEmpty()) {
            return $this->successResponse("No famory tag was found for the provided tag.", 200, []);
        }

        // Return the tags in the response
        return $this->successResponse(
            "Get all famory tag IDs successfully",
            200,
            $getTag->items(),
            $getTag
        );
    } catch (\Exception $e) {
        // Handle exceptions and return a structured error response
        return response()->json([
            'message' => $e->getMessage(),
            'status' => 'failed',
            "error_type" => "",
            'data' => []
        ], 500);
    }
}
    // public function getFamoryTagV2(Request $request)
    // {
    //     try {
    //         $currentuser = Auth::user();

    //         // Start building the query for FamilyTagId
    //         $query = FamilyTagId::query();

    //         // Apply search filter if provided
    //         if ($request->search) {
    //             $query->where('family_tag_id', 'LIKE', '%' . $request->search . '%');
    //         } else {
    //             $query->where('user_id', $currentuser->id); // Only fetch tags for the current user
    //         }

    //         // Paginate results
    //         $getTag = $query->orderBy('id', 'DESC')->paginate(10);

    //         // Check if there are no tags
    //         if ($getTag->isEmpty()) {
    //             return $this->successResponse("No famory tag was found for the provided tag.", 200, []);
    //         }

    //         // Get the count of collaborators for each tag
    //         $tagsWithCollaboratorsCount = $getTag->map(function ($tag) {
    //             // Get the count of accepted collaborators for each family_tag_id
    //             $collaboratorsCount = TagCollaborator::where('family_tag_id', $tag->family_tag_id)
    //                 ->where('status', 'accepted')
    //                 ->count();
    //             // Add the collaborators count to each tag
    //             $tag->collaborators_count = $collaboratorsCount;
    //             return $tag;
    //         });

    //         // Return the response with tags and collaborators count
    //         return $this->successResponse("Get all famory tag IDs successfully", 200, $tagsWithCollaboratorsCount, $getTag);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'status' => 'failed',
    //             "error_type" => "",
    //             'data' => []
    //         ], 500);
    //     }
    // }

    public function getFamoryTagV2(Request $request)
{
    try {
        $currentUser = Auth::user();

        // Start building the query, scoped to the current user
        $query = FamilyTagId::query()->where('user_id', $currentUser->id);

        // Apply search filter if provided
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('family_tag_id', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('title', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search . '%')
                    ->orWhereHas('creator', function ($q) use ($request) {
                        $q->where('first_name', 'LIKE', '%' . $request->search . '%')
                          ->orWhere('last_name', 'LIKE', '%' . $request->search . '%');
                    });
            });
        }

        // Paginate results
        $getTag = $query->orderBy('id', 'DESC')->paginate(10);

        // Check if there are no tags
        if ($getTag->isEmpty()) {
            return $this->successResponse("No famory tag was found for the provided tag.", 200, []);
        }

        // Add additional information (collaborators count, creator info) to each tag
        $tagsWithCollaboratorsCount = $getTag->map(function ($tag) {
            $creator = $tag->creator;

            $tag->creator_name = $creator ? $creator->first_name . ' ' . $creator->last_name : null;
            $tag->creator_avatar = $creator ? $creator->image : null;

            $collaboratorsCount = TagCollaborator::where('family_tag_id', $tag->family_tag_id)
                ->where('status', 'accepted')
                ->count();

            $tag->collaborators_count = $collaboratorsCount;

            return $tag;
        });

        // Return the response with tags and collaborators count
        return $this->successResponse("Get all famory tag IDs successfully", 200, $tagsWithCollaboratorsCount, $getTag);

    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'status' => 'failed',
            "error_type" => "",
            'data' => []
        ], 500);
    }
}


    
//     public function getTagInfo($family_tag_id)
// {
//     try {
//         // Step 1: Check if the family_tag_id exists
//         $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();

//         if (!$familyTag) {
//             return response()->json([
//                 'status' => 404,
//                 'message' => "No record found for the provided family_tag_id.",
//                 'data' => []
//             ]);
//         }

//         // Step 2: Get current user
//         $currentUser = Auth::user();

//         // Step 3: Handle public family tags
//         if ($familyTag->type === 'Public') {
//             $role = $familyTag->created_user_id == $currentUser->id ? 'owner' : 'requester';
//             return response()->json([
//                 'status' => 200,
//                 'message' => "Public family tag details retrieved successfully.",
//                 'data' => [
//                     'family_tag' => $familyTag,
//                     'role' => $role
//                 ]
//             ]);
//         }

//         // Step 4: Check collaborator record in tag_collaborators
//         $collaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
//             ->where('user_id', $currentUser->id)
//             ->first();
//             // dd($collaborator);

//         if (!$collaborator) {
//             return response()->json([
//                 'status' => 403,
//                 'message' => "You are not authorized to access this private family tag.",
//                 'data' => []
//             ]);
//         }

//         // Step 5: Verify collaboration status
//         if ($collaborator->status !== 'accepted') {
//             return response()->json([
//                 'status' => 403,
//                 'message' => "Your collaboration request has not been accepted yet.",
//                 'data' => []
//             ]);
//         }

//         // Step 6: Verify permissions level
//         $requiredPermission = 'add'; // Change this to the required action (e.g., 'add', 'view')
//         if ($collaborator->permissions_level !== $requiredPermission) {
//             return response()->json([
//                 'status' => 403,
//                 'message' => "You do not have the required permissions to perform this action. You can only view posts.",
//                 'data' => []
//             ]);
//         }

//         // Step 7: Return family tag details with role
//         $role = $familyTag->created_user_id == $currentUser->id ? 'owner' : 'requester';
//         return response()->json([
//             'status' => 200,
//             'message' => "Private family tag details retrieved successfully.",
//             'data' => [
//                 'family_tag' => $familyTag,
//                 'role' => $role,
//                 'permissions_level' => $collaborator->permissions_level
//             ]
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 500,
//             'message' => $e->getMessage(),
//             'data' => []
//         ]);
//     }
// }


// public function getTagInfo($family_tag_id, Request $request)
// {
//     try {
//         // Step 1: Check if the family_tag_id exists
//         $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();

//         if (!$familyTag) {
//             return response()->json([
//                 'status' => 404,
//                 'message' => "No record found for the provided family_tag_id.",
//                 'data' => [
//                     'my_permissions_level' => 'forbidden'
//                 ]
//             ]);
//         }

//         // Step 2: Get the current user
//         $currentUser = Auth::user();

//         // Step 3: Check if the user is the owner
//         if ($familyTag->created_user_id == $currentUser->id) {
//             return response()->json([
//                 'status' => 200,
//                 'message' => "Tag information retrieved successfully.",
//                 'data' => [
//                     'family_tag' => $familyTag,
//                     'my_permissions_level' => 'owner',
//                     'tag_status' => 'saved',
//                     'created_at' => $familyTag->created_at,
//                     'updated_at' => $familyTag->updated_at,
//                     'deleted_at' => $familyTag->deleted_at
//                 ]
//             ]);
//         }

//         // Step 4: Handle public tags
//         if ($familyTag->type === 'Public') {
//             return response()->json([
//                 'status' => 200,
//                 'message' => "Public tag information retrieved successfully.",
//                 'data' => [
//                     'family_tag' => $familyTag,
//                     'my_permissions_level' => 'view',
//                     'tag_status' => 'unsaved',
//                     'created_at' => $familyTag->created_at,
//                     'updated_at' => $familyTag->updated_at,
//                     'deleted_at' => $familyTag->deleted_at
//                 ]
//             ]);
//         }

//         // Step 5: Handle private tags and check for collaboration access
//         $collaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
//             ->where('user_id', $currentUser->id)
//             ->first();

//         if (!$collaborator) {
//             return response()->json([
//                 'status' => 403,
//                 'message' => "You are not authorized to access this private tag.",
//                 'data' => [
//                     'my_permissions_level' => 'forbidden',
//                     'tag_status' => 'private'
//                 ]
//             ]);
//         }

//         // Step 6: Determine permissions level from the ENUM field
//         $permissionLevel = $collaborator->permissions_level;

//         // Map ENUM values to `my_permissions_level`
//         switch ($permissionLevel) {
//             case 'add':
//                 $myPermissionsLevel = 'contribute';
//                 break;
//             case 'view':
//                 $myPermissionsLevel = 'view';
//                 break;
//             default:
//                 $myPermissionsLevel = 'forbidden';
//         }

//         // Step 7: Return response for private tags with permissions
//         return response()->json([
//             'status' => 200,
//             'message' => "Private tag information retrieved successfully.",
//             'data' => [
//                 'family_tag' => $familyTag,
//                 'my_permissions_level' => $myPermissionsLevel,
//                 'tag_status' => 'private',
//                 'created_at' => $familyTag->created_at,
//                 'updated_at' => $familyTag->updated_at,
//                 'deleted_at' => $familyTag->deleted_at
//             ]
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 500,
//             'message' => $e->getMessage(),
//             'data' => []
//         ]);
//     }
// }

// public function getTagInfo($family_tag_id, Request $request)
// {
//     try {
//         // Step 1: Check if the family_tag_id exists
//         $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();

//         if (!$familyTag) {
//             return response()->json([
//                 'status' => 404,
//                 'message' => "No record found for the provided family_tag_id.",
//                 'data' => [
//                     'my_permissions_level' => 'forbidden'
//                 ]
//             ]);
//         }

//         // Step 2: Get the current user
//         $currentUser = Auth::user();

//         // Step 3: Check if the user is the owner
//         if ($familyTag->created_user_id == $currentUser->id) {
//             return response()->json([
//                 'status' => 200,
//                 'message' => "Tag information retrieved successfully.",
//                 'data' => [
//                     'family_tag' => $familyTag,
//                     'my_permissions_level' => 'owner',
//                     'tag_status' => 'saved',
//                     'created_at' => $familyTag->created_at,
//                     'updated_at' => $familyTag->updated_at,
//                     'deleted_at' => $familyTag->deleted_at
//                 ]
//             ]);
//         }

//         // Step 4: Handle public tags
//         if ($familyTag->type === 'Public') {
//             return response()->json([
//                 'status' => 200,
//                 'message' => "Public tag information retrieved successfully.",
//                 'data' => [
//                     'family_tag' => $familyTag,
//                     'my_permissions_level' => 'view',
//                     'tag_status' => 'unsaved',
//                     'created_at' => $familyTag->created_at,
//                     'updated_at' => $familyTag->updated_at,
//                     'deleted_at' => $familyTag->deleted_at
//                 ]
//             ]);
//         }

//         // Step 5: Handle private tags and check for collaboration access
//         $collaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
//             ->where('user_id', $currentUser->id)
//             ->first();

//         if (!$collaborator) {
//             return response()->json([
//                 'status' => 403,
//                 'message' => "You are not authorized to access this private tag.",
//                 'data' => [
//                     'my_permissions_level' => 'forbidden',
//                     'tag_status' => 'private'
//                 ]
//             ]);
//         }

//         // Step 6: Determine permissions level from the ENUM field
//         $permissionLevel = $collaborator->permissions_level;
//         $myPermissionsLevel = $permissionLevel === 'add' ? 'contribute' : ($permissionLevel === 'view' ? 'view' : 'forbidden');

//         // Step 7: Include collaborator details for "contribute"
//         $collaboratorDetails = null;
//         if ($myPermissionsLevel === 'contribute') {
//             $user = \App\Models\User::find($collaborator->user_id);
//             if ($user) {
//                 $collaboratorDetails = [
//                     'collaborator_name' => $user->first_name . ' ' . $user->last_name,
//                     'avatar' => $user->avatar,
//                 ];
//             }
//         }

//         // Step 8: Return response for private tags
//         return response()->json([
//             'status' => 200,
//             'message' => "Private tag information retrieved successfully.",
//             'data' => [
//                 'family_tag' => $familyTag,
//                 'my_permissions_level' => $myPermissionsLevel,
//                 'collaborator_details' => $collaboratorDetails,
//                 'tag_status' => 'private',
//                 'created_at' => $familyTag->created_at,
//                 'updated_at' => $familyTag->updated_at,
//                 'deleted_at' => $familyTag->deleted_at
//             ]
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 500,
//             'message' => $e->getMessage(),
//             'data' => []
//         ]);
//     }
// }

// public function getTagInfo($family_tag_id, Request $request)
// {
//     try {
//         // Step 1: Check if the family_tag_id exists
//         $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();

//         if (!$familyTag) {
//             return response()->json([
//                 'status' => 404,
//                 'message' => "No record found for the provided family_tag_id.",
//                 'data' => [
//                     'family_tag' => null,
//                     'creator_name' => null,
//                     'my_permissions_level' => 'forbidden',
//                     'tag_status' => 'not found',
//                 ]
//             ]);
//         }

//         // Step 2: Get creator's details
//         $owner = User::find($familyTag->created_user_id);
//         $creatorInfo = [
//             'creator_name' => $owner ? $owner->first_name . ' ' . $owner->last_name : null,
//             'creator_avatar' => $owner ? $owner->avatar : null,
//         ];

//         // Step 3: Define base family_tag details
//         $familyTagDetails = [
//             "id" => $familyTag->id,
//             "created_user_id" => $familyTag->created_user_id,
//             "family_tag_id" => $familyTag->family_tag_id,
//             "user_id" => $familyTag->user_id,
//             "image" => $familyTag->image,
//             "title" => $familyTag->title,
//             "description" => $familyTag->description,
//             "privacy_type" => $familyTag->privacy_type,
//             "avatar" => $familyTag->avatar,
//             "created_at" => $familyTag->created_at,
//             "updated_at" => $familyTag->updated_at,
//             "deleted_at" => $familyTag->deleted_at,
//         ];

//         // Step 4: Get the current user
//         $currentUser = Auth::user();

//         // Step 5: Check if the user is the owner
//         if ($familyTag->created_user_id == $currentUser->id) {
//             return response()->json([
//                 'status' => 200,
//                 'message' => "Tag information retrieved successfully.",
//                 'data' => array_merge($familyTagDetails, $creatorInfo, [
//                     'my_permissions_level' => 'owner',
//                     'tag_status' => 'saved',
//                 ])
//             ]);
//         }

//         // Step 6: Check if the user is a collaborator
//         $collaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
//             ->where('user_id', $currentUser->id)
//             ->first();

//         if ($collaborator) {
//             $invitedBy = User::find($collaborator->invited_by);
//             $collaboratorInfo = [
//                 'collaborator_permissions_level' => $collaborator->permissions_level,
//                 'invited_by' => $invitedBy ? $invitedBy->first_name . ' ' . $invitedBy->last_name : null,
//                 'invited_by_avatar' => $invitedBy ? $invitedBy->avatar : null,
//             ];

//             $myPermissionsLevel = $collaborator->permissions_level === 'add' ? 'contribute' : 'view';

//             return response()->json([
//                 'status' => 200,
//                 'message' => "Collaborator tag information retrieved successfully.",
//                 'data' => array_merge($familyTagDetails, $creatorInfo, $collaboratorInfo, [
//                     'my_permissions_level' => $myPermissionsLevel,
//                     'tag_status' => 'private',
//                 ])
//             ]);
//         }

//         // Step 7: If the user is neither the owner nor a collaborator, forbid access
//         return response()->json([
//             'status' => 200,
//             'message' => "You are not authorized to access this private tag.",
//             'data' => array_merge($familyTagDetails, $creatorInfo, [
//                 'my_permissions_level' => 'forbidden',
//                 'tag_status' => 'private',
//             ])
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'status' => 500,
//             'message' => $e->getMessage(),
//             'data' => []
//         ]);
//     }
// }

public function getTagInfo($family_tag_id, Request $request)
{
    try {
        // Step 1: Check if the family_tag_id exists
        $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();

        // Default response structure with all required fields
        $defaultResponse = [
            "id" => null,
            "created_user_id" => null,
            "family_tag_id" => null,
            "user_id" => null,
            "image" => null,
            "title" => null,
            "description" => null,
            "privacy_type" => "forbidden",
            "avatar" => null,
            "creator_name" => null,
            "my_permissions_level" => "forbidden",
            "request_pending" => false,
            "tag_status" => "not found",
            "created_at" => null,
            "updated_at" => null,
            "deleted_at" => null,
        ];

        if (!$familyTag) {
            return response()->json([
                'status' => 404,
                'message' => "No record found for the provided family_tag_id.",
                'data' => $defaultResponse
            ]);
        }

        // Step 2: Get creator's details
        $owner = User::find($familyTag->created_user_id);
        $creatorInfo = [
            "creator_name" => $owner ? $owner->first_name . ' ' . $owner->last_name : null,
            "avatar" => $owner ? $owner->avatar : null,
        ];

        // Step 3: Define family_tag details
        $familyTagDetails = [
            "id" => $familyTag->id,
            "created_user_id" => $familyTag->created_user_id,
            "family_tag_id" => $familyTag->family_tag_id,
            "user_id" => $familyTag->user_id,
            "image" => $familyTag->image,
            "title" => $familyTag->title,
            "description" => $familyTag->description,
            "privacy_type" => $familyTag->privacy_type,
            "created_at" => $familyTag->created_at,
            "updated_at" => $familyTag->updated_at,
            "deleted_at" => $familyTag->deleted_at,
        ];

        // Step 4: Merge family_tag details with default response
        $responseData = array_merge($defaultResponse, $familyTagDetails, $creatorInfo);

        // Step 5: Get the current user
        $currentUser = Auth::user();

        // Check if privacy_type is public
        if ($familyTag->privacy_type === "public") {
            $responseData["my_permissions_level"] = "view";
            $responseData["request_pending"] = false;

            return response()->json([
                'status' => 200,
                'message' => "Public tag information retrieved successfully.",
                'data' => $responseData
            ]);
        }

        // Check ownership
        if ($familyTag->created_user_id == $currentUser->id) {
            $responseData["my_permissions_level"] = "owner";
            $responseData["request_pending"] = false;

            return response()->json([
                'status' => 200,
                'message' => "Tag information retrieved successfully.",
                'data' => $responseData
            ]);
        }

        // Check if the user is a collaborator (only for private tags)
        $collaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
            ->where('user_id', $currentUser->id)
            ->first();

        if ($collaborator) {
            $responseData["request_pending"] = $collaborator->status === 'pending';
            $responseData["my_permissions_level"] = $responseData["request_pending"]
                ? "forbidden"
                : $collaborator->permissions_level;

            return response()->json([
                'status' => 200,
                'message' => "Collaborator tag information retrieved successfully.",
                'data' => $responseData
            ]);
        }

        // Default case for private tags if the user is not a collaborator
        $responseData["my_permissions_level"] = "forbidden";
        $responseData["request_pending"] = false;

        return response()->json([
            'status' => 403,
            'message' => "You are not authorized to access this private tag.",
            'data' => $responseData
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => $e->getMessage(),
            'data' => []
        ]);
    }
}





    public function getFamoryTagWithSearchV2(Request $request)
    {
        try {
            $currentuser = Auth::user();

            // Start building the query for FamilyTagId
            $query = FamilyTagId::query();

            // Apply search filter if provided
            if ($request->search) {
                $searchKeyword = $request->search;
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('family_tag_id', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('title', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('description', 'LIKE', '%' . $searchKeyword . '%')
                        ->orWhere('privacy_type', 'LIKE', '%' . $searchKeyword . '%');
                });
            } else {
                $query->where('user_id', $currentuser->id); // Only fetch tags for the current user
            }

            // Paginate results
            $getTag = $query->orderBy('id', 'DESC')->paginate(10);

            // Check if there are no tags
            if ($getTag->isEmpty()) {
                return $this->successResponse("No famory tag was found for the provided tag.", 200, []);
            }

            // Get the count of collaborators for each tag
            $tagsWithCollaboratorsCount = $getTag->map(function ($tag) {
                // Get the count of accepted collaborators for each family_tag_id
                $collaboratorsCount = TagCollaborator::where('family_tag_id', $tag->family_tag_id)
                    ->where('status', 'accepted')
                    ->count();
                // Add the collaborators count to each tag
                $tag->collaborators_count = $collaboratorsCount;
                return $tag;
            });

            // Return the response with tags and collaborators count
            return $this->successResponse("Get all famory tag IDs successfully", 200, $tagsWithCollaboratorsCount, $getTag);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'failed',
                "error_type" => "",
                'data' => []
            ], 500);
        }
    }

    public function getFamoryTagFirstFive(Request $request)
    {
        try {
            $currentuser = Auth::user();

            $query = FamilyTagId::query();

            if ($request->search) {
                $query->where('family_tag_id', 'LIKE', '%' . $request->search . '%');
            } else {
                $query->where('user_id', $currentuser->id);
            }

            // Limit to first 5 records
            $getTag = $query->orderBy('id', 'DESC')->take(5)->get();

            if ($getTag->isEmpty()) {
                return $this->successResponse("No famory tag was found for the provided tag.", 200, []);
            }

            return $this->successResponse("Get first 5 famory tag IDs successfully", 200, $getTag);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "error_type" => "", 'data' => []], 500);
        }
    }


    // public function getCollaborators($family_tag_id)
    // {
    //     try {
    //         // Define per_page for pagination
    //         $perPage = 10;

    //         // Fetch collaborators based on the family_tag_id with pagination
    //         $collaboratorsQuery = TagCollaborator::where('family_tag_id', $family_tag_id);

    //         // Paginate the results
    //         $collaborators = $collaboratorsQuery->paginate($perPage);

    //         // If no collaborators are found
    //         if ($collaborators->isEmpty()) {
    //             return response()->json([
    //                 'message' => 'No collaborators found for this tag.',
    //                 'status' => 'success',
    //                 'error_type' => '',
    //                 'data' => [],
    //                 'total_records' => 0,
    //                 'total_pages' => 0,
    //                 'current_page' => 1,
    //                 'per_page' => $perPage,
    //             ], 200);
    //         }

    //         // Return the formatted response
    //         return response()->json([
    //             'message' => 'Get all family tag collaborators successfully.',
    //             'status' => 'success',
    //             'error_type' => '',
    //             'data' => $collaborators->items(), // Get the current page data
    //             'total_records' => $collaborators->total(), // Total records available
    //             'total_pages' => $collaborators->lastPage(), // Total number of pages
    //             'current_page' => $collaborators->currentPage(), // Current page
    //             'per_page' => $perPage, // Number of items per page
    //         ], 200);

    //     } catch (\Exception $e) {
    //         // Handle the exception and return an error response
    //         return response()->json([
    //             'message' => 'An error occurred while fetching collaborators.',
    //             'status' => 'failed',
    //             'error_type' => $e->getMessage(),
    //             'data' => [],
    //             'total_records' => 0,
    //             'total_pages' => 0,
    //             'current_page' => 1,
    //             'per_page' => 10,
    //         ], 500);
    //     }
    // }

    public function getCollaborators($family_tag_id)
    {
        try {
            // Define per_page for pagination
            $perPage = 10;

            // Fetch collaborators based on the family_tag_id with pagination
            $collaboratorsQuery = TagCollaborator::where('family_tag_id', $family_tag_id);

            // Paginate the results
            $collaborators = $collaboratorsQuery->paginate($perPage);

            // If no collaborators are found
            if ($collaborators->isEmpty()) {
                return response()->json([
                    'message' => 'No collaborators found for this tag.',
                    'status' => 'success',
                    'error_type' => '',
                    'data' => [],
                    'total_records' => 0,
                    'total_pages' => 0,
                    'current_page' => 1,
                    'per_page' => $perPage,
                ], 200);
            }

            // Map collaborators to include collaborator_name and avatar
            $collaboratorsData = $collaborators->items(); // Get the current page data
            $collaboratorsData = collect($collaboratorsData)->map(function ($collaborator) {
                // Fetch the user's full name and avatar using the user_id from the TagCollaborator table
                $user = \App\Models\User::find($collaborator->user_id);

                // Check if the user exists, and append the full name and avatar
                if ($user) {
                    $collaborator->collaborator_name = $user->first_name . ' ' . $user->last_name;

                } else {
                    $collaborator->collaborator_name = null;  // In case the user does not exist
                }

                // Remove the user_id from the response as we are now using collaborator_name
                unset($collaborator->user_id);

                return $collaborator;
            });

            // Return the formatted response
            return response()->json([
                'message' => 'Get all family tag collaborators successfully.',
                'status' => 'success',
                'error_type' => '',
                'data' => $collaboratorsData, // Return the collaborators with full names and avatars
                'total_records' => $collaborators->total(), // Total records available
                'total_pages' => $collaborators->lastPage(), // Total number of pages
                'current_page' => $collaborators->currentPage(), // Current page
                'per_page' => $perPage, // Number of items per page
            ], 200);

        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return response()->json([
                'message' => 'An error occurred while fetching collaborators.',
                'status' => 'failed',
                'error_type' => $e->getMessage(),
                'data' => [],
                'total_records' => 0,
                'total_pages' => 0,
                'current_page' => 1,
                'per_page' => 10,
            ], 500);
        }
    }


    // public function getFirstFiveCollaborators($family_tag_id)
    // {
    //     try {
    //         // Define the limit
    //         $limit = 5;

    //         // Fetch the first 5 collaborators based on the family_tag_id
    //         $collaborators = TagCollaborator::where('family_tag_id', $family_tag_id)
    //             ->limit($limit)
    //             ->get();

    //         // If no collaborators are found
    //         if ($collaborators->isEmpty()) {
    //             return response()->json([
    //                 'message' => 'No collaborators found for this tag.',
    //                 'status' => 'success',
    //                 'error_type' => '',
    //                 'data' => [],
    //                 'total_records' => 0,
    //             ], 200);
    //         }

    //         // Return the formatted response
    //         return response()->json([
    //             'message' => 'Get first five collaborators successfully.',
    //             'status' => 'success',
    //             'error_type' => '',
    //             'data' => $collaborators, // Return the collaborators
    //             'total_records' => $collaborators->count(), // Total records fetched
    //         ], 200);

    //     } catch (\Exception $e) {
    //         // Handle the exception and return an error response
    //         return response()->json([
    //             'message' => 'An error occurred while fetching collaborators.',
    //             'status' => 'failed',
    //             'error_type' => $e->getMessage(),
    //             'data' => [],
    //             'total_records' => 0,
    //         ], 500);
    //     }
    // }
    // public function getFirstFiveCollaborators($family_tag_id)
    // {
    //     try {
    //         // Define the limit
    //         $limit = 5;

    //         // Fetch the first 5 collaborators based on the family_tag_id with user's full name
    //         $collaborators = TagCollaborator::where('family_tag_id', $family_tag_id)
    //             ->limit($limit)
    //             ->get()
    //             ->map(function ($collaborator) {
    //                 // Fetch the user's full name using the user_id from the TagCollaborator table
    //                 $user = \App\Models\User::find($collaborator->user_id);

    //                 // Check if the user exists, and append the full name
    //                 if ($user) {
    //                     $collaborator->collaborator_name = $user->first_name . ' ' . $user->last_name;
    //                 } else {
    //                     $collaborator->collaborator_name = null;  // In case the user does not exist
    //                 }

    //                 // Remove the user_id from the response as we are now using collaborator_name
    //                 unset($collaborator->user_id);

    //                 return $collaborator;
    //             });

    //         // If no collaborators are found
    //         if ($collaborators->isEmpty()) {
    //             return response()->json([
    //                 'message' => 'No collaborators found for this tag.',
    //                 'status' => 'success',
    //                 'error_type' => '',
    //                 'data' => [],
    //                 'total_records' => 0,
    //             ], 200);
    //         }

    //         // Return the formatted response
    //         return response()->json([
    //             'message' => 'Get first five collaborators successfully.',
    //             'status' => 'success',
    //             'error_type' => '',
    //             'data' => $collaborators, // Return the collaborators with full names
    //             'total_records' => $collaborators->count(), // Total records fetched
    //         ], 200);

    //     } catch (\Exception $e) {
    //         // Handle the exception and return an error response
    //         return response()->json([
    //             'message' => 'An error occurred while fetching collaborators.',
    //             'status' => 'failed',
    //             'error_type' => $e->getMessage(),
    //             'data' => [],
    //             'total_records' => 0,
    //         ], 500);
    //     }
    // }
    public function getFirstFiveCollaborators($family_tag_id)
{
    try {
        // Define the limit
        $limit = 5;

        // Fetch the first 5 collaborators along with the family tag details
        $collaborators = TagCollaborator::where('family_tag_id', $family_tag_id)
            ->limit($limit)
            ->get()
            ->map(function ($collaborator) {
                // Fetch the user's full name and avatar using the user_id from the TagCollaborator table
                $user = \App\Models\User::find($collaborator->user_id);

                // Check if the user exists, and append the full name and avatar
                if ($user) {
                    $collaborator->collaborator_name = $user->first_name . ' ' . $user->last_name;
                    $collaborator->avatar = $user->avatar; // Add avatar to the collaborator
                } else {
                    $collaborator->collaborator_name = null; // In case the user does not exist
                    $collaborator->avatar = null; // If no user, set avatar as null
                }

                // Remove the user_id from the response as we are now using collaborator_name
                unset($collaborator->user_id);

                return $collaborator;
            });

        // Fetch family tag details
        $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();
// dd($familyTag->title, $familyTag->description);
        // If no collaborators or family tag are found
        if ($collaborators->isEmpty() || !$familyTag) {
            return response()->json([
                'message' => 'No collaborators found for this tag or tag does not exist.',
                'status' => 'success',
                'error_type' => '',
                'data' => [],
                'total_records' => 0,
                'title' => $familyTag ? $familyTag->title : null, // Include title if available
                'description' => $familyTag ? $familyTag->description : null, // Include description if available
            ], 200);
        }

        // Return the formatted response with family tag details
        return response()->json([
            'message' => 'Get first five collaborators successfully.',
            'status' => 'success',
            'error_type' => '',
            'data' => $collaborators, // Return the collaborators with full names and avatars
            'total_records' => $collaborators->count(), // Total records fetched
            'title' => $familyTag->title, // Include the title
            'description' => $familyTag->description, // Include the description
        ], 200);
    } catch (\Exception $e) {
        // Handle the exception and return an error response
        return response()->json([
            'message' => 'An error occurred while fetching collaborators.',
            'status' => 'failed',
            'error_type' => $e->getMessage(),
            'data' => [],
            'total_records' => 0,
            'title' => null,
            'description' => null,
        ], 500);
    }
}




    // public function inviteCollaborator(Request $request, $family_tag_id)
    // {
    //     try {
    //         // Validate the input
    //         $validated = $request->validate([
    //             'user_id' => 'required|exists:users,id',
    //             'permissions_level' => 'required|in:view,add,edit', // Ensure permissions_level is one of the allowed values
    //         ]);

    //         // Check if the collaborator already exists
    //         $existingCollaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
    //             ->where('user_id', $request->user_id)
    //             ->where('invited_by', Auth::id())
    //             ->first();

    //         if ($existingCollaborator) {
    //             return response()->json(['message' => 'This collaborator already exists for this tag.'], 409);
    //         }

    //         // Create a new TagCollaborator record
    //         $collaborator = new TagCollaborator();
    //         $collaborator->family_tag_id = $family_tag_id;
    //         $collaborator->user_id = $request->user_id;
    //         $collaborator->invited_by = Auth::id(); // The user inviting the collaborator
    //         $collaborator->status = 'pending'; // Default status when invited

    //         // Store the permissions level
    //         $collaborator->permissions_level = $request->permissions_level; // Store the permissions_level as a string (view, add, or edit)

    //         $collaborator->save();

    //         return response()->json(['message' => 'Collaborator invited successfully.'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
    //     }
    // }

    public function inviteCollaborator(Request $request, $family_tag_id)
{
    try {
        // Check if the $family_tag_id exists in the `family_tag_ids` table
        $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();

        if (!$familyTag) {
            return response()->json([
                'message' => 'The specified family tag ID does not exist.',
                'status' => 'failed',
                'data' => [],
            ], 404);
        }

        // Validate the input
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id', // User ID is optional but must exist if provided
            'email' => 'nullable|email|unique:tag_collaborators,email', // Email is optional but must be valid and unique
            'permissions_level' => 'required|in:view,add,edit', // Ensure permissions_level is one of the allowed values
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp,svg,webp|max:30048', // Validation for avatar image (optional)
        ]);

        if (empty($request->user_id) && empty($request->email)) {
            return response()->json([
                'message' => 'Either user_id or email must be provided.',
                'status' => 'failed',
                'data' => [],
            ], 400);
        }

        // Check if the user exists; if not, send an invitation email
        if (!$request->user_id) {
            $email = $request->email;

            // Send the email
            Mail::raw("You have been invited to join Famory App. Click the link below to register and collaborate:
                \n\n https://famoryapp.com/register", function ($message) use ($email) {
                $message->to($email)
                        ->subject("You're Invited to Famory App");
            });

            return response()->json([
                'message' => 'Invitation email sent successfully.',
                'status' => 'success',
                'data' => [],
            ], 200);
        }

        // Check if the collaborator already exists
        $existingCollaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
            ->where(function ($query) use ($request) {
                $query->where('user_id', $request->user_id)
                    ->orWhere('email', $request->email);
            })
            ->where('invited_by', Auth::id())
            ->first();

        if ($existingCollaborator) {
            return response()->json(['message' => 'This collaborator already exists for this tag.'], 409);
        }

        // Create a new TagCollaborator record
        $collaborator = new TagCollaborator();
        $collaborator->family_tag_id = $family_tag_id;
        $collaborator->user_id = $request->user_id; // May be null if email is used
        $collaborator->email = $request->email; // Save email if provided
        $collaborator->invited_by = Auth::id(); // The user inviting the collaborator
        $collaborator->status = 'pending'; // Default status when invited
        $collaborator->permissions_level = $request->permissions_level; // Store the permissions_level as a string (view, add, or edit)

        // Handle avatar upload (if provided)
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            // Generate a unique name for the avatar
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            // Store the avatar in the 'public/avatars' folder
            $avatar->move(public_path('avatars'), $avatarName);

            // Save the full avatar URL in the database
            $collaborator->avatar = 'https://admin.famoryapp.com/public/avatars/' . $avatarName; // Store full URL
        }

        $collaborator->save();

        return response()->json(['message' => 'Collaborator invited successfully.'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
    }
}



    public function inviteMultipleCollaborators(Request $request, $family_tag_id)
    {
        try {
            // Check if the $family_tag_id exists in the `family_tag_ids` table
            $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();

            if (!$familyTag) {
                return response()->json([
                    'message' => 'The specified family tag ID does not exist.',
                    'status' => 'failed',
                    'data' => [],
                ], 404);
            }

            // Validate the input
            $validated = $request->validate([
                'collaborators' => 'required|array|min:1',
                'collaborators.*.user_id' => 'required|exists:users,id',
                'collaborators.*.permissions_level' => 'required|in:view,add,edit',
                'collaborators.*.avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,bmp,svg,webp|max:30048',
            ]);

            $results = []; // To store individual user invitation results

            foreach ($request->collaborators as $collaboratorData) {
                $user_id = $collaboratorData['user_id'];
                $permissions_level = $collaboratorData['permissions_level'];

                // Check if the collaborator already exists
                $existingCollaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
                    ->where('user_id', $user_id)
                    ->where('invited_by', Auth::id())
                    ->first();

                if ($existingCollaborator) {
                    $results[] = [
                        'user_id' => $user_id,
                        'status' => 'failed',
                        'message' => 'Collaborator already exists for this tag.',
                    ];
                    continue;
                }

                // Create a new TagCollaborator record
                $newCollaborator = new TagCollaborator();
                $newCollaborator->family_tag_id = $family_tag_id;
                $newCollaborator->user_id = $user_id;
                $newCollaborator->invited_by = Auth::id();
                $newCollaborator->status = 'pending';
                $newCollaborator->permissions_level = $permissions_level;

                // Handle avatar upload (if provided)
                if (isset($collaboratorData['avatar']) && $collaboratorData['avatar'] instanceof \Illuminate\Http\UploadedFile) {
                    $avatar = $collaboratorData['avatar'];
                    $avatarName = time() . '_' . $avatar->getClientOriginalName();
                    $avatar->move(public_path('avatars'), $avatarName);
                    $newCollaborator->avatar = 'https://admin.famoryapp.com/public/avatars/' . $avatarName;
                }

                $newCollaborator->save();

                $results[] = [
                    'user_id' => $user_id,
                    'status' => 'success',
                    'message' => 'Collaborator invited successfully.',
                ];
            }

            return response()->json([
                'message' => 'Collaborators processed.',
                'data' => $results,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'failed',
                'data' => [],
            ], 500);
        }
    }

    public function getAvailableUsers(Request $request, $family_tag_id)
{
    try {
        // Check if the $family_tag_id exists in the `family_tag_ids` table
        $familyTag = FamilyTagId::where('family_tag_id', $family_tag_id)->first();

        if (!$familyTag) {
            return response()->json([
                'message' => 'The specified family tag ID does not exist.',
                'status' => 'failed',
                'data' => [],
            ], 404);
        }

        // Fetch all user IDs already invited for this tag
        $invitedUserIds = TagCollaborator::where('family_tag_id', $family_tag_id)
            ->pluck('user_id')
            ->toArray();

        // Initialize the query for users not yet invited
        $query = User::whereNotIn('id', $invitedUserIds);

        // Apply search filter if `search` parameter is provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Fetch the filtered results
        $availableUsers = $query->get(['id', 'first_name', 'last_name', 'email']);

        if ($availableUsers->isEmpty()) {
            return response()->json([
                'message' => 'No available users to invite.',
                'status' => 'success',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Available users fetched successfully.',
            'status' => 'success',
            'data' => $availableUsers,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'status' => 'failed',
            'data' => [],
        ], 500);
    }
}



    //     public function inviteCollaborator(Request $request, $family_tag_id)
// {
//     // dd($request->all(), $request->file('avatar'));

    //         // Validate the input
//         $validator = Validator::make($request->all(), [
//             'user_id' => 'required|exists:users,id',
//             'permissions_level' => 'required|in:view,add,edit',
//             'avatar' => 'required|image|mimes:jpg,jpeg,png,gif|max:10048', // Only validate avatar if provided
//         ]);

    //         if ($validator->fails()) {
//             $errors = $validator->errors();
//             return response()->json(['message' => $errors->first(), 'status' => 'failed', 'error_type' => 'validation_error'], 400);
//         }

    //         if ($request->hasFile('avatar') && !$request->file('avatar')->isValid()) {
//             return response()->json(['message' => 'The avatar file is invalid.', 'status' => 'failed', 'error_type' => 'upload_error'], 400);
//         }


    //         // dd('stop');
//         if ($validator->fails()) {
//             $errors = $validator->errors();
//             return response()->json(['message' => $errors->first(), 'status' => 'failed', 'error_type' => 'validation_error'], 400);
//         }

    //         // Check if the collaborator already exists
//         $existingCollaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
//             ->where('user_id', $request->user_id)
//             ->where('invited_by', Auth::id())
//             ->first();

    //         if ($existingCollaborator) {
//             return response()->json(['message' => 'This collaborator already exists for this tag.'], 409);
//         }

    //         // Create a new TagCollaborator record
//         $collaborator = new TagCollaborator();
//         $collaborator->family_tag_id = $family_tag_id;
//         $collaborator->user_id = $request->user_id;
//         $collaborator->invited_by = Auth::id(); // The user inviting the collaborator
//         $collaborator->status = 'pending'; // Default status when invited
//         $collaborator->permissions_level = $request->permissions_level;



    //         // Handle avatar upload if provided
//         if ($request->hasFile('avatar')) {
//             try {
//                 $file = $request->file('avatar');
//                 $currentUser = Auth::user();
//                 $res = $this->UploadImage->saveMedia($file, $currentUser->id);
//                 $collaborator->avatar = $res; // Save path/response from saveMedia
//             } catch (\Exception $uploadException) {
//                 return response()->json([
//                     'message' => 'Failed to upload avatar. Please try again.',
//                     'status' => 'failed',
//                     'error_type' => 'upload_error'
//                 ], 500);
//             }
//         }

    //         $collaborator->save();

    //         return response()->json([
//             'message' => 'Collaborator invited successfully.',
//             'status' => 'success',
//             'data' => $collaborator
//         ], 200);

    // }




    public function deleteCollaborator(Request $request, $family_tag_id, $user_id)
    {
        try {
            // Find the collaborator record
            $collaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
                ->where('user_id', $user_id)
                ->first();

            if (!$collaborator) {
                return response()->json(['message' => 'Collaborator not found for this family tag.'], 404);
            }

            // Soft delete the collaborator
            $collaborator->delete();

            return response()->json(['message' => 'Collaborator removed successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
    }

    public function deleteCollaboratorV2(Request $request, $family_tag_id, $collaborator_id)
{
    try {
        // Find the collaborator record using the collaborator_id
        $collaborator = TagCollaborator::where('family_tag_id', $family_tag_id)
        ->where('id', $collaborator_id)
        ->first();

        if (!$collaborator) {
            return response()->json(['message' => 'Collaborator not found for this family tag.'], 404);
        }
        // Soft delete the collaborator
        $collaborator->delete();

        return response()->json(['message' => 'Collaborator removed successfully.'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
    }
}
    
    public function restoreCollaborator(Request $request, $family_tag_id, $user_id)
    {
        try {
            // Find the soft-deleted collaborator
            $collaborator = TagCollaborator::withTrashed()
                ->where('family_tag_id', $family_tag_id)
                ->where('user_id', $user_id)
                ->first();

            if (!$collaborator) {
                return response()->json(['message' => 'Collaborator not found or already restored.'], 404);
            }

            // Restore the collaborator
            $collaborator->restore();

            return response()->json(['message' => 'Collaborator restored successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
    }

    // public function getAllReceivedCollaboratorRequests(Request $request)
    // {
    //     try {
    //         // Ensure the user is authenticated
    //         $userId = Auth::id();

    //         // Fetch all pending collaborator invitations for the authenticated user across all tags
    //         $collaboratorRequests = TagCollaborator::where('user_id', $userId)  // Only show requests sent to the current user
    //             ->where('status', 'pending') // Only pending requests
    //             ->paginate(10); // Add pagination

    //         // Check if any invitations exist
    //         if ($collaboratorRequests->isEmpty()) {
    //             return response()->json([
    //                 'message' => 'No invitations found',
    //                 'status' => 'success', // Success status for consistency
    //                 'error_type' => '',
    //                 'data' => [],
    //                 'total_records' => 0,
    //                 'total_pages' => 1,
    //                 'current_page' => 1,
    //                 'per_page' => 10,
    //             ], 200);
    //         }

    //         // Return the collaborator requests with pagination details
    //         return response()->json([
    //             'message' => 'Get all collaborator requests successfully',
    //             'status' => 'success',
    //             'error_type' => '',
    //             'data' => $collaboratorRequests->items(),
    //             'total_records' => $collaboratorRequests->total(),
    //             'total_pages' => $collaboratorRequests->lastPage(),
    //             'current_page' => $collaboratorRequests->currentPage(),
    //             'per_page' => $collaboratorRequests->perPage(),
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => $e->getMessage(),
    //             'status' => 'failed',
    //             'error_type' => 'Exception',
    //             'data' => [],
    //             'total_records' => 0,
    //             'total_pages' => 0,
    //             'current_page' => 0,
    //             'per_page' => 0,
    //         ], 500);
    //     }
    // }

    public function getAllReceivedCollaboratorRequests(Request $request)
{
    try {
        // Ensure the user is authenticated
        $userId = Auth::id();

        // Fetch all pending collaborator invitations for the authenticated user across all tags
        $collaboratorRequests = TagCollaborator::where('user_id', $userId) // Only show requests sent to the current user
            ->where('status', 'pending') // Only pending requests
            ->paginate(10); // Add pagination

        // Check if any invitations exist
        if ($collaboratorRequests->isEmpty()) {
            return response()->json([
                'message' => 'No invitations found',
                'status' => 'success', // Success status for consistency
                'error_type' => '',
                'data' => [],
                'total_records' => 0,
                'total_pages' => 1,
                'current_page' => 1,
                'per_page' => 10,
            ], 200);
        }

        // Convert items to a collection and map inviter_name, inviter_email, and tag_title
        $formattedRequests = collect($collaboratorRequests->items())->map(function ($request) {
            // Fetch the inviter's details
            $inviter = User::find($request->invited_by);
            $request->inviter_name = $inviter ? $inviter->first_name . ' ' . $inviter->last_name : null;
            $request->inviter_email = $inviter ? $inviter->email : null;

            // Fetch the family tag title for each request
            $tag = FamilyTagId::where('family_tag_id', $request->family_tag_id)->first();
            $request->tag_title = $tag ? $tag->title : null;

            return $request;
        });

        // Return the collaborator requests with pagination details
        return response()->json([
            'message' => 'Get all collaborator requests successfully',
            'status' => 'success',
            'error_type' => '',
            'data' => $formattedRequests,
            'total_records' => $collaboratorRequests->total(),
            'total_pages' => $collaboratorRequests->lastPage(),
            'current_page' => $collaboratorRequests->currentPage(),
            'per_page' => $collaboratorRequests->perPage(),
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'status' => 'failed',
            'error_type' => 'Exception',
            'data' => [],
            'total_records' => 0,
            'total_pages' => 0,
            'current_page' => 0,
            'per_page' => 0,
        ], 500);
    }
}



    public function getAllReceivedCollaboratorRequestsAll(Request $request)
    {
        try {
            // Ensure the user is authenticated
            $userId = Auth::id();

            // Invitations received by the user (collaborate requests)
            $receivedInvitations = TagCollaborator::where('user_id', $userId)
                ->where('status', 'pending') // Invitations not yet accepted/rejected
                ->get()
                ->map(function ($invitation) {
                    $tag = FamilyTagId::where('family_tag_id', $invitation->family_tag_id)->first();
                    $inviter = User::find($invitation->invited_by);

                    return [
                        'message' => ($inviter ? $inviter->first_name . ' ' . $inviter->last_name : 'Someone') .
                            " requested you to collaborate on their Tag (" .
                            ($tag ? $tag->title : 'Unknown Tag') .
                            ") on " . $invitation->created_at->format('m.d.y'),
                        'details' => array_merge($invitation->toArray(), [
                            'inviter_name' => $inviter ? $inviter->first_name . ' ' . $inviter->last_name : 'Someone',
                            'tag_title' => $tag ? $tag->title : 'Unknown Tag',
                        ]),
                    ];
                });

            // Access requests made to the user's tags
            $accessRequests = TagCollaborator::where('invited_by', $userId)
                ->where('status', 'requested') // Requests for access
                ->get()
                ->map(function ($request) {
                    $tag = FamilyTagId::where('family_tag_id', $request->family_tag_id)->first();
                    $requester = User::find($request->user_id);

                    return [
                        'message' => ($requester ? $requester->first_name . ' ' . $requester->last_name : 'Someone') .
                            " requested access to your tag (" .
                            ($tag ? $tag->title : 'Unknown Tag') .
                            ") on " . $request->created_at->format('m.d.y'),
                        'details' => array_merge($request->toArray(), [
                            'requester_name' => $requester ? $requester->first_name . ' ' . $requester->last_name : 'Someone',
                            'tag_title' => $tag ? $tag->title : 'Unknown Tag',
                        ]),
                    ];
                });

            // Combine the results
            $combinedResults = [
                'received_invitations' => $receivedInvitations,
                'access_requests' => $accessRequests,
            ];

            // Check if there are any results
            if ($receivedInvitations->isEmpty() && $accessRequests->isEmpty()) {
                return response()->json([
                    'message' => 'No invitations or access requests found.',
                    'status' => 'success',
                    'error_type' => '',
                    'data' => $combinedResults,
                ], 200);
            }

            // Return the formatted response
            return response()->json([
                'message' => 'Get all received invitations and access requests successfully.',
                'status' => 'success',
                'error_type' => '',
                'data' => $combinedResults,
            ], 200);

        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'failed',
                'error_type' => 'Exception',
                'data' => [],
            ], 500);
        }
    }






    public function requestCollaboratorAccess(Request $request, $family_tag_id)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'permissions_level' => 'required|in:view,add,edit', // Allowed permission levels
                'request_message' => 'nullable|string|max:255', // Optional request message
            ]);

            // Ensure the user is authenticated
            $userId = Auth::id();

            // Check if the user is the owner of the tag
            $isOwner = DB::table('family_tag_ids')
                ->where('family_tag_id', $family_tag_id)
                ->where('created_user_id', $userId)
                ->exists();

            if ($isOwner) {
                return response()->json([
                    'message' => 'You cannot request access to your own tag.',
                    'status' => 'failed',
                    'data' => [],
                ], 403);
            }

            // Check if the request already exists
            $existingRequest = TagCollaborator::where('family_tag_id', $family_tag_id)
                ->where('user_id', $userId)
                ->where('request_type', 'access_request')
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'message' => 'You have already requested access to this tag.',
                ], 409);
            }

            // Create a new TagCollaborator record for access request
            $accessRequest = new TagCollaborator();
            $accessRequest->family_tag_id = $family_tag_id;
            $accessRequest->user_id = $userId;
            $accessRequest->invited_by = null; // No inviter since it's a request
            $accessRequest->status = 'pending'; // Default status
            $accessRequest->request_type = 'access_request'; // Indicate it's an access request
            $accessRequest->permissions_level = $request->permissions_level; // Requested permission level
            $accessRequest->request_message = $request->request_message; // Optional message

            $accessRequest->save();

            return response()->json([
                'message' => 'Access request submitted successfully.',
                'status' => 'success',
                'data' => [
                    'family_tag_id' => $family_tag_id,
                    'user_id' => $userId,
                    'permissions_level' => $request->permissions_level,
                    'request_message' => $request->request_message,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'failed',
                'data' => [],
            ], 500);
        }
    }


    public function getFirstFiveReceivedCollaboratorRequests(Request $request)
    {
        try {
            // Ensure the user is authenticated
            $userId = Auth::id();

            // Fetch the first 5 pending collaborator invitations for the authenticated user
            $collaboratorRequests = TagCollaborator::where('user_id', $userId) // Only show requests sent to the current user
                ->where('status', 'pending') // Only pending requests
                ->take(5) // Limit the results to the first 5
                ->get();

            // Check if any invitations exist
            if ($collaboratorRequests->isEmpty()) {
                return response()->json([
                    'message' => 'No invitations found',
                    'status' => 'success',
                    'error_type' => '',
                    'data' => [],
                ], 200);
            }

            // Map inviter name and tag title
            $formattedRequests = $collaboratorRequests->map(function ($request) {
                $inviter = User::find($request->invited_by);
                $tag = FamilyTagId::where('family_tag_id', $request->family_tag_id)->first();

                return array_merge($request->toArray(), [
                    'inviter_name' => $inviter ? $inviter->first_name . ' ' . $inviter->last_name : 'Someone',
                    'tag_title' => $tag ? $tag->title : 'Unknown Tag',
                ]);
            });

            // Return the collaborator requests
            return response()->json([
                'message' => 'Get first five collaborator requests successfully',
                'status' => 'success',
                'error_type' => '',
                'data' => $formattedRequests,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'failed',
                'error_type' => 'Exception',
                'data' => [],
            ], 500);
        }
    }



    public function getTagRequests(Request $request)
    {
        try {
            // Get the currently authenticated user
            $currentuser = Auth::user();

            // Fetch all tags with 'status' set to 'pending' from the 'tag_collaborators' table for the current user
            $pendingTags = TagCollaborator::where('invited_by', $currentuser->id)
                ->where('status', 'pending')
                ->with('familyTag')  // Assuming the relationship with the FamilyTagId model is defined
                ->orderBy('created_at', 'DESC')
                ->paginate(10); // Pagination applied

            // Check if there are no records
            if ($pendingTags->isEmpty()) {
                return response()->json([
                    'message' => 'No pending collaborators found for the current user.',
                    'status' => 'success',
                    'error_type' => '',
                    'data' => [],
                    'total_records' => 0,
                    'total_pages' => 0,
                    'current_page' => $pendingTags->currentPage(),
                    'per_page' => $pendingTags->perPage()
                ], 200);
            }

            return response()->json([
                'message' => 'Get all famory tag IDs successfully',
                'status' => 'success',
                'error_type' => '',
                'data' => $pendingTags->items(),
                'total_records' => $pendingTags->total(),
                'total_pages' => $pendingTags->lastPage(),
                'current_page' => $pendingTags->currentPage(),
                'per_page' => $pendingTags->perPage()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'failed',
                'error_type' => 'exception',
                'data' => [],
                'total_records' => 0,
                'total_pages' => 0,
                'current_page' => 0,
                'per_page' => 0
            ], 500);
        }
    }


    public function getTagRequestsFirstFive(Request $request)
    {
        try {
            // Get the currently authenticated user
            $currentuser = Auth::user();

            // Fetch only the first 5 tags with 'status' set to 'pending' from the 'tag_collaborators' table for the current user
            $pendingTags = TagCollaborator::where('invited_by', $currentuser->id)
                ->where('status', 'pending')
                ->with('familyTag')  // Assuming the relationship with the FamilyTagId model is defined
                ->orderBy('created_at', 'DESC')
                ->take(5)  // Limit the result to only the first 5 records
                ->get();  // Use get() to retrieve the records without pagination

            if ($pendingTags->isEmpty()) {
                return response()->json(['message' => 'No pending collaborators found for the current user.'], 200);
            }

            return response()->json([
                'message' => 'Successfully retrieved pending collaborators.',
                'data' => $pendingTags
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'failed',
                'data' => []
            ], 500);
        }
    }

    public function updateCollaboratorStatus(Request $request)
    {
        try {
            // Validate the input
            $validated = $request->validate([
                'collaborator_id' => 'required|exists:tag_collaborators,id',
                'status' => 'required|in:accepted,rejected', // Ensure status is either 'accepted' or 'rejected'
            ]);

            // Find the collaborator request by ID
            $collaborator = TagCollaborator::where('id', $request->collaborator_id)
                ->where('status', 'pending') // Ensure the status is currently 'pending'
                ->first();

            // Check if the collaborator request exists and is pending
            if (!$collaborator) {
                return response()->json(['message' => 'Collaborator request not found or not pending.'], 404);
            }

            // Update the status to either 'accepted' or 'rejected'
            $collaborator->status = $request->status;
            $collaborator->save();

            return response()->json(['message' => 'Collaborator request status updated successfully.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }


    // public function getSavedTags(Request $request)
    // {
    //     try {
    //         // Ensure the user is authenticated
    //         $userId = Auth::id();

    //         // Fetch all accepted collaborator invitations for the authenticated user across all tags
    //         $acceptedCollaboratorRequests = TagCollaborator::where('user_id', $userId)  // Only show requests for the current user
    //             ->where('status', 'accepted') // Only accepted requests
    //             ->paginate(10); // Paginate the results

    //         // Check if any accepted invitations exist
    //         if ($acceptedCollaboratorRequests->isEmpty()) {
    //             return response()->json([
    //                 "message" => "No accepted invitations found",
    //                 "status" => "success",
    //                 "error_type" => "",
    //                 "data" => [],
    //                 "total_records" => 0,
    //                 "total_pages" => 0,
    //                 "current_page" => 1,
    //                 "per_page" => 10
    //             ], 200);
    //         }

    //         // Format the response
    //         return response()->json([
    //             "message" => "Get all family tag IDs successfully",
    //             "status" => "success",
    //             "error_type" => "",
    //             "data" => $acceptedCollaboratorRequests->items(),
    //             "total_records" => $acceptedCollaboratorRequests->total(),
    //             "total_pages" => $acceptedCollaboratorRequests->lastPage(),
    //             "current_page" => $acceptedCollaboratorRequests->currentPage(),
    //             "per_page" => $acceptedCollaboratorRequests->perPage()
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             "message" => $e->getMessage(),
    //             "status" => "failed",
    //             "error_type" => "exception",
    //             "data" => [],
    //             "total_records" => 0,
    //             "total_pages" => 0,
    //             "current_page" => 1,
    //             "per_page" => 10
    //         ], 500);
    //     }
    // }

    public function getSavedTags(Request $request)
{
    try {
        // Ensure the user is authenticated
        $userId = Auth::id();

        // Fetch all accepted collaborator invitations for the authenticated user across all tags
        $acceptedCollaboratorRequests = TagCollaborator::where('user_id', $userId)  // Only show requests for the current user
            ->where('status', 'accepted') // Only accepted requests
            ->paginate(10); // Paginate the results

        // Check if any accepted invitations exist
        if ($acceptedCollaboratorRequests->isEmpty()) {
            return response()->json([
                "message" => "No accepted invitations found",
                "status" => "success",
                "error_type" => "",
                "data" => [],
                "total_records" => 0,
                "total_pages" => 0,
                "current_page" => 1,
                "per_page" => 10
            ], 200);
        }

        // Use the map function directly on the collection to add saved_date, creator_name, and tag details
        $formattedData = $acceptedCollaboratorRequests->getCollection()->map(function ($request) {
            // Fetch the creator's information (invited_by user)
            $creator = \App\Models\User::find($request->invited_by);
            $creatorName = $creator ? $creator->first_name . ' ' . $creator->last_name : 'Unknown Creator';

            // Fetch the family tag details
            $familyTag = \App\Models\FamilyTagId::where('family_tag_id', $request->family_tag_id)->first();
            $tagTitle = $familyTag ? $familyTag->title : 'Unknown Tag';
            $tagImage = $familyTag ? $familyTag->image : null;

            return [
                'id' => $request->id,
                'family_tag_id' => $request->family_tag_id,
                'user_id' => $request->user_id,
                'invited_by' => $request->invited_by,
                'status' => $request->status,
                'request_type' => $request->request_type,
                'request_message' => $request->request_message,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
                'permissions_level' => $request->permissions_level,
                'saved_date' => $request->updated_at->format('Y-m-d H:i:s'), // Format the saved_date to datetime
                'creator_name' => $creatorName, // Add the creator_name field
                'tag_title' => $tagTitle, // Add the tag title
                'tag_image' => $tagImage, // Add the tag image
            ];
        });

        // Format the response with paginated data
        return response()->json([
            "message" => "Get all family tag IDs successfully",
            "status" => "success",
            "error_type" => "",
            "data" => $formattedData,
            "total_records" => $acceptedCollaboratorRequests->total(),
            "total_pages" => $acceptedCollaboratorRequests->lastPage(),
            "current_page" => $acceptedCollaboratorRequests->currentPage(),
            "per_page" => $acceptedCollaboratorRequests->perPage()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            "message" => $e->getMessage(),
            "status" => "failed",
            "error_type" => "exception",
            "data" => [],
            "total_records" => 0,
            "total_pages" => 0,
            "current_page" => 1,
            "per_page" => 10
        ], 500);
    }
}

public function saveTag(Request $request)
{
    try {
        // Ensure the user is authenticated
        $userId = Auth::id();

        // Validate the request data
        $validated = $request->validate([
            'family_tag_id' => 'required|exists:family_tag_ids,family_tag_id', // Ensure family_tag_id exists in the family_tag_ids table
        ]);

        // Check if the tag already exists for the user
        $existingTag = SavedTag::where('user_id', $userId)
                               ->where('family_tag_id', $validated['family_tag_id'])
                               ->first();

        if ($existingTag) {
            return response()->json([
                "message" => "Tag already Already saved",
                "status" => "success",
                "error_type" => "duplicate_entry",
                "data" => $existingTag
            ], 200);
        }

        // Create the new saved tag
        $savedTag = SavedTag::create([
            'family_tag_id' => $validated['family_tag_id'],
            'user_id' => $userId,
            'is_removed' => 0, // Default value for is_removed
        ]);

        return response()->json([
            "message" => "Tag saved successfully",
            "status" => "success",
            "error_type" => "",
            "data" => $savedTag
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            "message" => $e->getMessage(),
            "status" => "failed",
            "error_type" => "exception",
            "data" => []
        ], 500);
    }
}


public function getSavedTagsV2(Request $request)
{
    try {
        // Ensure the user is authenticated
        $userId = Auth::id();

        // Fetch all saved tags for the authenticated user
        $savedTags = SavedTag::where('user_id', $userId)
                             ->with(['familyTag', 'user']) // Load the related models
                             ->paginate(10); // Paginate the results
                             // Check if any saved tags exist
                             if ($savedTags->isEmpty()) {
                                 return response()->json([
                                     "message" => "No saved tags found",
                "status" => "success",
                "error_type" => "",
                "data" => [],
                "total_records" => 0,
                "total_pages" => 0,
                "current_page" => 1,
                "per_page" => 10
            ], 200);
        }
        // dd($savedTags);

        // Format the response to include the necessary data
        $formattedData = $savedTags->getCollection()->map(function ($savedTag) {
            // Fetch the family tag details
            $familyTag = $savedTag->familyTag;
            $tagTitle = $familyTag ? $familyTag->title : 'Unknown Tag';
            $tagImage = $familyTag ? $familyTag->image : null;

            // Fetch the user details
            $user = $savedTag->user;
            $userName = $user ? $user->first_name . ' ' . $user->last_name : 'Unknown User';

            return [
                'id' => $savedTag->id,
                'family_tag_id' => $savedTag->family_tag_id,
                'user_id' => $savedTag->user_id,
              //  'is_removed' => $savedTag->is_removed,
                'saved_date' => $savedTag->created_at->format('Y-m-d H:i:s'),
                'tag_title' => $tagTitle,
                'tag_image' => $tagImage,
                'user_name' => $userName,
            ];
        });

        // Format the response with paginated data
        return response()->json([
            "message" => "Get all saved tags successfully",
            "status" => "success",
            "error_type" => "",
            "data" => $formattedData,
            "total_records" => $savedTags->total(),
            "total_pages" => $savedTags->lastPage(),
            "current_page" => $savedTags->currentPage(),
            "per_page" => $savedTags->perPage()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            "message" => $e->getMessage(),
            "status" => "failed",
            "error_type" => "exception",
            "data" => [],
            "total_records" => 0,
            "total_pages" => 0,
            "current_page" => 1,
            "per_page" => 10
        ], 500);
    }
}

public function getFirstFiveSavedTagsV2(Request $request)
{
    try {
        // Ensure the user is authenticated
        $userId = Auth::id();

        // Fetch the first 5 saved tags for the authenticated user
        $savedTags = SavedTag::where('user_id', $userId)
                             ->with(['familyTag', 'user']) // Load the related models
                             ->take(5) // Limit the results to the first 5
                             ->get();

        // Check if any saved tags exist
        if ($savedTags->isEmpty()) {
            return response()->json([
                "message" => "No saved tags found",
                "status" => "success",
                "error_type" => "",
                "data" => [],
                "total_records" => 0,
            ], 200);
        }

        // Format the response to include the necessary data
        $formattedData = $savedTags->map(function ($savedTag) {
            // Fetch the family tag details
            $familyTag = $savedTag->familyTag;
            $tagTitle = $familyTag ? $familyTag->title : 'Unknown Tag';
            $tagImage = $familyTag ? $familyTag->image : null;

            // Fetch the user details
            $user = $savedTag->user;
            $userName = $user ? $user->first_name . ' ' . $user->last_name : 'Unknown User';

            return [
                'id' => $savedTag->id,
                'family_tag_id' => $savedTag->family_tag_id,
                'user_id' => $savedTag->user_id,
               // 'is_removed' => $savedTag->is_removed,
                'saved_date' => $savedTag->created_at->format('Y-m-d H:i:s'),
                'tag_title' => $tagTitle,
                'tag_image' => $tagImage,
                'user_name' => $userName,
            ];
        });

        // Format the response
        return response()->json([
            "message" => "Get first 5 saved tags successfully",
            "status" => "success",
            "error_type" => "",
            "data" => $formattedData,
            "total_records" => $savedTags->count(),
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            "message" => $e->getMessage(),
            "status" => "failed",
            "error_type" => "exception",
            "data" => [],
            "total_records" => 0,
        ], 500);
    }
}

public function deleteSavedTag($id)
{
    try {
        // Ensure the user is authenticated
        $userId = Auth::id();

        // Find the saved tag for the authenticated user
        $savedTag = SavedTag::where('id', $id)
                            ->where('user_id', $userId)
                            ->first();

        // Check if the saved tag exists
        if (!$savedTag) {
            return response()->json([
                "message" => "Saved tag not found or does not belong to the user",
                "status" => "failed",
                "error_type" => "not_found",
                "data" => []
            ], 404);
        }

        // Delete the saved tag
        $savedTag->delete();

        return response()->json([
            "message" => "Saved tag deleted successfully",
            "status" => "success",
            "error_type" => "",
            "data" => []
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            "message" => $e->getMessage(),
            "status" => "failed",
            "error_type" => "exception",
            "data" => []
        ], 500);
    }
}


public function getSavedTagsWithSearch(Request $request)
{
    try {
        // Ensure the user is authenticated
        $userId = Auth::id();

        // Retrieve the search keyword from the request
        $searchKeyword = $request->input('search', '');

        // Fetch all accepted collaborator invitations for the authenticated user across all tags
        $query = TagCollaborator::where('user_id', $userId) // Only show requests for the current user
            ->where('status', 'accepted'); // Only accepted requests

        // Apply the search filter if a keyword is provided
        if (!empty($searchKeyword)) {
            $query->where(function ($q) use ($searchKeyword) {
                $q->where('family_tag_id', 'like', "%{$searchKeyword}%")
                    ->orWhere('request_message', 'like', "%{$searchKeyword}%")
                    ->orWhere('permissions_level', 'like', "%{$searchKeyword}%")
                    ->orWhere('status', 'like', "%{$searchKeyword}%")
                    ->orWhere('request_type', 'like', "%{$searchKeyword}%")
                    ->orWhereHas('invitedByUser', function ($subQuery) use ($searchKeyword) {
                        $subQuery->where('first_name', 'like', "%{$searchKeyword}%")
                            ->orWhere('last_name', 'like', "%{$searchKeyword}%");
                    });
            });
        }

        // Paginate the results
        $acceptedCollaboratorRequests = $query->paginate(10);

        // Check if any accepted invitations exist
        if ($acceptedCollaboratorRequests->isEmpty()) {
            return response()->json([
                "message" => "No accepted invitations found",
                "status" => "success",
                "error_type" => "",
                "data" => [],
                "total_records" => 0,
                "total_pages" => 0,
                "current_page" => 1,
                "per_page" => 10
            ], 200);
        }

        // Use the map function directly on the collection to add all required fields
        $formattedData = $acceptedCollaboratorRequests->getCollection()->map(function ($request) {
            // Fetch the creator's information (invited_by user)
            $creator = \App\Models\User::find($request->invited_by);
            $creatorName = $creator ? $creator->first_name . ' ' . $creator->last_name : 'Unknown Creator';

            // Fetch the family tag details
            $familyTag = \App\Models\FamilyTagId::where('family_tag_id', $request->family_tag_id)->first();
            $tagTitle = $familyTag ? $familyTag->title : 'Unknown Tag';
            $tagImage = $familyTag ? $familyTag->image : null;

            return [
                'id' => $request->id,
                'family_tag_id' => $request->family_tag_id,
                'user_id' => $request->user_id,
                'invited_by' => $request->invited_by,
                'status' => $request->status,
                'request_type' => $request->request_type,
                'request_message' => $request->request_message,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
                'permissions_level' => $request->permissions_level,
                'saved_date' => $request->updated_at->format('Y-m-d H:i:s'), // Format saved_date
                'creator_name' => $creatorName, // Add the creator_name field
                'tag_title' => $tagTitle, // Add the tag title
                'tag_image' => $tagImage, // Add the tag image
            ];
        });

        // Format the response with paginated data
        return response()->json([
            "message" => "Get all family tag IDs successfully",
            "status" => "success",
            "error_type" => "",
            "data" => $formattedData,
            "total_records" => $acceptedCollaboratorRequests->total(),
            "total_pages" => $acceptedCollaboratorRequests->lastPage(),
            "current_page" => $acceptedCollaboratorRequests->currentPage(),
            "per_page" => $acceptedCollaboratorRequests->perPage()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            "message" => $e->getMessage(),
            "status" => "failed",
            "error_type" => "exception",
            "data" => [],
            "total_records" => 0,
            "total_pages" => 0,
            "current_page" => 1,
            "per_page" => 10
        ], 500);
    }
}

// public function getFirstFiveSavedTags(Request $request)
// {
//     try {
//         // Ensure the user is authenticated
//         $userId = Auth::id();

//         // Fetch the first 5 accepted collaborator requests for the authenticated user
//         $collaboratorRequests = TagCollaborator::where('user_id', $userId)  // Only show requests for the current user
//             ->where('status', 'accepted') // Only accepted requests
//             ->limit(5) // Get only the first 5 requests
//             ->get();

//         // Check if any accepted invitations exist
//         if ($collaboratorRequests->isEmpty()) {
//             return response()->json(['message' => 'No accepted invitations found'], 404);
//         }

//         // Format the response with all required fields
//         $formattedData = $collaboratorRequests->map(function ($request) {
//             // Fetch the creator's information (invited_by user)
//             $creator = \App\Models\User::find($request->invited_by);
//             $creatorName = $creator ? $creator->first_name . ' ' . $creator->last_name : 'Unknown Creator';

//             // Fetch the family tag details
//             $familyTag = \App\Models\FamilyTagId::where('family_tag_id', $request->family_tag_id)->first();
//             $tagTitle = $familyTag ? $familyTag->title : 'Unknown Tag';
//             $tagImage = $familyTag ? $familyTag->image : null;

//             return [
//                 'id' => $request->id,
//                 'family_tag_id' => $request->family_tag_id,
//                 'user_id' => $request->user_id,
//                 'invited_by' => $request->invited_by,
//                 'status' => $request->status,
//                 'request_type' => $request->request_type,
//                 'request_message' => $request->request_message,
//                 'created_at' => $request->created_at,
//                 'updated_at' => $request->updated_at,
//                 'permissions_level' => $request->permissions_level,
//                 'saved_date' => $request->updated_at->format('Y-m-d H:i:s'), // Format saved_date
//                 'creator_name' => $creatorName, // Add the creator_name field
//                 'tag_title' => $tagTitle, // Add the tag title
//                 'tag_image' => $tagImage, // Add the tag image
//             ];
//         });

//         // Return the formatted response
//         return response()->json(['saved_tags' => $formattedData], 200);

//     } catch (\Exception $e) {
//         return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
//     }
// }


    //-----------------------------------------------------------------------------------------------------------------





    // public function getTrustedCompanies(Request $request){
    //     try{
            
    //         $getCompanies = TrustedPartners::whereHas('creator', function($query) {
    //                         $query->whereNull('deleted_at'); 
    //                             })->orderBy('featured_partner', 'DESC')
    //                         ->orderBy('id', 'DESC');
            
    //         if ($request->category) {
    //             $getCompanies = $getCompanies->where('category', 'LIKE', '%' . $request->category . '%');
    //         }
            
    //         if ($request->search) {
    //             $getCompanies = $getCompanies->where('company_name', 'LIKE', '%' . $request->search . '%');
    //         }
            
            
    //         if (!empty($request->latitude) && !empty($request->longitude)) {
    //             $latitude = $request->latitude;
    //             $longitude = $request->longitude;
    //             $type = $request->type;
            
    //             $orderColumn = 'distance';
    //             $orderDirection = 'ASC';
            
    //             if ($type === 'furthest') {
    //                 $orderDirection = 'DESC';
    //             }
            
    //             $userData = TrustedPartners::whereNotNull('lat')
    //                         ->whereNotNull('lng')
    //                         ->select('trusted_partners.*')
    //                         ->selectRaw("( 3959 * acos( cos( radians(?) ) * cos( radians(lat) ) 
    //                             * cos( radians(lng) - radians(?) ) + sin( radians(?) ) 
    //                             * sin( radians(lat) ) ) ) AS distance", [$latitude, $longitude, $latitude])
    //                         ->orderBy($orderColumn, $orderDirection)
    //                         ->orderBy('featured_partner', 'DESC')
    //                         ->orderBy('id', 'DESC')
    //                         ->paginate(10);
            
    //             return $this->successResponse("Get all Trusted Companies successfully", 200, $userData->items(), $userData);
    //         }
            
    //         $getCompanies = $getCompanies->paginate(10);

    //         if ($getCompanies->isEmpty()) {
    //             return $this->successResponse("Not found any Trusted Company", 200, null);
    //         }

    //         return $this->successResponse("Get all Trusted Companies successfully",200, $getCompanies->items(),$getCompanies);  

            
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "error_type" => "", 'data' => []], 500);
    //     }
        
    // }



    public function getTrustedCompanies(Request $request)
    {
        try {
            // Start with the base query
            $getCompanies = TrustedPartners::whereHas('creator', function($query) {
                                    $query->whereNull('deleted_at');
                                })
                                ->orderBy('featured_partner', 'DESC')
                                ->orderBy('id', 'DESC');

            // Apply category filter if provided
            if ($request->category) {
                $getCompanies = $getCompanies->where('category', 'LIKE', '%' . $request->category . '%');
            }

            // Apply company name search filter if provided
            if ($request->search) {
                $getCompanies = $getCompanies->where('company_name', 'LIKE', '%' . $request->search . '%');
            }

            // Apply location-based filter if latitude and longitude are provided
            if (!empty($request->latitude) && !empty($request->longitude)) {
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $type = $request->type;

                $orderColumn = 'distance';
                $orderDirection = 'ASC';

                if ($type === 'furthest') {
                    $orderDirection = 'DESC';
                }

                // Add the location-based logic to the query
                $getCompanies = $getCompanies->whereNotNull('lat')
                    ->whereNotNull('lng')
                    ->select('trusted_partners.*')
                    ->selectRaw("( 3959 * acos( cos( radians(?) ) * cos( radians(lat) ) 
                                    * cos( radians(lng) - radians(?) ) + sin( radians(?) ) 
                                    * sin( radians(lat) ) ) ) AS distance", [$latitude, $longitude, $latitude])
                    ->orderBy($orderColumn, $orderDirection);
            }

            // Paginate the final result
            $getCompanies = $getCompanies->paginate(10);

            // Check if the result set is empty
            if ($getCompanies->isEmpty()) {
                return $this->successResponse("Not found any Trusted Company", 200, null);
            }

            // Return successful response with data
            return $this->successResponse("Get all Trusted Companies successfully", 200, $getCompanies->items(), $getCompanies);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "error_type" => "", 'data' => []], 500);
        }
    }

    
    
    // cron-renewal-subscription
    public function renewalsubscription(Request $request){
        \Log::info("run cron subscription Trusted");
        try{
            $getRenewalDates = TrustedPartners::where('renewal_date','!=',null)->get();
            $getCurrentDate =  date('Y-m-d');
            
            foreach($getRenewalDates as $getRenewalDate){
                
                $renewalDate = date('Y-m-d', strtotime($getRenewalDate->renewal_date));
                
                // Check if the current date matches the renewal date
                if($getCurrentDate === $renewalDate){
                    
                    // If cancel_status is 0 (not canceled)
                    if($getRenewalDate->cancel_status == '0'){
                        
                        $priceId = $getRenewalDate->featured_company_price_id;
                        $getPrice = FeaturedCompanyPrice::where('id',$priceId)->first();
                        
                        if (!$getPrice) {
                            continue; // Skip to the next iteration if price is not found
                        }
                        
                        $userId = $getRenewalDate->created_by;
                        $current_user = User::where('id',$userId)->first();
                        
                        $getCardId = SubscribedPartner::where('trusted_partner_id',$getRenewalDate->id)->orderBy('id','desc')->first();
                        
                        $stripe_res = $this->StripeService->stripePaymentIntent($getPrice->price, $getCardId->source, $current_user->stripe_customer_id);
                        if($stripe_res['res'] == false){  
                            \Log::error("Payment failed for User ID: " . $userId . " - " . $stripe_res['msg']);
                            $update = TrustedPartners::where('id', $getRenewalDate->id)->update(['featured_partner' => '0','renewal_date' => null]);
                            continue;
                        }
                        
                        $payment_intent_id = $stripe_res['payment_intent_id'];
                        $charge_id = $stripe_res['charge_id'];
                        
                        
                        $featureCompanyPrice = FeaturedCompanyPrice::find($priceId);
                        
                        $today = date('Y-m-d');
                        $renewalDate = date('Y-m-d', strtotime('+'.$featureCompanyPrice->month.' month', strtotime($today)));
        
            
                        $data = new SubscribedPartner;
                        $data->trusted_partner_id = $getRenewalDate->id;
                        $data->user_id = $userId;
                        $data->payment_indent_id = $payment_intent_id;
                        $data->charge_id = $charge_id;
                        $data->source = $getCardId->source;
                        $data->source_type = 'card';
                        $data->type = 'debit';
                        $data->subscription_type= $priceId;
                        $data->amount = $getPrice->price;
                        $data->save();
                        
                        
                        // update the feature_partner status and renewal_date
                        $update = TrustedPartners::where('id', $getRenewalDate->id)->update(['featured_partner' => '1','renewal_date' => $renewalDate,'featured_company_price_id'=>$priceId]);
                        
                        return "successfully";
                    }
                    // If cancel_status is 1 (canceled)
                    elseif($getRenewalDate->cancel_status == '1'){
                        $update = TrustedPartners::where('id', $getRenewalDate->id)->update(['featured_partner' => '0','renewal_date' => null]);
                    }
                }
                
            }
        } catch (\Exception $e) {
           \Log::error("Error in renewalsubscription: " . $e->getMessage());
            return response()->json(['message' => 'An error occurred during the renewal process.', 'status' => 'failed'], 500);
        }
    }
    
    
        // cron-renewal-subscription-ads
    public function renewalAdsSubscription()
    {
        try {
            $getRenewalDates = Advertisement::where('renew_date', '!=', null)->get();
            $getCurrentDate = date('Y-m-d');
    
            foreach ($getRenewalDates as $getRenewalDate) {
                $renewalDate = date('Y-m-d', strtotime($getRenewalDate->renew_date));
                
                // Check if today is 5 days before the renewal date
                $fiveDaysBeforeRenewal = date('Y-m-d', strtotime('-5 days', strtotime($renewalDate)));
                if ($getCurrentDate === $fiveDaysBeforeRenewal && !$getRenewalDate->reminder_email_sent) {
                    // Send email notification here
                    $user = User::find($getRenewalDate->user_id);
                    \Mail::to($user->email)->send(new AdsRenewalReminder($getRenewalDate,$user));
                    $getRenewalDate->update(['reminder_email_sent' => 1]);
                }
    
                // Check if the current date matches the renewal date
                if ($getCurrentDate >= $renewalDate){
                    // If cancel_status is 0 (not canceled)
                    if ($getRenewalDate->cancel_status == '0') {
                        $getPrice = AdsPrice::first();
                        
                        if (!$getPrice) {
                            continue; // Skip to the next iteration if price is not found
                        }
                        
                        $userId = $getRenewalDate->user_id;
                        $current_user = User::find($userId);
                        
                        $stripe_res = $this->StripeService->stripePaymentIntent($getPrice->price, $getRenewalDate->card_id, $current_user->stripe_customer_id);
                        if ($stripe_res['res'] == false) {
                            \Log::info("check renewal ads subscription");
                            \Log::info($stripe_res);  
                            $update = Advertisement::where('id', $getRenewalDate->id)->update(['renew_date' => null,'cancel_status' => 1]);
                            continue;
                        }
                        
                        $payment_intent_id = $stripe_res['payment_intent_id'];
                        $charge_id = $stripe_res['charge_id'];
                        
                     
                        $featureCompanyPrice = $getPrice;
                        
                        $today = date('Y-m-d');
                        $renewalDate = date('Y-m-d', strtotime('+' . $featureCompanyPrice->day . ' month', strtotime($today)));
                        
                        $data = new TransactionHistory;
                        $data->user_id = $current_user->id;
                        $data->ads_id = $getRenewalDate->id;
                        $data->source = $stripe_res['card_last_four'];
                        $data->source_type = 'card';
                        $data->type = 'debit';
                        $data->amount = $getPrice->price;
                        $data->save();
    
                        $advertisement = Advertisement::find($getRenewalDate->id);
                        $advertisement->renew_date = $renewalDate;
                        $advertisement->save(); 
                        $user = User::find($getRenewalDate->user_id);
                        \Mail::to($user->email)->send(new RenewalAdPaymentProcess($advertisement,$user,$data));
                        
                        return "successfully";
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error in renewalsubscription: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    


    public function getAds(Request $request) {
        try {
            // Validate user input (latitude and longitude)
            $validator = Validator::make($request->all(), [
                'lat' => 'required',
                'long' => 'required',
            ]);

            // Return validation errors if any
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $error) {
                    return response()->json(['message' => $error, 'status' => 'failed', "error_type" => "",], 400);
                }
            }

            $currect_user = Auth::id();
            $userLat = $request->lat;
            $userLng = $request->long;
            $radius = 60;


            $ads = Advertisement::whereHas('user', function($query) {
                        $query->whereNull('deleted_at'); 
                    })
                    ->where('payment_status', 1) 
                    ->orWhere('show_ads_status', '1') 
                    ->orderBy('id', 'desc') 
                    ->get();

            $localAds = $ads->filter(function ($ad) use ($userLat, $userLng, $radius) {
                if ($ad->latitude !== null && $ad->longtitude !== null) {
                    $distance = $this->calculateDistance($userLat, $userLng, $ad->latitude, $ad->longtitude);
                    return $distance <= $radius;
                }
                return false;
            })->values();

            $nationalAds = $ads->filter(function ($ad) {
                    return $ad->is_national == 1;
            })->values();


            // If no local ads found, fallback to national ads
            if ($localAds->isEmpty()) {
                $filteredAds = $nationalAds;
            } else {
                $filteredAds = $localAds->merge($nationalAds);
            }

            // If no ads found at all, return message
            if ($filteredAds->isEmpty()) {
                return $this->successResponse("No ads found", 200, null);
            }

            // Select a random ad from the filtered ads
            $randomAd = $filteredAds->random();
            
            // Track ad view in AdsSee table
            $adSee = AdsSee::firstOrNew(['ads_id' => $randomAd->id]);
            $adSee->view = ($adSee->view ?? 0) + 1;
            $adSee->save();

            // Return the random ad as a response
            return $this->successResponse("Ads fetched successfully", 200, $randomAd);

        } catch (\Exception $e) {
            // Return any exception as a 500 error
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "error_type" => "", 'data' => []], 500);
        }
    }

    

    protected function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 3959; // Radius of the earth in miles

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    
    public function updateAdsSeeCount(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required',
                'ads_id' => 'required',
                'count' => 'required|integer',
            ]);
    
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            $adSee = AdsSee::firstOrNew(['ads_id' => $request->ads_id]);
    
            if ($request->type === 'open') {
                $adSee->click_to_open = ($adSee->click_to_open ?? 0) + $request->count;
            } elseif ($request->type === 'website') {
                $adSee->click_to_website = ($adSee->click_to_website ?? 0) + $request->count;
            }
    
            $adSee->save();
    
            return $this->successResponse("Count Update Successfully", 200, null);
    
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    public function getSubscriptionList(Request $request)
    {
        try {
            $getDatas = SubscriptionSetting::orderby('id', 'asc')->get();
    
            if ($getDatas->isEmpty()) {
                return $this->successResponse("Subscription Plan not fetched successfully", 200, null);
            }
            
            $current_user = Auth::id();
            $data = Subscription::where('user_id',$current_user)->first();
            if($data){
                 
                if($data->subscription == "free"){
                    foreach ($getDatas as $getdata) {
                        if ($getdata->plan_id_android == "free") {
                            $getdata->isActive = true;
                        } elseif ($getdata->plan_id_ios == "free") {
                            $getdata->isActive = true;
                        } else {
                            $getdata->isActive = false;
                        }
                    }
                }else{
                    $subscribeValidation = $this->subscribe_validation();
                    if ($subscribeValidation && isset($subscribeValidation->original['data'])) {
                        $product_Ids = $subscribeValidation->original['data']['product_id'] ?? null;
                        $expired = $subscribeValidation->original['data']['is_expired'] ?? null;
                        $platform = $subscribeValidation->original['data']['platform'] ?? null;  // Assuming this should be platform
            
                        foreach ($getDatas as $getdata) {
                            if ($platform == "android" && $getdata->plan_id_android == $product_Ids && $expired === 'no') {
                                $getdata->isActive = true;
                            } elseif ($platform == "ios" && $getdata->plan_id_ios == $product_Ids && $expired === 'no') {
                                $getdata->isActive = true;
                            } else {
                                $getdata->isActive = false;
                            }
                        }
                    } else {
                        foreach ($getDatas as $getdata) {
                            $getdata->isActive = false;
                        }
                    }
                }
            }else{
                 foreach($getDatas as $data)
                 {
                     $data->isActive = false; 
                 }
            }
    
            return $this->successResponse("Subscription Plan fetched successfully", 200, $getDatas);
    
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }

    
    
    public function subscribe(Request $request){

        $validator = Validator::make($request->all(), [
            'subscription' => 'required',
            // 'receipt' => 'required',
            'platform' => 'required',
        ]);

        if($validator->fails()){
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
               return $this->errorResponse(ucfirst($value), 'validation_error', 400);
            }
        }
        try{

            $user = Auth::user();
            $subscription = Subscription::where('user_id',$user->id)->first();
            if(!$subscription){
                $subscription = new Subscription;
                $subscription->user_id = $user->id;
                $subscription->subscription = $request->subscription;
                $subscription->receipt = $request->receipt;
                $subscription->platform = $request->platform;
                
                if ($request->subscription === "free") {
                    $subscription->expiry_date = now()->addMonth();
                }

                
                $subscription->save();
                $response= $subscription;
              
            }else{
                $subscription->user_id = $user->id;
                $subscription->subscription = $request->subscription;
                $subscription->receipt = $request->receipt;
                $subscription->platform = $request->platform;
                $subscription->updated_at = date('Y-m-d H:i:s');
                
                if ($request->subscription === "free") {
                    $subscription->expiry_date = now()->addMonth();
                }
                
                $subscription->save();
                $response= $subscription;
            
            }

            if($subscription){
                return $this->successResponse("The subscription has been purchased successfully",200, $response);
            }else{
                return $this->errorResponse('The subscription has not been purchased successfully', 'not_found', 400);
            }
            
        }catch(\Exception $exception){
            return $this->errorResponse($exception-> getMessage(), 'internal_server_error', 500);
        }
    }
    
    public function subscribe_validation()
    {
        try {
            $data = [];
            $current_user = Auth::guard('api')->user();
            $subscribed_user = Subscription::where('user_id', $current_user->id)->first();
            
            if($subscribed_user->subscription == 'free'){
                return $this->successResponse('User get free subscribed', 200, null);
            }
    
            if (empty($subscribed_user)) {
                return $this->successResponse('User not subscribed', 200, null);
            }
    
            $receipt = $subscribed_user->receipt;
            if ($subscribed_user->platform == "android") {
                require_once(base_path() . '/vendor/google_subscription/autoload.php');
                $subscription_details = $this->checkAndroid(json_decode($receipt));

                if ($subscription_details) {
                    $data['membershipEnable'] = $subscription_details['membershipEnable'];
                    $data['expires_date_pst'] = date('Y-m-d h:i A', strtotime($subscription_details['expires_date_pst']));
                    $data['product_id'] = $subscription_details['product_id'];
                    $data['is_expired'] = $subscription_details['expires_date_pst'] >= date("Y-m-d h:i A") ? 'no' : 'yes';
                    $data['platform'] = 'android';
                }
                return $this->successResponse('Get android subscription successfully', 200, $data);
                
                
                
            } elseif ($subscribed_user->platform == "iOS") {
                require_once(base_path() . '/vendor/subscription_validation/autoload.php');
                $ios = $this->checkIos($receipt);
                $data = $ios->original['data'];
                return $this->successResponse('Get iOS subscription successfully', 200, $data);
            }
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'internal_server_error', 500);
        }
    }

    
    
    private function checkAndroid($receipt) {
        if (isset($receipt)) {
            $keyFilePath = app_path('Services/service_account.json');
            $client = new Client();
            $client->setAuthConfig($keyFilePath);
            $client->addScope(AndroidPublisher::ANDROIDPUBLISHER);
    
            $packageName = 'io.famory.app';
            $subscriptionId = $receipt->productId;
            $token = $receipt->purchaseToken;
    
            $service = new AndroidPublisher($client);
    
            try {
                $subscriptionDetails = $service->purchases_subscriptions->get($packageName, $subscriptionId, $token);
                $raw_data = [];
    
                if (isset($subscriptionDetails->expiryTimeMillis)) {
                    $expiryTimeMillis = $subscriptionDetails->expiryTimeMillis / 1000; // Convert to seconds
                    $currentTime = time();

                    $raw_data['membershipEnable'] = $expiryTimeMillis > $currentTime ? 'yes' : 'no';
                    $raw_data['expires_date_pst'] = date('Y-m-d h:i A', $expiryTimeMillis);
                    $raw_data['product_id'] = $subscriptionId; // Use subscription ID as product ID
                    $raw_data['is_trial_period'] = isset($subscriptionDetails->paymentState) && $subscriptionDetails->paymentState == 2;

                    return $raw_data;
                } else {
                    return $this->successResponse('Android User not subscribed', []);
                }
            } catch (\Exception $e) {
                \Log::error('Subscription validation failed: ' . $e->getMessage());
                return $this->errorResponse('Failed to validate subscription', 'internal_server_error', 500);
            }
        } else {
            return $this->successResponse('Android User not subscribed', $receipt);
        }
    }

    
    
    
    

    
    
       
    
    private function checkIos($receipt){
        
        $validator = new iTunesValidator(iTunesValidator::ENDPOINT_PRODUCTION);
        $receiptBase64Data = $receipt;
        $data=[];
        try {
            $response = $validator->setReceiptData($receiptBase64Data)->validate();
            $sharedSecret = 'acbed3e516ff425db8e726d3efd153fa'; 
            $response = $validator->setSharedSecret($sharedSecret)->setReceiptData($receiptBase64Data)->validate();
              
            if ($response->isValid()) {
              $raw_data = $response->getRawData();
              $receipt = current($raw_data['latest_receipt_info']);
                $data['membershipEnable'] = '';
                $data['expires_date_pst'] = date('Y-m-d h:i A', ($receipt['expires_date_ms']/1000));
                $data['product_id'] = $receipt['product_id'];
                $data['is_expired'] = $data['expires_date_pst'] >= date("Y-m-d h:i A") ? 'no' : 'yes';
                $data['platform'] = 'ios';
                $renewalinfo = $raw_data['pending_renewal_info'];
                foreach($renewalinfo as $renewal){
                    if(!empty($renewal) && isset($renewal['is_in_billing_retry_period']) && $renewal['is_in_billing_retry_period'] == '1' && $data['is_expaired'] == 'yes'){
                        $data['membershipEnable'] = 'yes';
                    }
                  else{
                      $data['membershipEnable'] = 'no';
                  }
                }
              return $this->successResponse('Ios User subscribed',200, $data);  
            }else{
                return $this->successResponse('Invalid response',200, $response);
            } 
        } catch (Exception $e) {
          return $this->errorResponse($e->getMessage(), 'internal_server_error', 500);  
        }

    }
    
    
    public function checksubscription($current_user){
        try{
            $data = Subscription::where('user_id', $current_user)->first();
            
            if ($data) {
                if ($data->platform == "web") {
                    $data->product_id = "free";
                    $data->is_expired = $data->expiry_date >= date("Y-m-d h:i A") ? 'no' : 'yes'; 
                    $data->makeHidden(['id', 'user_id', 'platform', 'subscription', 'receipt']);
                } else {
                    $data = (object) $this->subscribe_validation()->original['data'];
                }
            } else {
                $data = (object)[];
            }
            
            return $data;
            
        }catch (Exception $e) {
          return $this->errorResponse($e->getMessage(), 'internal_server_error', 500);  
        }
    }

    // private function sanitizeFileName($fileName) {
    //     // Replace any non-alphanumeric characters with an underscore
    //     $sanitizedFileName = preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName);
    //     return $sanitizedFileName;
    // }
    
    public function getCategory(){
         try {
            $getCategories = Category::orderBy('name','asc')->get();
            return $this->successResponse("Category fetched successfully.", 200, $getCategories);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), 'internal_server_error', 500);
        }
    }
    
    
    public function createDafultablum(Request $request){
        
        $getUsers = User::whereIn('role_id', ['2', '3'])->get();
        foreach ($getUsers as $getUser) {
            $getAlbum = Album::where('user_id', $getUser->id)
                             ->where('album_name', 'Saved Posts')
                             ->first();
            if (!$getAlbum) {
                $album = new Album();
                $album->album_name = "Saved Posts";
                $album->user_id = $getUser->id;
                $album->save();
            }
        }
        return "Successfully created default albums where needed.";
    }
    
    
    public function deceasedReport(Request $request){
        $validator = Validator::make($request->all(), [
            'deceased_by' => 'required',
            'user_id' => 'required',
        ]);

        if($validator->fails()){
            $errors = $validator->errors();
            foreach ($errors->all() as $key => $value) {
               return $this->errorResponse(ucfirst($value), 'validation_error', 400);
            }
        }
        try{
            
            $userId = Auth::id();    
            
            $getdata = DeceasedReport::where(['user_id'=>$request->user_id, 'deceased_by'=>$request->deceased_by, 'report_by'=>$userId])->first();
            if($getdata){
               return $this->successResponse('This deceased has already been reported',200,null);   
            }
           
           
            $data = new DeceasedReport();
            $data->user_id = $request->user_id;
            $data->deceased_by = $request->deceased_by;
            $data->report_by = $userId;
            $data->save();

           
            if(!$data){
                return $this->errorResponse("The report was not saved. Please try again", 'report_not_saved', 200);
            }
            return $this->successResponse('The report has been saved successfully',200, $data);
            
        }catch(\Exception $exception){
            return $this->errorResponse("Something went wrong. Please check your request.", 'something_wrong', 500);
        }
    }
    
    
    public function testImage(Request $request){
        $user_id = Auth::id();
        $file = $request->file('image');
        $res = $this->UploadImage->saveMedia($file,$user_id);

        return $res;
    }
    
    
    //new methods

    public function updateUserProfile(Request $request){
        try{
            $current_user = Auth::user()->id;
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'dob' => 'required|date|before:' . Carbon::now()->subYears(18)->toDateString(), // Check if the DOB is older than 17
                'gender' => 'required|in:male,female,other,notosay',
                'is_private' => 'sometimes|required|in:0,1'

            ]);
    
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    $response = $value;
                    return response()->json(["message" => $response, "status" => "failed", "data" => null], 400);
                }
            }
            
            $getUser = User::where('id',$current_user)->first();
            $getUser->first_name = Str::ucfirst($request->first_name);
            $getUser->last_name = Str::ucfirst($request->last_name);
            $getUser->dob = $request->dob;
            $getUser->gender = $request->gender;
            if($request->is_private){
                $getUser->is_private = $request->is_private;
            }
            if($request->description){
                $getUser->description = $request->description;
            }
           
            if($request->image){
                
                $file = $request->file('image');
                $res = $this->UploadImage->saveMedia($file,$current_user);
                $getUser->image = $res;
                
            }
           $getUser->save();
            if(!$getUser){
                return response()->json(["message" => "Profile not update successfully", "status" => "failed", "data" => null], 400);
            }
            
            return response()->json(["message" => "Profile update successfully", "status" => "success", "data" =>$getUser], 200);
            
        } catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "data" => null], 500);
        }
    }
    public function updateUserPhoneNumber(Request $request){
        try{
            $current_user = Auth::user()->id;
            $validator = Validator::make($request->all(), [
                'country_code' => 'required',
                'phone' => 'required|unique:users,phone',
                'agree_on_receiving' => 'required|in:0,1',

            ]);
    
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    $response = $value;
                    return response()->json(["message" => $response, "status" => "failed", "data" => null], 400);
                }
            }
            
            $getUser = User::where('id',$current_user)->first();
            $getUser->phone = $request->phone;
            $getUser->agree_on_receiving = $request->agree_on_receiving;
            $getUser->country_code = $request->country_code;
            $getUser->save();


//             $token = JWTAuth::fromUser($getUser);
//             if($token){
//                 $getUser->token = $token;
//             }
             $is_exist = DeviceDetail::where('user_id',$getUser->id)->first();
             $getUser['is_first_login'] = ($is_exist) ? false : true ;
            
//            if(!$getUser->wasChanged()){
//                return response()->json(["message" => "Phone Number not update successfully", "status" => "failed", "data" => null], 400);
//            }
            
            return response()->json(["message" => "Phone Number update successfully", "status" => "success", "data" =>$getUser], 200);
            
        } catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "data" => null], 500);
        }
    }

    public function giveAccess(Request $request){
        try{
         
            $current_user = Auth::user()->id;
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exits:users,email',
            ]);
    
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    $response = $value;
                    return response()->json(["message" => $response, "status" => "failed", "data" => []], 400);
                }
            }
            
            $getUser = User::where('id',$current_user)->first();
            $getUser->first_name = Str::ucfirst($request->first_name);
            $getUser->last_name = Str::ucfirst($request->last_name);
            $getUser->dob = $request->dob;
            $getUser->gender = $request->gender;
            if($request->image){
                
                $file = $request->file('image');
                $res = $this->UploadImage->saveMedia($file,$current_user);
                $getUser->image = $res;
                
            }
            $getUser->save();
            
            
            if(!$getUser){
                return response()->json(["message" => "Profile not update successfully", "status" => "failed", "data" => []], 400);
            }
            
            return response()->json(["message" => "Profile update successfully", "status" => "success", "data" =>$getUser], 200);
            
        } catch (JWTException $exception) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', "data" => []], 500);
        }
    }





}


