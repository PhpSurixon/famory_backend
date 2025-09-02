<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\FollowController;



Route::post('/register', [ApiController::class, 'register']);
Route::post('/verify-otp-email', [ApiController::class, 'verifyEmailOTP']);
Route::post('/resend-otp', [ApiController::class, 'resendOTP']);

Route::middleware(['jwt.verify'])->group(function () { 
 Route::put('/update-user-profile', [ApiController::class, 'updateUserProfile']);
 Route::put('/update-user-phonenumber', [ApiController::class, 'updateUserPhoneNumber']);

   //Post comment
 Route::post('post/{post_id}/comment', [PostController::class, 'commentPost']);
 Route::get('post/{post_id}/comment', [PostController::class, 'getCommentPost']);
 Route::delete('post/{post_id}/comment/{comment_id}', [PostController::class, 'deleteCommentPost']);

 //comment reply
 Route::post('post/{post_id}/comment/{comment_id}/reply', [PostController::class, 'addCommentReply'])->name('comment.reply.add');
 Route::get('post/{post_id}/comment/reply', [PostController::class, 'getCommentReply'])->name('comment.reply');

 //report comment
 Route::post('comment/{comment_id}/report', [PostController::class, 'reportComment'])->name('comment.report.add');
 Route::get('comment/{comment_id}/report', [PostController::class, 'getReportedComments'])->name('comment.report');

 //like comment
 Route::post('comment/{comment_id}/like', [PostController::class, 'likeComment']); // Like/Unlike a comment
Route::get('comment/{comment_id}/likes', [PostController::class, 'getLikes']); // Get likes for a comment


//contact
Route::get('user-contact', [ContactController::class, 'index']); // Get likes for a comment
Route::post('user-contact', [ContactController::class, 'store']); // Get likes for a comment
Route::put('user-contact/{contact_id}', [ContactController::class, 'update']);
Route::delete('user-contact/{contact_id}', [ContactController::class, 'destroy']);

//post save
Route::post('post/{post_id}/save', [PostController::class, 'savePost']);

// Follow,unfollow 
Route::post('follow', [FollowController::class, 'follow']);
Route::post('unfollow', [FollowController::class, 'unfollow']);
Route::post('follow/request-status', [FollowController::class, 'respondToRequest']);
Route::get('followers-list', [FollowController::class, 'followers']);
Route::get('following-list', [FollowController::class, 'following']);
Route::get('follow/pending', [FollowController::class, 'pendingRequests']);


Route::get('user-list', [UserController::class,'userList']);


});
