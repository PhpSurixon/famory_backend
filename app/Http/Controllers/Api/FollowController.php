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
        try {
            $limit = (int) $request->get('limit', 30);
            $page  = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $authUser = Auth::user();

            $query = Follow::where('following_id', $authUser->id)
                            // ->where('status', 'approved')
                            ->with('follower:id,first_name,last_name,email,username,image');

            $totalUsers = $query->count();

            $followers = $query->orderBy('id','desc')
                               ->skip($offset)
                               ->take($limit)
                               ->get()
                               ->pluck('follower'); // only return user info

            $data = [
                'user_id'     => $authUser->id,
                'count'       => $totalUsers,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($totalUsers / $limit),
                'users'       => $followers
            ];

            return response()->json([
                'message' => 'Followers fetched successfully',
                'status'  => "success",
                'data'    => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! ".$e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }


    public function following(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 30);
            $page  = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $authUser = Auth::user();

            $query = Follow::where('follower_id', $authUser->id)
                            // ->where('status', 'approved')
                            ->with('following:id,first_name,last_name,email,username,image');

            $totalUsers = $query->count();

            $following = $query->orderBy('id','desc')
                               ->skip($offset)
                               ->take($limit)
                               ->get()
                               ->pluck('following'); // only return user info

            $data = [
                'user_id'     => $authUser->id,
                'count'       => $totalUsers,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($totalUsers / $limit),
                'users'       => $following
            ];

            return response()->json([
                'message' => 'Following fetched successfully',
                'status'  => "success",
                'data'    => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! ".$e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }





}
