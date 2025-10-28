<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\AlbumUser;
use App\Models\AlbumPost;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\FormatResponseTrait;
class AlbumMemberController extends Controller
{
    use FormatResponseTrait;
    public function getAlbumlist(Request $request)
    {
        try 
        {
            $user       = Auth::user();
            $perPage    = (int) $request->input('per_page', 10);
            $album_type = $request->get('album_type'); // my, collaborator, viewer

            $query = Album::query();

            if ($album_type === 'collaborator') 
            {
                // Albums where user is a collaborator
                $albumIds = AlbumUser::where('user_id', $user->id)
                                     ->where('role', 'collaborator')
                                     ->pluck('album_id')
                                     ->toArray();

                $query->whereIn('id', $albumIds);

            } elseif ($album_type === 'viewer') {
                // Albums where user is a viewer
                $albumIds = AlbumUser::where('user_id', $user->id)
                                     ->where('role', 'viewer')
                                     ->pluck('album_id')
                                     ->toArray();
                $query->whereIn('id', $albumIds);

            } else {
                // Default: user's own albums
                $query->where('user_id', $user->id);
            }

            // Order and paginate
            $albums = $query->withCount('posts')->orderBy('created_at', 'asc')->paginate($perPage);

            // If no albums found
            if ($albums->isEmpty()) {
                return $this->successResponse("No albums found", 200, $albums->items(), $albums);
            }

            // Success response
            return $this->successResponse("Albums retrieved successfully", 200, $albums->items(), $albums);

        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'status'  => 'failed'
            ], 500);
        }
    }

    public function addOrUpdateMember(Request $request)
    {
        try 
        {

            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
                'user_id'  => 'required|exists:users,id',
                'role'     => 'required|in:collaborator,viewer',
            ]);

            if ($validator->fails()) 
            {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
            $authUser = Auth::user();
            $getAlbum = Album::where('id',$request->album_id)->first();
            if($getAlbum->user_id != $authUser->id )
            {
                return response()->json(['message' => 'You are not Album Owner So you Can not Add and Update User', 'status' => 'failed'], 400);
            }

            $checkAlbumUser = AlbumUser::where('album_id',$request->album_id)
                                        ->where('user_id',$request->user_id)
                                        ->first();
            if($checkAlbumUser)
            {
                $checkAlbumUser->role = $request->role;
                $checkAlbumUser->save();

                $msg = 'Album User Roles Updated Successfully!'; 

            }else
            {
                 $insertData = [
                                 'album_id'=>$request->album_id,
                                 'user_id'=>$request->user_id,
                                 'role'=>$request->role,
                               ];
                $createData = AlbumUser::create($insertData);
                $msg = 'Album User Added Successfully!'; 
            }
            return response()->json(['message' => $msg, 'status' => 'success'], 200);
            
        } catch (Exception $e) {
             return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }

    public function removeMember(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
                'user_id'  => 'required|exists:users,id',
            ]);

            if ($validator->fails()) 
            {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
            $authUser = Auth::user();
            $getAlbum = Album::where('id',$request->album_id)->first();
            if($getAlbum->user_id != $authUser->id )
            {
                return response()->json(['message' => 'You are not Album Owner So you Can not Add and Update User', 'status' => 'failed'], 400);
            }

            $checkAlbumUser = AlbumUser::where('album_id',$request->album_id)
                                        ->where('user_id',$request->user_id)
                                        ->first();
            if($checkAlbumUser)
            {
                $checkAlbumUser->delete();
                $msg = 'Album User Deleted Successfully!'; 
            }
            return response()->json(['message' => $msg, 'status' => 'success'], 200);
            
        } catch (Exception $e) {
             return response()->json(['message' => "Something Went Wrong!", 'status' => 'failed'], 400);
        }
    }

    public function listMembers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:albums,id',
                'search'   => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $album = Album::findOrFail($request->album_id);

            // Only owner can see member list (optional rule)
            if ($album->user_id != $authUser->id) {
                return response()->json([
                    'message' => 'You are not the Album Owner, so you cannot view members.',
                    'status'  => 'failed'
                ], 403);
            }

            // Pagination parameters
            $limit  = (int) $request->get('limit', 30);
            $page   = (int) $request->get('page', 1);
            $offset = ($page - 1) * $limit;
            $search = $request->get('search');

            // Base query
            $query = AlbumUser::where('album_id', $album->id)
                ->with('user:id,first_name,last_name,email,username,image');

            // Search by user fields
            if (!empty($search)) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $totalMembers = $query->count();

            // Paginated results
            $albumMembers = $query->orderBy('id', 'desc')
                                  ->skip($offset)
                                  ->take($limit)
                                  ->get();

            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

            // Format data like follower list
            $members = $albumMembers->map(function ($member) use ($s3BaseUrl) {
                $user = $member->user;
                return [
                    'id'            => $member->id,
                    'user_id'       => $user->id,
                    'first_name'    => $user->first_name,
                    'last_name'     => $user->last_name,
                    'email'         => $user->email,
                    'username'      => $user->username,
                    'image'         => $user->image ? $s3BaseUrl . $user->image : null,
                    'role'          => $member->role, // collaborator/viewer
                ];
            });

            // Paginated meta response
            $data = [
                'album_id'     => (int) $album->id,
                'count'        => $totalMembers,
                'page'         => $page,
                'limit'        => $limit,
                'total_pages'  => ceil($totalMembers / $limit),
                'members'      => $members,
            ];

            return response()->json([
                'message' => 'Album members fetched successfully',
                'status'  => 'success',
                'data'    => $data
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }


}
