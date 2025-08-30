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
            return response()->json(['message' => "You unfollowed user", 'status' => 'success'], 200);
            
        } catch (Exception $e) {
              return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }


    public function followers(Request $request)
    {
        try 
        {
            $authUser = Auth::user();

                // Custom pagination params
                $limit = (int) $request->get('limit', 1); // default 10
                $page  = (int) $request->get('page', 1);   // default 1
                $offset = ($page - 1) * $limit;

                // Total followers count
                $totalFollowers = Follow::where('following_id', $authUser->id)->count();
                // Fetch paginated followers
                $followers = Follow::where('following_id', $authUser->id)
                                    ->with('follower:id,first_name,last_name,email')
                                    ->skip($offset)
                                    ->take($limit)
                                    ->get()
                                    ->pluck('follower');
                $data = [
                           'user_id'         => $authUser->id,
                           'followers_count' => $totalFollowers,
                           'page'            => $page,
                           'limit'           => $limit,
                           'total_pages'     => ceil($totalFollowers / $limit),
                           'followers'       => $followers
                        ];

                return response()->json([
                    'message' => 'Followers fetched successfully',
                    'status'  => "success",
                    'data'    => $data
                    
                ]);
            
        } catch (Exception $e) {
             return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
        
    }

    
    public function following(Request $request)
    {
        try 
        {
            $authUser = Auth::user();

            // Custom pagination params
            $limit  = (int) $request->get('limit', 10); // default 10
            $page   = (int) $request->get('page', 1);   // default 1
            $offset = ($page - 1) * $limit;

            // Total following count
            $totalFollowing = Follow::where('follower_id', $authUser->id)->count();

            // Fetch paginated following users
            $following = Follow::where('follower_id', $authUser->id)
                                ->with('following:id,first_name,last_name,email')
                                ->skip($offset)
                                ->take($limit)
                                ->get()
                                ->pluck('following');

            $data =    [
                           'user_id' => $authUser->id,
                           'following_count' => $totalFollowing,
                           'page' => $page,
                           'limit' => $limit,
                           'total_pages' => ceil($totalFollowing / $limit),
                           'following' => $following,
                       ];

            return response()->json([
                'message' => 'Following list fetched successfully',
                'status' => "success",
                'data'    => $data
                
            ]);
            
        } catch (Exception $e) {
            return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }
}
