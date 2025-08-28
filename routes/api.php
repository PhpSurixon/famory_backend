<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\BurialInfoController;
use App\Http\Controllers\StripeSubscriptionController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    //new routes
    // Route::post('/register', [ApiController::class, 'register']);
    // Route::post('/verify-otp-email', [ApiController::class, 'verifyEmailOTP']);
    // Route::post('/resend-otp', [ApiController::class, 'resendOTP']);
    //Route::post('/change-password', [ApiController::class ,'changePassword']);//reset password


    // Signup
    Route::post('/signup', [ApiController::class, 'signup']);
    Route::post('/login', [ApiController::class, 'login']);
    Route::post('/forgot-password', [ApiController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ApiController::class, 'verifyOtp']);
    Route::post('/reset-password', [ApiController::class ,'resetPassword']);


    //for reset new api call resend otp 
        
    Route::middleware(['jwt.verify'])->group(function () { 
        // User
        Route::post('/update-profile', [ApiController::class, 'updateProfile']);
        Route::post('/device-details', [ApiController::class, 'storeDeviceDetails']);
        Route::get('/logout', [ApiController::class, 'logout']);
        Route::get('/about-us', [ApiController::class, 'aboutUs']);
        Route::get('/f-a-q', [ApiController::class, 'faq']);
        Route::get('/tutorial', [ApiController::class, 'tutorial']);
        Route::post('/contact-us',[ApiController::class, 'contactUs']);
        Route::post('/verify-password',[ApiController::class, 'verifyPassword']);
        Route::post('/add-about-us',[ApiController::class, 'addAboutUs']);
        Route::post('/live-user-status',[ApiController::class, 'blockedUser']);
        Route::get('/get-blocked-user',[ApiController::class, 'getBlockedUsers']);
        //Group
        Route::post('/invite-guest-user',[UserController::class, 'inviteGuestUser']);
        Route::post('/add-group',[UserController::class, 'addGroup']);
        Route::post('/group/{id}',[UserController::class, 'editGroup']);
        Route::post('/delete-group/{id}',[UserController::class, 'deleteGroup']);
        
        Route::post('/user-invite',[UserController::class, 'inviteUser']);
        Route::get('/all-group',[UserController::class, 'getAllGroup']);
       
        Route::post('/accept-request',[UserController::class, 'acceptRequest']);
        Route::get('/my-group',[UserController::class, 'allMyGroup']);
        
        Route::get('/get-family-friends',[UserController::class, 'getFamilyAndFriends']);
        Route::post('/user-group-list',[UserController::class, 'userGroupList']);
        Route::post('/move-user-group',[UserController::class, 'moveUserGroup']);
        
        
        Route::post('/add-member-group',[UserController::class, 'addMemberToGroup']);
        Route::get('/get-group-member',[UserController::class, 'getGroupMember']);
        Route::post('/delete-connection-request',[UserController::class, 'deleteConnectionRequest']);
        
        // album
        Route::post('/album',[ApiController::class, 'createAlbum']);
        Route::get('/get-album',[ApiController::class, 'getAllAlbums']);
        Route::get('/get-album/{id}',[ApiController::class, 'getAlbum']);
        Route::post('/add-album-post',[ApiController::class, 'addAlbumPost']);
        Route::post('/delete-album-post',[UserController::class, 'deleteAlbumPost']);
        // Route::post('/album/{id}', [ApiController::class, 'editAlbum']);
        Route::post('/album/{albumId}', [ApiController::class, 'editAlbum']);


        //Post
        Route::post('/create-post',[PostController::class, 'createPost']);
        Route::post('/create-post/{id}',[PostController::class, 'editPost']);
        Route::post('/delete-post/{id}',[PostController::class, 'deletePost']);
        
        Route::get('/get-famory-tag-post',[PostController::class,'getFamoryTagPost']);
        Route::post('/follow-unfollow',[PostController::class, 'followUnfollow']);
        Route::post('/like-unlike',[PostController::class, 'likeUnlike']);
        
        
        
        Route::get('/test',[PostController::class, 'scheduleReoccurring']);
        //burial_infouser-invite
        Route::post('/add-burial-info',[BurialInfoController::class, 'createBurialInfo']);
        //add-last-words
        Route::post('/add-last-words',[BurialInfoController::class, 'AddLastWords']);
        Route::get('/get-user-info/{user_id}', [ApiController::class, 'getUserById']);
        Route::post('/user-live-status', [ApiController::class, 'userLiveStatus']);//  user live status
        Route::post('/deleteAccount',[ApiController::class,'deleteAccount']);
        //google storage api 
        Route::post("/upload-file",[PostController::class, "uploadFileToCloud"]);
        //auth user group data
        Route::get('get-user-groups', [UserController::class, 'getUserGroups']);
        
        // Notification
        Route::post('/get-notification', [ApiController::class, 'getNotificationList']);
        Route::post('/delete-notification', [ApiController::class, 'deleteNotification']);
        Route::post('/count-notification', [ApiController::class, 'countNotification']);
        
        //Report Post
        Route::post('/add-report-post', [PostController::class, 'addReportPost']);
        Route::post('/add-stop-seeking-post', [PostController::class, 'addStopSeekingPost']);
        Route::get('/get-burial-pdf', [ApiController::class, 'getBurailpdf']);
        
   
      // ------------------------------------------------------------------------------------------------

     // this is a comment added for test.

     Route::get('all-available-users', [ApiController::class, 'getAllUsers']);
     Route::post('/check-family-tag', [ApiController::class, 'checkFamilyTag']); // check if this is available to be created ios side
     Route::post('/create-famory-tag', [ApiController::class, 'createFamoryTag']);
     Route::post('/create-famory-tagV2', [ApiController::class, 'createFamoryTagV2']); // new api
     Route::post('/create-famory-tagV3', [ApiController::class, 'createFamoryTagV3']); // new api with all fields from figma // (1)
     Route::post('/create-family-tag-post', [ApiController::class, 'createFamoryTagWithPost']); //create famory tag with post
     Route::post('/create-famory-tagV4', [ApiController::class, 'createTagWithCollaborators_ios']);
     Route::post('/create-famory-tagV4_ios', [ApiController::class, 'createTagWithCollaborators_ios']);
     Route::post('/update-famory-tag/{family_tag_id}', [ApiController::class, 'updateFamoryTag']); // new api 
 
     Route::post('/create-buy-new-tag', [ApiController::class, 'createBuyNewTag']); // new api
 
     Route::post('/get-famory-tags', [ApiController::class, 'getFamoryTag']); // (2)
     Route::post('/get-famory-tagsV2', [ApiController::class, 'getFamoryTagV2']); // (changes added collaborator count)
     Route::post('/get-tag-info/{family_tag_id}', [ApiController::class, 'getTagInfo']); // get all the details related to this tag
 
     Route::post('/get-famory-tags-with-searchV2', [ApiController::class, 'getFamoryTagWithSearchV2']);
     Route::post('/get-famory-tags-first-five', [ApiController::class, 'getFamoryTagFirstFive']);
 
     // Route collaborators related Api's
     Route::get('tag/{tag_id}/collaborators', [ApiController::class, 'getCollaborators']); // (4)
     Route::get('tag/{tag_id}/collaborators/first-five', [ApiController::class, 'getFirstFiveCollaborators']); // (3)
 
     Route::post('tag/{tag_id}/collaborators/invite', [ApiController::class, 'inviteCollaborator']); // Invite new collaborator
     Route::post('tag/{tag_id}/collaborators/invite-multiple', [ApiController::class, 'inviteMultipleCollaborators']); // Invite multiple new collaborator
     Route::get('tag/{tag_id}/available-users', [ApiController::class, 'getAvailableUsers']); // ?search=jenny smith  ?search=jenny@example.com  // Fetch available users which are not invited for particular tag.
 
 
     Route::delete('tag/{tag_id}/collaborators/{collaborator_user_id}', [ApiController::class, 'deleteCollaborator']);
     Route::delete('tag/{tag_id}/collaboratorsV2/{collaborator_id}', [ApiController::class, 'deleteCollaboratorV2']);
     Route::patch('tag/{tag_id}/collaborators/{collaborator_user_id}/restore', [ApiController::class, 'restoreCollaborator']);
 
     Route::post('tag/{tag_id}/collaborators/request', [ApiController::class, 'requestCollaboratorAccess']); // request for tag access to the owner
     Route::get('tag-collaborators/requests', [ApiController::class, 'getAllReceivedCollaboratorRequests']); //all invited request shown to the new users
     Route::get('collaborator/requests/all', [ApiController::class, 'getAllReceivedCollaboratorRequestsAll']); //all invited request shown to the new users handles both invitations (where the user is invited to collaborate on a tag) and access requests (where someone has requested access to the user's tag)
     Route::get('tag-collaborators-first-five/requests', [ApiController::class, 'getFirstFiveReceivedCollaboratorRequests']);
 
     Route::post('tag/{tag_id}/collaborators/request', [ApiController::class, 'requestCollaboratorAccess']); // send request to tag owner that you want to have access
 
     Route::get('tag-invites', [ApiController::class, 'getTagRequests']); // here all the request will be shown to the owner
     Route::get('tag-invites-first-five', [ApiController::class, 'getTagRequestsFirstFive']);
 
     Route::post('tag-collaborators/update-status', [ApiController::class, 'updateCollaboratorStatus']);
 
 
    //  Route::get('tag-collaborators/saved-tags', [ApiController::class, 'getSavedTags']); //list of saved tags
    //  Route::get('/saved-tags-with-search', [ApiController::class, 'getSavedTagsWithSearch']);
     Route::post('/save-tag', [ApiController::class, 'saveTag']);
     Route::get('/saved-tags', [ApiController::class, 'getSavedTagsV2']);

     Route::get('/saved-tags-first-5', [ApiController::class, 'getFirstFiveSavedTagsV2']); // this is taking issue
     Route::delete('/saved-tags/{id}', [ApiController::class, 'deleteSavedTag']);
     //Route::get('tag-collaborators/saved-tags/first-five', [ApiController::class, 'getFirstFiveSavedTags']);
 
     // ------------------------------------------------------------------------------------------------
 



         

        Route::post('/get-trusted-companies',[ApiController::class,'getTrustedCompanies']);
        Route::post('/get-ads',[ApiController::class,'getAds']);
        Route::post('/update-ads-count',[ApiController::class,'updateAdsSeeCount']);
        Route::get('/get-subscription',[ApiController::class,'getSubscriptionList']);
        Route::post('/subscribe',[ApiController::class,'subscribe']);
        Route::get('/subscribe_validation',[ApiController::class,'subscribe_validation']);
        
        Route::get('testing', [UserController::class, 'test']);
        Route::get('/get-category',[ApiController::class,'getCategory']);
        
        Route::post('/deceased-report',[ApiController::class, 'deceasedReport']);
        Route::post('/test-image', [ApiController::class, 'testImage']);
        
        // Block User 
        Route::middleware(['checkBlocked'])->group(function () { 
            Route::get('/get-post',[PostController::class, 'getPost']);
            Route::get('/family-member',[UserController::class, 'allMyFamilyMember']);
            Route::post('/search',[UserController::class, 'search']);
            Route::get('/connection-request',[UserController::class, 'getConnectionRequest']);
            Route::get('/get-following-user',[PostController::class, 'getAllFollowingUser']);
            Route::post('/get-like-user',[PostController::class, 'getallLikeuser']);
            Route::get('/my-family',[UserController::class, 'myFamily']);
            Route::post('/get-profile', [ApiController::class, 'getProfile']);
        });
        
    });
    
    
    
    Route::post('/createDafultablum',[ApiController::class, 'createDafultablum']);
    Route::get('/terms-condition', [ApiController::class, 'terms']);
    Route::get('/privacy-policy', [ApiController::class, 'privacyPolicy']);
    
    Route::post('/userInformationForDeleteAC',[ApiController::class, 'userInformationForDeleteAC']);
    Route::post('/verifyEmail',[ApiController::class, 'verifyEmail']);
    Route::get('/cron-post-reoccurring',[PostController::class, 'runCronJobPost']);
    Route::get('/cron-check-user-live',[ApiController::class, 'cronCheckUserLive']);
    Route::get('/cron-renewal-subscription',[ApiController::class,'renewalsubscription']);
    Route::get('/sendDeceasedNotifications',[ApiController::class,'sendDeceasedNotifications']);
    Route::get('/renewalAdsSubscription',[ApiController::class,'renewalAdsSubscription']);

    Route::get('/test11111111111111111111111',function(){
        return response()->json(['test' => 'test']);
    });
    
    
    
    Route::get('/webhook/adSubscriptionRenewal',[StripeSubscriptionController::class,'adSubscriptionRenewal']);
    