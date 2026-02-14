<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustedUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use DB;

class TrustedUserController extends Controller
{
    use OneSignalTrait;
    public function __construct()
    {
        //
    }

    public function index(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 30);
            if ($limit <= 0) $limit = 30;              // avoid division by zero
            $limit = min($limit, 100);                 // optional cap

            $page  = (int) $request->get('page', 1);
            if ($page <= 0) $page = 1;

            $offset   = ($page - 1) * $limit;
            $search = $request->get('search');
            $user_id = $request->get('user_id');
            $status = $request->get('status');

            $authId = Auth::id();
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);

            if (!empty($user_id)) {
                $checkUser = User::find($user_id);
                if (!$checkUser) {
                    return response()->json([
                        'message' => 'User not found',
                        'status' => 'failed'
                    ], 404);
                }
                $get_follower_user_id = $user_id;
            } else {
                $get_follower_user_id = $authId;
            }

            $query = TrustedUser::where('user_id', $get_follower_user_id)
                ->whereNotIn('trusted_user_id', $blockedUserIds) // ✅ fix: filter trusted_user_id, not owner id
                ->where('status','!=','rejected')
                ->with('trustedUser:id,first_name,last_name,email,username,image');

            if (!empty($search)) {
                $query->whereHas('trustedUser', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }
            // ✅ Apply status filter
            if (!empty($status)) {
                $query->where('status', $status);
            }

            $totalUsers = $query->count();

            $trusted_users = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

            $users = $trusted_users->map(function ($trust) use ($s3BaseUrl) {
                $trusted = $trust->trustedUser; // ✅ use relation directly
                if (!$trusted) {
                    return null; // skip later
                }
                  
                return [
                    'id'         => $trust->id,
                    'user_id'    => $trusted->id,
                    'first_name' => $trusted->first_name,
                    'last_name'  => $trusted->last_name,
                    'email'      => $trusted->email,
                    'username'   => $trusted->username,
                    // 'image'      => $trusted->image ? $s3BaseUrl . $trusted->image : null,
                    'image'      => $trusted->image ? $trusted->image : null,
                    'status'     => $trust->status,
                ];
            })
                ->filter()   // ✅ removes null records
                ->values();

            $data = [
                'user_id'     => (int) $get_follower_user_id,
                'count'       => $totalUsers,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($totalUsers / $limit),
                'users'       => $users
            ];

            return response()->json([
                'message' => 'Trusted Users fetched successfully',
                'status'  => "success",
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

    public function trustedByOthers(Request $request)
    {
        try {
            $limit = (int) $request->get('limit', 30);
            if ($limit <= 0) $limit = 30;
            $limit = min($limit, 100);

            $page  = (int) $request->get('page', 1);
            if ($page <= 0) $page = 1;

            $offset = ($page - 1) * $limit;
            $search = $request->get('search');
            $status = $request->get('status');

            $authId = Auth::id();
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
           
            // 🔹 Query: who added me as trusted_user_id
            $query = TrustedUser::where('trusted_user_id', $authId)
                ->whereNotIn('user_id', $blockedUserIds)
                ->where('status','!=','rejected')
                ->with('owner:id,first_name,last_name,email,username,image');

            if (!empty($search)) {
                $query->whereHas('owner', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // 🔹 Apply status filter
            if (!empty($status)) {
                $query->where('status', $status);
            }

            $totalUsers = $query->count();

            $trusted_users = $query->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

            $users = $trusted_users->map(function ($trust) use ($s3BaseUrl) {
                $user = $trust->owner; // ✅ relation for sender
                if (!$user) return null;

                return [
                    'id'         => $trust->id,
                    'user_id'    => $user->id,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'email'      => $user->email,
                    'username'   => $user->username,
                    // 'image'      => $user->image ? $s3BaseUrl . $user->image : null,
                    'image'      => $user->image ? $user->image : null,
                    'status'     => $trust->status,
                ];
            })
            ->filter()
            ->values();

            $data = [
                'user_id'     => $authId,
                'count'       => $totalUsers,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($totalUsers / $limit),
                'users'       => $users
            ];

            return response()->json([
                'message' => 'Trusted By Others fetched successfully',
                'status'  => "success",
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }



    public function sendManageRequestOLD(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'trusted_user_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            $id = $request->trusted_user_id;

            $targetUser = User::findOrFail($id);
            $authUser = Auth::user();

            if ($targetUser->id === $authUser->id) {
                return response()->json(['message' => "You can't send yourself", 'status' => 'failed'], 400);
            }
            $existing = TrustedUser::where('user_id', $authUser->id)
                ->where('trusted_user_id', $targetUser->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existing) {
                return response()->json(['message' => 'Already Request for this user'], 400);
            }

            $status = 'pending';

            $createFollow=  TrustedUser::create([
                'user_id' => Auth::id(),
                'trusted_user_id' => $targetUser->id,
                'status' => $status,
            ]);

            
            $msg = "Trusted user added successfully (pending acceptance) from {$targetUser->first_name}";
            $this->notifyMessage($authUser, $targetUser->id, $authUser->id, "trust_request"); // pending request
        
            return response()->json(['message' => $msg, 'status' => 'success','data'=>$createFollow], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => "Something Went Wrong! " . $e->getMessage(), 'status' => 'failed'], 400);
        }
    }

    public function sendManageRequest(Request $request)
    {
        try {
            $ids = $request->input('trusted_user_id');

            // Normalize input into array
            if (is_string($ids)) {
                // Case 1: Comma-separated string "1,2,3"
                if (str_contains($ids, ',')) {
                    $ids = array_map('intval', explode(',', $ids));
                }
                // Case 2: Stringified JSON array "[1,2,3]"
                elseif (str_starts_with($ids, '[') && str_ends_with($ids, ']')) {
                    $decoded = json_decode($ids, true);
                    $ids = is_array($decoded) ? $decoded : [$ids];
                }
                // Case 3: Single number string "5"
                elseif (is_numeric($ids)) {
                    $ids = [(int) $ids];
                }
            }

            // Always array
            $ids = (array) $ids;
            $request->merge(['trusted_user_id' => $ids]);

            // Validate
            $validator = Validator::make($request->all(), [
                'trusted_user_id'   => 'required|array|min:1',
                'trusted_user_id.*' => 'integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authUser = Auth::user();
            $created = [];

            foreach ($ids as $id) {
                if ($id === $authUser->id) {
                    continue; // skip self
                }

                $targetUser = User::find($id);
                if (!$targetUser) {
                    continue; // skip invalid ids
                }

                // Check if already requested or approved
                $existing = TrustedUser::where('user_id', $authUser->id)
                    ->where('trusted_user_id', $targetUser->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->first();

                if ($existing) {
                    continue; // skip already requested
                }

                // Create new trusted user request
                $createFollow = TrustedUser::create([
                    'user_id'        => $authUser->id,
                    'trusted_user_id'=> $targetUser->id,
                    'status'         => 'pending',
                ]);

                $created[] = $createFollow;

                // Send notification
                $this->notifyMessage(
                    $authUser,
                    $targetUser->id,
                    $createFollow->id,
                    "trust_request"
                );
            }

            if (empty($created)) {
                return response()->json([
                    'message' => 'No new trusted requests were created (maybe duplicates/self).',
                    'status'  => 'failed'
                ], 400);
            }

            return response()->json([
                'message' => count($created) > 1
                    ? 'Trusted users added successfully (pending acceptance)'
                    : 'Trusted user added successfully (pending acceptance)',
                'status' => 'success',
                'data'   => $created
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }


    public function requestUpdateStatus(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'status' => 'required|in:accepted,rejected',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            $authUser = Auth::user();

            $trusted = TrustedUser::where('id', $request->id)
                ->where('trusted_user_id', Auth::id())
                ->where('status', "pending")
                ->first();

            if (empty($trusted)) {
                return response()->json(['message' => 'No pending request found', 'status' => 'failed'], 404);
            }
            $status = $request->status == 'accepted' ? 'accepted' : 'rejected';
            $msg = $request->status == 'accepted' ? 'Trust request accepted' : 'Trust request rejected';

            $trusted->status = $status;
            $trusted->save();

            if ($status === 'accepted') {
                
                $this->notifyMessage($authUser,$trusted->user_id,$trusted->id,"trust_accept");
            }
            
            if ($status === 'rejected') {
                
                $this->notifyMessage($authUser,$trusted->user_id,$trusted->id,"trust_reject");
            }

            return response()->json([
                'message' => $msg,
                'status'  => 'success',
                'data' => $trusted,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => "Something Went Wrong! " . $e->getMessage(), 'status' => 'failed'], 400);
        }
    }

    public function destroyOLD(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
            $authId = Auth::id();
            $trustestID = $request->input('id'); // get id from POST body

            $trustedData = TrustedUser::where('id', $trustestID)
                                        ->where('user_id', $authId)
                                        ->first();

            if (!$trustedData) {
                return response()->json([
                    'message' => 'Trusted User not found or unauthorized',
                    'status' => 'failed'
                ], 404);
            }

            $trustedData->delete();

            return response()->json([
                'message' => 'Trusted User deleted successfully',
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('id');

            // Normalize "id" input into array
            if (is_string($ids)) {
                // Case 1: Comma-separated string "1,2,3"
                if (str_contains($ids, ',')) {
                    $ids = array_map('intval', explode(',', $ids));
                }
                // Case 2: Stringified JSON array "[1,2,3]"
                elseif (str_starts_with($ids, '[') && str_ends_with($ids, ']')) {
                    $decoded = json_decode($ids, true);
                    $ids = is_array($decoded) ? $decoded : [$ids];
                }
                // Case 3: Single number string "5"
                elseif (is_numeric($ids)) {
                    $ids = [(int) $ids];
                }
            }

            // Always cast to array
            $ids = (array) $ids;

            // Merge normalized ids back to request
            $request->merge(['id' => $ids]);

            // Validate
            $validator = Validator::make($request->all(), [
                'id'   => 'required|array|min:1',
                // 'id.*' => 'integer|exists:trusted_users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $authId = Auth::id();

            // Get records that belong to the logged-in user
            $trustedData = TrustedUser::whereIn('id', $ids)
                                      ->where('user_id', $authId)
                                      ->get();

            if ($trustedData->isEmpty()) {
                return response()->json([
                    'message' => 'Trusted User(s) not found or unauthorized',
                    'status'  => 'failed'
                ], 404);
            }

            // Delete all
            TrustedUser::whereIn('id', $ids)
                       ->where('user_id', $authId)
                       ->delete();

            return response()->json([
                'message' => count($ids) > 1
                    ? 'Trusted Users deleted successfully'
                    : 'Trusted User deleted successfully',
                'status'  => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }

    public function getManageUserList(Request $request)
    {
        try {
            // 🔹 Validate request
            $validator = Validator::make($request->all(), [
                'marked_by_user_id' => 'required|exists:users,id',
                'marked_to_user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status'  => 'failed'
                ], 400);
            }

            $s3BaseUrl = 'https://famorys3.s3.amazonaws.com';

            // 🔹 Helper for image prefix
            $prefixIfNeeded = function ($path) use ($s3BaseUrl) {
                if (empty($path)) return null;
                if (preg_match('/^https?:\/\//', $path)) return $path;
                return rtrim($s3BaseUrl, '/') . '/' . ltrim($path, '/');
            };

            // 🔹 Common function to format user
            $formatUser = function ($user) use ($prefixIfNeeded) {
                if (!$user) return null;
                return [
                    'user_id'    => $user->id,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name ?? null,
                    'email'      => $user->email ?? null,
                    'phone'      => $user->phone ?? null,
                    'image'      => $prefixIfNeeded($user->image),
                ];
            };

            // 🔹 Marked by user
            $marked_by_user = User::select('id','first_name','last_name','email','phone','image')
                                ->where('id', $request->marked_by_user_id)
                                ->whereNull('deleted_at')
                                ->where('role_id', 2)
                                ->first();

            $marked_by_user_formatted = $formatUser($marked_by_user);

            // 🔹 Marked to user
            $marked_to_user = User::select('id','first_name','last_name','email','phone','image')
                                ->where('id', $request->marked_to_user_id)
                                ->whereNull('deleted_at')
                                ->where('role_id', 2)
                                ->first();

            $marked_to_user_formatted = $formatUser($marked_to_user);

            // 🔹 Manage user list (trusted users of marked_to_user)
            $mark_to_manage_user_list = TrustedUser::where('user_id', $request->marked_to_user_id)
                                                    ->where('status', 'accepted')
                                                    ->with('trustedUser:id,first_name,last_name,email,phone,image')
                                                    ->get();

            $manage_users = $mark_to_manage_user_list->map(function ($tu) use ($formatUser) {
                return $formatUser($tu->trustedUser);
            })->filter()->values();

            // 🔹 Final response data
            $data = [
                'marked_by_user'       => $marked_by_user_formatted,
                'marked_to_user'       => $marked_to_user_formatted,
                'mark_to_manage_user_list' => $manage_users,
            ];

            return response()->json([
                'message' => 'Marked Deceased Manage User List retrieved successfully',
                'status'  => 'success',
                'data'    => $data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something went wrong! " . $e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }


}
