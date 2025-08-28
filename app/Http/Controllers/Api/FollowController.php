<?php

// app/Http/Controllers/Api/FollowController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class FollowController extends Controller
{
    
    public function follow(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'following_id' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            $id = $request->following_id;

            $user = User::findOrFail($id);
            $authUser = Auth::user();

            if ($user->id === $authUser->id) 
            {
                return response()->json(['message' => "You can't follow yourself", 'status' => 'failed'], 400);
            }

            Follow::firstOrCreate([
                'follower_id' => Auth::id(),
                'following_id' => $user->id
            ]);

            return response()->json(['message' => "You are now following {$user->name}", 'status' => 'success'], 200);
            
        } catch (Exception $e) {
            return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }

    
    public function unfollow(Request $request)
    {
        try 
        {
            
            $validator = Validator::make($request->all(), [
                'following_id' => 'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            $id = $request->following_id;

            Follow::where('follower_id', Auth::id())
                    ->where('following_id', $id)
                    ->delete();

            return response()->json(['message' => "You unfollowed user"]);
            
        } catch (Exception $e) {
              return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }

    
    public function followers(Request $request)
    {
        $authUser = Auth::user();
        $followers = Follow::where('following_id', $authUser->id)
                            ->with('follower:id,first_name,last_name,email')
                            ->get()
                            ->pluck('follower');

        return response()->json([
            'user_id' => $authUser->id,
            'followers_count' => $followers->count(),
            'followers' => $followers
        ]);
    }

    
    public function following(Request $request)
    {
        $authUser = Auth::user();
        $following = Follow::where('follower_id', $authUser->id)
            ->with('following:id,first_name,last_name,email')
            ->get()
            ->pluck('following');

        return response()->json([
            'user_id' => $authUser->id,
            'following_count' => $following->count(),
            'following' => $following
        ]);
    }
}
