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
use Illuminate\Support\Facades\DB;
use App\Traits\FormatResponseTrait;
use Illuminate\Support\Facades\Mail;

use App\Models\UserProfile;
use App\Models\PasswordReset;
use App\Models\Contact;
use App\Models\UserGroup;
use App\Models\AssignUserGroup;
use App\Models\ConnectionRequest;
use App\Models\FamilyMember;
use App\Models\MemberGroup;
use App\Models\Album;
use App\Models\AlbumPost;
use App\Models\InviteGuestUser;
use App\Traits\OneSignalTrait;
use App\Mail\SendMailreset;
use App\Mail\InviteGuestUserMail;
use App\Models\Notification;
use App\Models\Follow;
use App\Services\UploadImage;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
//require '/home/ubuntu/public_html/backend/vendor/vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

class UserController extends Controller
{
    use FormatResponseTrait;
    use OneSignalTrait;

    protected $storageClient;
    
    
    public function __construct(UploadImage $UploadImage)
    {
        $this->UploadImage = $UploadImage;
    }
    
    
    
    private function sanitizeFileName($fileName) {
        // Replace any non-alphanumeric characters with an underscore
        $sanitizedFileName = preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName);
        return $sanitizedFileName;
    }
    
    
    
    public function addGroup(Request $request){
        
         try{
             
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'image'=> 'nullable|file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,ogg,mp3,wav|max:20000',
                
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            $exitsData = UserGroup::where(['name'=>$request->name,'user_id'=>Auth::user()->id])->first();
            if ($exitsData) {
                return $this->errorResponse("Group name already exists", 'already_exists', 400);
            }
    
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                
                $userId = Auth::user()->id;
                $file = $request->file('image');
                $res = $this->UploadImage->saveMedia($file,$userId);
                $imageURL = $res;
                
        } else {
            $imageURL = null; // or set a default image URL if no image is uploaded
        }

                $addGroup = new UserGroup;
                $addGroup->name = $request->name;
                $addGroup->image = $imageURL; 
                $addGroup->user_id = Auth::user()->id;
                $addGroup->save();
                if(!$addGroup){
                    return $this->errorResponse("Group not added", 'data_not_add', 400);
                }
                
                $addGroup = UserGroup::find($addGroup->id);
                return $this->successResponse("Group Added successfully",200,$addGroup);
            
            
        }catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    public function editGroup(Request $request, $groupId){
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,ogg,mp3,wav|max:20000',
            ]);
    
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
    
            // Check if the group exists and belongs to the authenticated user
            $group = UserGroup::where('id', $groupId)
                ->where('user_id', Auth::user()->id)
                ->first();
    
            if (!$group) {
                return $this->errorResponse("Group not found or does not belong to you", 'not_found', 404);
            }
    
            // Check if the group name already exists for the user
            $existingGroup = UserGroup::where('name', $request->name)
                ->where('user_id', Auth::user()->id)
                ->where('id', '!=', $groupId)
                ->first();
    
            if ($existingGroup) {
                return $this->errorResponse("Group name already exists", 'already_exists', 400);
            }
    
            // Handle image upload if provided
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                
                $userId = Auth::user()->id;
                $file = $request->file('image');
                $res = $this->UploadImage->saveMedia($file,$userId);
                $imageURL = $res;
                
            } else {
                $imageURL = $group->image; // Keep the current image if no new image is uploaded
            }
    
            // Update group details
            $group->name = $request->name;
            $group->image = $imageURL;
            $group->save();
    
            return $this->successResponse("Group updated successfully", 200, $group);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }
    
    public function deleteGroup($groupId){
        DB::beginTransaction();

        try {
            // Check if the group exists and belongs to the authenticated user
             $group = UserGroup::where(['id'=>$groupId,'user_id'=>Auth::id()])->first();
             if($group){
                 
                // Delete the group's image if it exists
                if ($group->image) {
                    $imagePath = public_path(parse_url($group->image, PHP_URL_PATH));
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
        
                // Delete related records
                AssignUserGroup::where('user_group_id', $group->id)->delete();
                ConnectionRequest::where('group_id', $group->id)->delete();
                FamilyMember::where('group_id', $group->id)->delete();
                MemberGroup::where('group_id', $group->id)->delete();
        
                // Finally, delete the group
                $group->delete();
        
                DB::commit();
        
                return response()->json(['message' => 'Group deleted successfully', 'status' => 'success', 'error_type' => " "], 200);
             }else{
                 return $this->successResponse("You are not a valid user to delete a group", 200,null);
             }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            DB::rollBack();
            return response()->json(['message' => 'Group not found', 'status' => 'failed', 'error_type' => " "], 404);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed', 'error_type' => ''], 500);
        }
    }

    private function errorResponse($message, $error, $statusCode)
    {
        return response()->json(['message' => $message, 'error' => $error, 'status' => 'failed'], $statusCode);
    }

    private function successResponse($message, $statusCode, $data = [])
    {
        return response()->json(['message' => $message, 'status' => 'success', 'data' => $data], $statusCode);
    }

    
    public function search(Request $request) {
    try {
        $validator = Validator::make($request->all(), [
            'search' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
        }

        $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
        $currentUserId = auth()->id(); // Get the current user's ID
       
        
        // Start building the query
        $query = User::where('id', '!=', $currentUserId)
                     ->where('role_id', '<>', 1)
                     ->whereNotIn('id', $blockedUserIds);
                     
        if ($request->search) {
            $searchTerm = $request->search;
            $searchTerms = explode(' ', $searchTerm);
            
            // Build the search query
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($q1) use ($term) {
                        $q1->where('first_name', 'like', '%' . $term . '%')
                              ->orWhere('last_name', 'like', '%' . $term . '%')
                              ->orWhere('phone', 'like', '%' . $term . '%')
                              ->orWhere('email', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        
        

        // Get the paginated results
        $users = $query->paginate(10);

        if ($users->isEmpty()) {
            return $this->errorResponse("No users found matching your search.", 'data_not_found', 400);
        }

        return response()->json([
            "message" => 'Users retrieved successfully',
            "status" => "success",
            "error_type" => "",
            "data" => $users->items(),
            'total_records' => $users->total(),
            'total_pages' => $users->lastPage(),
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
        ]);
    } catch (\Exception $exception) {
        return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
    }
}

    
    
    public function inviteUser(Request $request)
    {
        DB::beginTransaction(); // Start the transaction earlier
    
        try {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'is_add' => 'required',
                'group_id' => 'sometimes|nullable|integer'
            ]);
    
            // Handle validation errors
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json(['message' => $errors->first(), 'status' => 'failed'], 400);
            }
    
            $currentUser = Auth::user();
    
            // Check if adding to group
            if ($request->is_add === "true") {
                
                $request->group_id = $request->group_id ?? 1;
                
                
                $membershipExists = MemberGroup::where('user_id', $request->user_id)->where('member_id', $currentUser->id)->where('group_id', $request->group_id)->exists();
                $membershipExist = MemberGroup::where('user_id', $currentUser->id)->where('member_id', $request->user_id)->where('group_id', $request->group_id)->exists();
                
                if ($membershipExists || $membershipExist) {
                    return response()->json(['message' => "You are already a member of this group. Please choose another group.", 'status' => 'already_exist'], 400);
                }

                
                $connection = ConnectionRequest::where(function ($query) use ($request, $currentUser) {
                    $query->where('group_id', $request->group_id)
                          ->where(function ($subQuery) use ($request, $currentUser) {
                              $subQuery->where('user_id', $request->user_id)
                                       ->where('sender_id', $currentUser->id)
                                       ->orWhere(function ($q) use ($request, $currentUser) {
                                           $q->where('user_id', $currentUser->id)
                                             ->where('sender_id', $request->user_id);
                                       });
                          });
                })->orderBy('id','desc')->first(); // Use first() to get a single result
                
                if ($connection) {
                    if ($connection->status == "Invitation sent") {
                        return $this->errorResponse("The request has already been sent", 'already_sent', 400);
                        
                    }elseif($connection->status == "Invitation accepted"){    
                        $memberExits = MemberGroup::where(['user_id'=>$currentUser->id,'group_id'=>$request->group_id,'member_id'=>$request->user_id])->first();
                        if(!$memberExits){
                            
                            // Send connection request 
                            $date = date('m/d/Y');
                            $info = new ConnectionRequest();
                            $info->sender_id = $currentUser->id;
                            $info->user_id = $request->user_id;
                            $info->group_id = $request->group_id;
                            $info->msg = $currentUser->first_name . " has sent you a request to join the family on " . $date;
                            $info->is_verify = false;
                            $info->status = "Invitation sent";
                            $info->save();
                            
                            $getgroup = UserGroup::where('id',$request->group_id)->select('id','name')->first();
                            $type = "invite";
                            $this->notifyMessage($currentUser, $request->user_id, $getgroup, $type);
                            DB::commit();
                            return $this->successResponse("User invited successfully", 200, $info);
                            
                            
                        }
                    } else {
                        $familyExists = FamilyMember::where(function ($query) use ($request, $currentUser) {
                            $query->where(['user_id' => $request->user_id, 'member_id' => $currentUser->id])
                                  ->orWhere(['user_id' => $currentUser->id, 'member_id' => $request->user_id]);
                        })->exists();
                
                        if ($familyExists) {
                            return $this->errorResponse("This user is already a family member", 'already_exists', 400);
                        }
                    }
                }
               
                                

                // Send connection request
                $date = date('m/d/Y');
                $info = new ConnectionRequest();
                $info->sender_id = $currentUser->id;
                $info->user_id = $request->user_id;
                $info->group_id = $request->group_id;
                $info->msg = $currentUser->first_name . " has sent you a request to join the family on " . $date;
                $info->is_verify = false;
                $info->status = "Invitation sent";
                $info->save();
                
                $getgroup = UserGroup::where('id',$request->group_id)->select('id','name')->first();
    
                // Notify the recipient
                $type = "invite";
                $this->notifyMessage($currentUser, $request->user_id, $getgroup, $type);
    
                DB::commit(); // Commit transaction
                return $this->successResponse("User invited successfully", 200, $info);
            }
    
            // Handle user removal case (is_add == false)
            elseif($request->is_add === "false"){
                $authId = $currentUser->id;
                
                $deleteStatus = FamilyMember::where(['user_id' => $authId, 'member_id' => $request->user_id])->delete();
                $deleteStatu = FamilyMember::where(['user_id' => $request->user_id, 'member_id' => $authId])->delete();
                
                $memberId = $request->user_id;
                
                MemberGroup::where('user_id', $authId)->where('member_id', $memberId)->where('group_id', 2)->delete();
                MemberGroup::where('user_id', $memberId)->where('member_id', $authId)->where('group_id', 2)->delete();
               
               
                
                $createdGroups = UserGroup::where('user_id', $authId)->pluck('id');
                // Get the member ID from the request
                $memberId = $request->user_id;
                
                // Delete member associations only if they belong to the user's created groups
                MemberGroup::where('user_id', $authId)->where('member_id', $memberId)->whereIn('group_id', $createdGroups)->delete();
                MemberGroup::where('user_id', $memberId)->where('member_id', $authId)->whereIn('group_id', $createdGroups)->delete();

    
                DB::commit(); // Commit the transaction
    
                if ($deleteStatus) {
                    return $this->successResponse("The user was successfully removed from the group", 200, null);
                } else {
                    return $this->successResponse("The user has already been removed from the group", 200, null);
                }
            }
    
            return $this->errorResponse("Something went wrong. Please check your request.", 'something_wrong', 400);
    
        } catch (\Exception $exception) {
            DB::rollBack(); // Rollback transaction on failure
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }

    

    public function getConnectionRequest(Request $request){
        try{
            
            $getHeaders = apache_request_headers();
            $timeZone = isset($getHeaders['time_zone']) ? $getHeaders['time_zone'] : 'UTC';
            
            $user = Auth::user()->id;
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            
            // $getAllUser = ConnectionRequest::where('user_id',$user)->orWhere('sender_id',$user)->where('sender_delete','0')->whereNotIn('user_id', $blockedUserIds)->with('group')->orderBy('id','desc')->get();
            
            $getAllUser = ConnectionRequest::where(function($query) use ($user) {
                $query->where('user_id', $user)
                      ->orWhere(function($q) use ($user) {
                          $q->where('sender_id', $user)
                           ->where('sender_delete', '0');
                      });
            })
            // ->where('sender_delete', '0')
            ->with('group')
            ->orderBy('id', 'desc')
            ->get();


            
            foreach ($getAllUser as $getUser) {
                
                if($getUser->sender_id == $user){
                    
                    if($getUser->guest_email == null)
                    {
                      $nameuser = User::where('id',$getUser->user_id)->first();
                      $getUser->msg = "Requested $nameuser->first_name to join the family.";
                    }else{
                        $getUser->msg = "Requested $getUser->guest_email to join the family.";
                    }
                    
                    $user = User::where('id',$getUser->user_id)->select('id','first_name','last_name','image')->first();
                    if($user){
                         $getUser->user = $user;
                     }else{
                          $getUser->user = null;
                     }
                    
                }else{
                     $user = User::where('id',$getUser->sender_id)->select('id','first_name','last_name','image')->first();
                     if($user){
                         $getUser->user = $user;
                     }else{
                          $getUser->user = null;
                     }
                }
            }

            
            if($getAllUser->isEmpty()){
                return $this->successResponse("No connection requests found.", 200,null);
            }else{
                $perPage = $request->input('per_page', 10); // Default to 10 items per page
                $currentPage = $request->input('page', 1);
        
                $slicedGroups = $getAllUser->slice(($currentPage - 1) * $perPage, $perPage)->values();
                // Create a LengthAwarePaginator instance manually
                $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
                    $slicedGroups,
                    $getAllUser->count(),
                    $perPage,
                    $currentPage
                );
        
                // Return success response using the successResponse method
                return response()->json([
                    "message" => 'Connection List',
                    "status" => "success",
                    "error_type" => "",
                    "data" => $paginatedGroups->items(),
                    'total_records' => $paginatedGroups->total(),
                    'total_pages' => $paginatedGroups->lastPage(),
                    'current_page' => $paginatedGroups->currentPage(),
                    'per_page' => $paginatedGroups->perPage(),
                ]);
                // return $this->successResponse("Connection List", 200,$getAllUser->items(), $getAllUser);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        }
    }
    
    
    public function getAllGroup(){
         try{
             $getGroups = UserGroup::withCount('addUserGroups')->paginate(10);
            if($getGroups->isEmpty()){
                return $this->errorResponse("Data not found", 'data_not_found', 400);
            }else{
                return $this->successResponse("All groups list",200,$getGroups->items(),$getGroups);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        } 
    }
    
  

    public function acceptRequest(Request $request) {
        try {
            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'is_accept' => 'required',
                'sender_id' => 'required|integer',
            ]);
    
            if ($validator->fails()) {
                return response()->json([ 'message' => $validator->errors()->first(),'status' => 'failed'], 400);
            }
    
            // Check if the user is authenticated
            if (!Auth::check()) {
                return response()->json(['message' => 'User not authenticated', 'status' => 'fail'], 401);
            }
    
            // Get authenticated user and default group_id
            $userId = Auth::id();
            //$userId = 787;
            $group_id = $request->group_id ?? 1;
            
            $membershipExists = MemberGroup::where(function ($query) use ($request, $userId, $group_id) {
                $query->where('user_id', $request->sender_id)
                      ->where('member_id', $userId)
                      ->where('group_id', $group_id);
            })->orWhere(function ($query) use ($request, $userId, $group_id) {
                $query->where('user_id', $userId)
                      ->where('member_id', $request->sender_id)
                      ->where('group_id', $group_id);
            })->exists();
            
            // if ($membershipExists) {
            //     return response()->json(['message' => "You are already a member of this group. Please choose another group.", 'status' => 'already_exist'], 400);
            // }

            // Retrieve the connection request data
            $getData = ConnectionRequest::where(['user_id' => $userId,  'sender_id' => $request->sender_id])->orderBy('id', 'DESC')->first();
            $getNotification = Notification::where(['receiver_id' => $userId,  'sender_id' => $request->sender_id])->orderBy('id', 'DESC')->first();

            if (!$getData) {
                return response()->json(['message' => "Connection request not found for the specified user and group", 'status' => 'not_found'], 404);
            }
            
    
            // If the request is accepted
            if ($request->is_accept == 1 || $request->is_accept == true) {
                $getData->status = "Invitation accepted";
                $getData->group_id = $group_id;
                $getData->is_verify = 1;
                $getData->save();
                
                $getNotification->has_actioned = 1;
                $getNotification->group_id = $group_id;
                $getNotification->save();
                
    
                // Send notification about acceptance
                $this->notifyMessage(Auth::user(), $request->sender_id, null, 'accept');
                    
                    $existingMember = FamilyMember::where(['user_id' => $request->sender_id, 'member_id' => $userId])->first();
                    $existingFamily = FamilyMember::where(['member_id' => $request->sender_id, 'user_id' => $userId])->first();
                    
                    if (!$existingMember && !$existingFamily) {
                        // Create a new family member
                        $addMember = new FamilyMember;
                        $addMember->user_id = $userId; // Member's user ID
                        $addMember->member_id = $request->sender_id; // Sender's user ID
                        $addMember->group_id = 1;
                        $addMember->save();
    
                        if (!$addMember) {
                            return response()->json(['message' => "Something went wrong while adding member to the family group", 'status' => 'something_wrong'], 500);
                        }
                    }
                    
                    /// Add the member to the appropriate group
                    $existingMember = MemberGroup::where(['user_id' => $request->sender_id, 'member_id' => $userId, 'group_id' => $group_id ])->first();
                    
                    if (!$existingMember) {
                        $newMember = new MemberGroup;
                        $newMember->user_id = $request->sender_id;
                        $newMember->group_id = $group_id;
                        $newMember->member_id = $userId;
                        $newMember->save();
    
                        if (!$newMember) {
                            return response()->json(['message' => "Something went wrong while adding member to the group", 'status' => 'something_wrong'], 500);
                        }
                    }
    
                // Success response for accepting the request
                return response()->json(['message' => "Welcome to the group", 'status' => 'success'], 200);
    
            } else {
                $getData->status = "Invitation declined";
                $getData->save();
                
                $getNotification->has_actioned = 1;
                $getNotification->save();
    
                return response()->json(['message' => "Request denied successfully", 'status' => 'success'], 200);
            }
    
        } catch (\Exception $e) {
            // Catch any exceptions and roll back if needed
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail'], 500);
        }
    }



    public function deleteConnectionRequest(Request $request)
    {
        try {
            $user = Auth::user();
    
            if (!$user) {
                return response()->json(['message' => 'User not authenticated.', 'status' => 'fail', 'data' => null], 401);
            }
    
            if ($request->id) {
                // Delete connection request by ID
                // ConnectionRequest::where('id', $request->id)->update(['status'=>"Invitation declined"]);
                
                ConnectionRequest::where('id', $request->id)->delete();
            } else {
                // Delete all connection requests for the authenticated user
                ConnectionRequest::where('user_id', $user->id)->delete();
                ConnectionRequest::where('sender_id', $user->id)->update(['sender_delete'=>1]);
            }
    

            return $this->successResponse('Connection Request Deleted Successfully', 200, null);  
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage(), 'status' => 'fail', 'data' => null], 500);
        }
    }

     
    public function allMyFamilyMember(Request $request){
        try{
            $currentUser = Auth::user()->id ;
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            $currentUser = ($request->user_id) ? $request->user_id : Auth::user()->id;

             
            $getUser = FamilyMember::where(function($query) use ($currentUser) {
                    $query->where('user_id', $currentUser)
                          ->orWhere('member_id', $currentUser);
                })
                ->with(['user', 'user.assignusergroup.group_name', 'member'])
                ->when($request->search, function ($query) use ($request) {
                    $searchTerm = '%' . $request->search . '%';
                    $query->where(function($query) use ($searchTerm) {
                        $query->whereHas('user', function ($query) use ($searchTerm) {
                            $query->where('first_name', 'like', $searchTerm)
                                  ->orWhere('last_name', 'like', $searchTerm);
                        })->orWhereHas('member', function ($query) use ($searchTerm) {
                            $query->where('first_name', 'like', $searchTerm)
                                  ->orWhere('last_name', 'like', $searchTerm);
                        });
                    });
                })
                ->orderBy('id', 'desc');
    
            
            $paginatedData = $getUser->whereNotIn('user_id', $blockedUserIds)->paginate(10);
            
            $simplifiedData = $paginatedData->map(function ($familyMember) use ($request, $currentUser) {
                if($familyMember->member_id ==  $currentUser){
                    $groupExists = MemberGroup::where(function($query) use ($request, $familyMember, $currentUser) {
                                $query->where([
                                    ['group_id', '=', $request->group_id],
                                    ['user_id', '=', $familyMember->user_id],
                                    ['member_id', '=', $currentUser]
                                ]);
                            })->orWhere(function($query) use ($request, $familyMember, $currentUser) {
                                $query->where([
                                    ['group_id', '=', $request->group_id],
                                    ['user_id', '=', $currentUser],
                                    ['member_id', '=', $familyMember->user_id]
                                ]);
                            })->exists();  
                }else{
                    $groupExists = MemberGroup::where(function($query) use ($request, $familyMember, $currentUser) {
                                $query->where([
                                    ['group_id', '=', $request->group_id],
                                    ['user_id', '=', $familyMember->member_id],
                                    ['member_id', '=', $currentUser]
                                ]);
                            })->orWhere(function($query) use ($request, $familyMember, $currentUser) {
                                $query->where([
                                    ['group_id', '=', $request->group_id],
                                    ['user_id', '=', $currentUser],
                                    ['member_id', '=', $familyMember->member_id]
                                ]);
                            })->exists();
                }
              
                if($familyMember->member_id == $currentUser){
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
                            'is_exist' => $groupExists,
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
                            'is_exist' => $groupExists,
                        ];
                    }
                }
                
                return [
                    'id' => $familyMember->id,
                    'user_id' => $userId,
                    'member_id' => $memberId,
                    'user' => $user,
                ];
            })->whereNotIn('user.id', $blockedUserIds)->unique('user.id')->filter()->values();
            
            
                $perPage = $request->input('per_page', 10);
                $currentPage = $request->input('page', 1);
        
                $slicedGroups = $simplifiedData->slice(($currentPage - 1) * $perPage, $perPage)->values();
                // Create a LengthAwarePaginator instance manually
                $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
                    $slicedGroups,
                    $simplifiedData->count(),
                    $perPage,
                    $currentPage
                );
        
                // Return success response using the successResponse method
                return response()->json([
                    "message" => 'Get all family member',
                    "status" => "success",
                    "error_type" => "",
                    "data" => $paginatedGroups->items(),
                    'total_records' => $paginatedGroups->total(),
                    'total_pages' => $paginatedGroups->lastPage(),
                    'current_page' => $paginatedGroups->currentPage(),
                    'per_page' => $paginatedGroups->perPage(),
                ]);
            
            
            
            
            // return $this->successResponse("Get all family member",200,$simplifiedData, $paginatedData);
        
        }catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        } 
    }   
                
                
    public function addMemberToGroup(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                // 'member_id' => 'required',
                'group_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            $menberIds = $request->member_id;
            $currentUser = Auth::user()->id;
            
            if(($request->group_id != 1) && ($request->group_id != 2)){
                $created_user = UserGroup::where(['user_id'=>$currentUser,'id'=>$request->group_id])->first();
                if(empty($created_user)){
                    return $this->errorResponse("You cannot add members to this group because you did not create this group.", 'not_add_member', 400);
                }
            }
            
            if(!isset($menberIds)){
                 $memberExits = MemberGroup::where(['user_id'=>$currentUser,'group_id'=>$request->group_id])->delete();
                 $memberExit = MemberGroup::where(['member_id'=>$currentUser,'group_id'=>$request->group_id])->delete();
                 return $this->successResponse("Members updated successfully", 200);
            }else{
                foreach ($menberIds as $menberId) {    
                    $menberIds = explode(',', $menberId);
                    //delete existing records
                    $memberExits = MemberGroup::where(['user_id'=>$currentUser,'group_id'=>$request->group_id])->delete();
                    foreach ($menberIds as $menberId) {
                        $memberExits = MemberGroup::where(['user_id'=>$currentUser,'group_id'=>$request->group_id,'member_id'=>$menberId])->first();
                        $user = User::find($menberId);
                        if (!$user) {
                            return $this->successResponse("This member does not exist.", 400);
                        }
                        
                        if(!$memberExits){
                            $newMember = new MemberGroup;
                            $newMember->user_id = $currentUser;
                            $newMember->group_id = $request->group_id;
                            $newMember->member_id = $menberId;
                            $newMember->save();
                        }
                    }
                }
                return $this->successResponse("Member added successfully",200);
            }
     
        }catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
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

    

    public function deleteAlbumPost(Request $request){
        try {
            $current_user = Auth::user();
            $deleted = false;
            
            if ($request->post_id && $request->album_id) {
                $posts = $request->post_id;
                $albums = $request->album_id;
                
                foreach ($posts as $post) {    
                    $postsArr = explode(',', $post);
                    foreach ($postsArr as $post_id) {
                        foreach ($albums as $album) {
                            $albumsArr = explode(',', $album);
                            foreach ($albumsArr as $album_id) {
                                $deleteAlbumPost = AlbumPost::where(['album_id' => $album_id, 'post_id' => $post_id, 'user_id' => $current_user->id])->delete();
                                if ($deleteAlbumPost) {
                                    $deleted = true;
                                }
                                $getAlbumPost = AlbumPost::where(['album_id' => $album_id, 'user_id' => $current_user->id])->with('post')->orderBy('id','desc')->first();
                                if(!empty($getAlbumPost)){
                                    $thumbnailPath = null;
                                    $post = $getAlbumPost->post;
                                    $fileExtension = strtolower(pathinfo(
                                        $post->video_formats['original'] ?? $post->file, PATHINFO_EXTENSION
                                    ));
                                    $fileType = $this->getFileType($fileExtension);
                                   
                    
                                   if ($fileType === 'videos') {
                                        $videoFilename = basename($getAlbumPost->post->video_formats['original']);
                                        $thumbnailFilename = public_path('thumbnails/' . pathinfo($videoFilename, PATHINFO_FILENAME) . '.jpg');
                    
                                        if (!file_exists($thumbnailFilename)) {
                                            try {
                                                $command = "ffmpeg -i " . escapeshellarg($getAlbumPost->post->video_formats['original']) . " -ss 00:00:01.000 -vframes 1 " . escapeshellarg($thumbnailFilename);
                                                shell_exec($command);
                                            } catch (\Exception $e) {
                                                return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
                                            }
                                        }
                                    
                                        $thumbnailPath = "https://admin.famoryapp.com/thumbnails/" . pathinfo($videoFilename, PATHINFO_FILENAME) . '.jpg';
                                    }  elseif ($fileType === 'images'){
                                        $thumbnailPath = $getAlbumPost->post->file;
                                    } elseif ($fileType === 'audio'){
                                         $thumbnailPath = "https://admin.famoryapp.com/assets/img/audio_bg.webp";
                                    }
                                    
                                    if(!empty($thumbnailPath)){
                                        $album = Album::find($album_id);
                                        $album->album_cover = $thumbnailPath;
                                        $album->save();
                                    }else{
                                        $album = Album::find($album_id);
                                        $album->album_cover = null;
                                        $album->save();
                                    }
                                }else{
                                    $album = Album::find($album_id);
                                    $album->album_cover = null;
                                    $album->save();
                                }
                            }
                        }
                    }
                }
                
               return $this->successResponse("Posts removed from album", 200, null); 
            } elseif ($request->album_id) {
                $albums = $request->album_id;
                
                foreach ($albums as $album) {
                    $albumsArr = explode(',', $album);
                    foreach ($albumsArr as $album_id) {
                        $deleteAlbumPost = AlbumPost::where(['album_id' => $album_id,'user_id' => $current_user->id])->delete();
                        $deleteAlbum = Album::where(['id' => $album_id])->delete();
                        if ($deleteAlbumPost && $deleteAlbum) {
                            $deleted = true;
                        }
                    }
                }
                return $this->successResponse("Album has been deleted successfully", 200, null);
            } else {
                $deleteAlbum = Album::where(['user_id' => $current_user->id])->delete();
                if ($deleteAlbum) {
                    $deleted = true;
                }
                return $this->successResponse("All album has been deleted successfully", 200, null);
            }
            
            if (!$deleted) {
                return $this->successResponse("Data not found", 200, null);
            }
            
            
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        }
    }


    

        public function allMyGroup(Request $request) {
            try {
                $user = Auth::user();
                // Fetch user groups
                // Fetch member groups and transform data
                $memberGroups = MemberGroup::where(function($query) use ($user) {
                        $query->where('member_groups.member_id', $user->id)
                              ->orWhere('member_groups.user_id', $user->id);
                    })
                    ->join('user_groups', 'member_groups.group_id', '=', 'user_groups.id')
                    ->select('member_groups.member_id', 'member_groups.user_id', 'member_groups.group_id', 'user_groups.id as group_id', 'user_groups.name as group_name','user_groups.image as image')
                    ->get();
                    
                $transformedMemberGroups = $memberGroups->map(function ($item) {
                    return [
                        'created_by' => $item->member_id,
                        'user_id' => $item->user_id,
                        'group' => [
                            'id' => (int)$item->group_id,
                            'name' => $item->group_name,
                            'image' => isset($item->image) ? $item->image : null,
                        ]
                    ];
                });

                
                $familyandfriendsGroup = AssignUserGroup::where(['assgin_user_groups.user_id'=> $user->id,'is_add' => 1])
                    ->join('user_groups', 'assgin_user_groups.user_group_id', '=', 'user_groups.id')
                    
                    ->select(
                        'assgin_user_groups.sender_id', 
                        'assgin_user_groups.user_id', 
                        'assgin_user_groups.user_group_id', 
                        'user_groups.id as user_group_id', 
                        'user_groups.name as group_name',
                        'user_groups.image as image'
                    )
                    ->get();

                    $transformedFamilyAndFriendsGroup = $familyandfriendsGroup->map(function ($item) {
                        return [
                            'created_by' => $item->sender_id,
                            'user_id' => $item->user_id,
                            'group' => [
                                'id' => (int)$item->user_group_id,
                                'name' => $item->group_name,
                                'image' => isset($item->image) ? $item->image : null,
                            ]
                        ];
                    });
                    
                    $searchTerm = $request->search;
                    $userGroups = UserGroup::where('user_id', $user->id)->get();
            
                    // Combine all groups and ensure uniqueness based on group ID
                    $allGroups = $transformedMemberGroups
                        ->concat($transformedFamilyAndFriendsGroup)
                        // ->concat($transformedFamilyMemberGroups)
                        ->concat($userGroups->map(function ($group) {
                            return [
                                'created_by' => $group->user_id,
                                'user_id' => $group->user_id,
                                'group' => [
                                    'id' => (int)$group->id,
                                    'name' => $group->name,
                                    'image' => isset($group->image) ? $group->image : null,
                                ]
                            ];
                        }))
                        ->filter(function ($group) use ($searchTerm) {
                            return str_contains(strtolower($group['group']['name']), strtolower($searchTerm));
                        })
                        ->unique('group.id')->values();
                
                
                // $sortedGroups = $allGroups->sortByDesc(function ($groupItem) {
                //     return in_array($groupItem['group']['name'], ['Family', 'Friends']);
                // });
                
                $sortedGroups = $allGroups->sortBy(function ($groupItem) {
                    $groupName = strtolower($groupItem['group']['name']);
                    
                    if ($groupName === 'family') {
                        return 0; // Family first
                    } elseif ($groupName === 'friends') {
                        return 1; // Friends second
                    }
                    
                    return 2; // All other groups come last
                })->values();
                
        
                $groupData = [];
                // Calculate member counts for each group
                $allGroupsWithCounts = $sortedGroups->map(function ($groupItem) {

                    $groupId = $groupItem['group']['id'];
                    $groupName = strtolower($groupItem['group']['name']);
            
                    if ($groupName === 'family' || $groupName === 'friends') {
                        // Count for family and friend groups with auth check
                        $memberGroupCount = MemberGroup::where('group_id', $groupId)
                            ->where(function($query) {
                                $query->where('user_id', Auth::user()->id)
                                      ->orWhere('member_id', Auth::user()->id);
                            })
                            ->whereHas('user', function ($query) {
                                $query->whereNull('deleted_at'); 
                            })
                            ->count();

                        
                        $memberCount = $memberGroupCount ;
                        $createdaUserId = UserGroup::where('id', $groupId)->pluck('user_id')->first();
                        $groupItem['created_by'] = $createdaUserId;
                        $groupItem['user_id'] = (string)Auth::id();
                }
                    else {
                        $userGroups = UserGroup::where('id', $groupId)->get();
                        if($userGroups){
                            $memberCount = MemberGroup::where(['group_id' => $groupId])->count()  + 1;
                        }else{
                            
                        $memberCount = MemberGroup::where(['group_id' => $groupId])->count() ;
                        }
                        $createdaUserId = UserGroup::where('id', $groupId)->pluck('user_id')->first();
                        $groupItem['created_by'] = $createdaUserId;
                        $groupItem['user_id'] = (string)Auth::id();
                        
                    }
                    
                    $groupItem['member_count'] = $memberCount;
                    return $groupItem;
                    
                });
                
                if($request->type == 'my'){
                    $allGroupsWithCounts = $allGroupsWithCounts->filter(function ($item) {
                        return $item['created_by'] == Auth::id();
                    });
                }

        
                // Paginate the results
                $perPage = $request->input('per_page', 10); // Default to 10 items per page
                $currentPage = $request->input('page', 1);
        
                $slicedGroups = $allGroupsWithCounts->slice(($currentPage - 1) * $perPage, $perPage)->values();
                // Create a LengthAwarePaginator instance manually
                $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
                    $slicedGroups,
                    $allGroupsWithCounts->count(),
                    $perPage,
                    $currentPage
                );
        
                // Return success response using the successResponse method
                return response()->json([
                    "message" => 'User groups fetched successfully',
                    "status" => "success",
                    "error_type" => "",
                    "data" => $paginatedGroups->items(),
                    'total_records' => $paginatedGroups->total(),
                    'total_pages' => $paginatedGroups->lastPage(),
                    'current_page' => $paginatedGroups->currentPage(),
                    'per_page' => $paginatedGroups->perPage(),
                ]);
        
            } catch (\Exception $exception) {
                return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
            }
    }
    
    
    public function myFamily(Request $request){
        // try{
            $data = new \stdClass();
            $currentUser = Auth::user()->id;
            $user = Auth::user();
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);       

            $userGroups = UserGroup::where('user_id', $user->id)->get();
        
                // Fetch member groups and transform data
                $memberGroups = MemberGroup::where(function($query) use ($user) {
                        $query->where('member_groups.member_id', $user->id)
                              ->orWhere('member_groups.user_id', $user->id);
                    })
                    ->join('user_groups', 'member_groups.group_id', '=', 'user_groups.id')
                    ->select('member_groups.member_id', 'member_groups.user_id', 'member_groups.group_id', 'user_groups.id as group_id', 'user_groups.name as group_name','user_groups.image as image')
                    ->get();
                $transformedMemberGroups = $memberGroups->map(function ($item) {
                    return [
                        'created_by' => $item->member_id,
                        'user_id' => $item->member_id,
                        'group' => [
                            'id' => (int)$item->group_id,
                            'name' => $item->group_name,
                            'image' => isset($item->image) ? $item->image : null,
                        ]
                    ];
                });
        
                $familyandfriendsGroup = AssignUserGroup::where(['assgin_user_groups.user_id'=> $user->id,'is_add' => 1])
                    ->join('user_groups', 'assgin_user_groups.user_group_id', '=', 'user_groups.id')
                    ->select(
                        'assgin_user_groups.sender_id', 
                        'assgin_user_groups.user_id', 
                        'assgin_user_groups.user_group_id', 
                        'user_groups.id as user_group_id', 
                        'user_groups.name as group_name',
                        'user_groups.image as image'
                    )
                    ->get();

                    $transformedFamilyAndFriendsGroup = $familyandfriendsGroup->map(function ($item) {
                        return [
                            'created_by' => $item->sender_id,
                            'user_id' => $item->sender_id,
                            'group' => [
                                'id' => (int)$item->user_group_id,
                                'name' => $item->group_name,
                                'image' => isset($item->image) ? $item->image : null,
                            ]
                        ];
                    });
                    
                    $searchTerm = $request->search;
                    // Combine all groups and ensure uniqueness based on group ID
                    $allGroups = $transformedMemberGroups
                        ->concat($transformedFamilyAndFriendsGroup)
                        ->concat($userGroups->map(function ($group) {
                            return [
                                'created_by' => $group->user_id,
                                'user_id' => $group->user_id,
                                'group' => [
                                    'id' => (int)$group->id,
                                    'name' => $group->name,
                                    'image' => isset($group->image) ? $group->image : null,
                                ]
                            ];
                        }))
                        ->filter(function ($group) use ($searchTerm) {
                            return str_contains(strtolower($group['group']['name']), strtolower($searchTerm));
                        })
                        ->unique('group.id')->values();
                
                
                    $sortedGroups = $allGroups->sortBy(function ($group) {
                        $groupName = strtolower($group['group']['name']);
                        if ($groupName === 'family') {
                            return 0; // Family first
                        } elseif ($groupName === 'friends') {
                            return 1; // Friends second
                        }
                        return 2; // other groups come later
                    })->values();
        
        
               
                
                $allGroupsWithCounts = $sortedGroups->map(function ($groupItem) {
                $groupId = $groupItem['group']['id'];
                $groupName = strtolower($groupItem['group']['name']);
            
                if ($groupName === 'family' || $groupName === 'friends') {
                    // Count for family and friend groups with auth check
                    $memberGroupCount = MemberGroup::where('group_id', $groupId)
                        ->where(function($query) {
                            $query->where('user_id', Auth::user()->id)
                                  ->orWhere('member_id', Auth::user()->id);
                        })
                        ->whereHas('user', function ($query) {
                            $query->whereNull('deleted_at'); // Exclude soft-deleted users
                        })
                        ->count();
                    
                    // $familyMemberCount = FamilyMember::where('group_id', $groupId)
                    //     ->where(function($query) {
                    //         $query->where('member_id', Auth::user()->id)
                    //               ->orWhere('user_id', Auth::user()->id);
                    //     })
                    //     ->whereHas('user', function ($query) {
                    //         $query->whereNull('deleted_at'); // Exclude soft-deleted members
                    //     })
                    //     ->count();
                    
                    $memberCount = $memberGroupCount ;
                } else {
                    
                    // Count for other groups
                    $userGroups = UserGroup::where('id', $groupId)->first();
                    $memberUserIds = MemberGroup::where('group_id', $groupId)
                        ->whereHas('member', function ($query) {
                            $query->whereNull('deleted_at');
                        })
                        ->pluck('member_id'); // Get a collection of user IDs
                        
                    // Fetch user IDs from FamilyMember
                    $familyUserIds = FamilyMember::where('group_id', $groupId)
                        ->whereHas('user', function ($query) {
                            $query->whereNull('deleted_at');
                        })
                        ->pluck('user_id');

                    // Merge and deduplicate user IDs
                    $allUserIds = $memberUserIds->merge($familyUserIds)->unique();
                    // Count unique user IDs
                    $memberCount = $memberUserIds->count();
                    //echo $memberCount; die;
                    
                    if ($memberCount) {
                        $memberCount = $memberCount +1;
                        // $memberCount = MemberGroup::where('group_id', $groupId)
                        //     ->whereHas('user', function ($query) {
                        //         $query->whereNull('deleted_at'); // Exclude soft-deleted users
                        //     })
                        //     ->count()
                        //     + FamilyMember::where('group_id', $groupId)
                        //     ->whereHas('user', function ($query) {
                        //         $query->whereNull('deleted_at'); // Exclude soft-deleted members
                        //     })
                        //     ->count()
                        //     + 1; // Including the group creator or owner
                        
                        // $memberUserIds = MemberGroup::where('group_id', $groupId)
                        //     ->whereHas('user', function ($query) {
                        //         $query->whereNull('deleted_at');
                        //     })->get();
                        
                        // // Fetch user IDs from FamilyMember
                        // $familyUserIds = FamilyMember::where('group_id', $groupId)
                        //     ->whereHas('user', function ($query) {
                        //         $query->whereNull('deleted_at');
                        //     })->get();
                        
                        // // Merge and deduplicate user IDs
                        // $allUserIds = $memberUserIds->merge($familyUserIds)->unique();
                        
                        // // Count unique user IDs
                        // $memberCount = $allUserIds->count();
                        
                    } 
                    // else {
                        // Count for other groups without auth check
                        // $memberCount = MemberGroup::where('group_id', $groupId)
                        //     ->whereHas('user', function ($query) {
                        //         $query->whereNull('deleted_at'); // Exclude soft-deleted users
                        //     })
                        //     ->count()
                        //     + FamilyMember::where('group_id', $groupId)
                        //     ->whereHas('user', function ($query) {
                        //         $query->whereNull('deleted_at'); // Exclude soft-deleted members
                        //     })
                        //     ->count();
                        
                       
                        
                    // }
                }
            
                $groupItem['member_count'] = $memberCount;
                return $groupItem;
            });
            

            $datas = [];
            $groupData = [];
            
            foreach ($allGroupsWithCounts as $group) { 
                if (isset($group['group']['name']) && ($group['group']['name'] === 'Family' || $group['group']['name'] === 'Friends')) {
                    
                    $createdaUserId = UserGroup::where('id', $group['group']['id'])->pluck('user_id')->first();
                    $group['created_by'] = $createdaUserId;
                    array_push($groupData,$group);
                    
                }else{
                    
                    $createdaUserId = UserGroup::where('id', $group['group']['id'])->pluck('user_id')->first();
                    $group['created_by'] = $createdaUserId;
                    array_push($groupData,$group);
                    
                }
               
            }
           
            $groupDataCollection = collect($groupData);
            $data->groups = $groupDataCollection->take(5);
            
            
           
            $familyAsUser = FamilyMember::where('user_id', $currentUser)->whereNotIn('member_id', $blockedUserIds)
                    ->whereHas('user', function($query) {
                        $query->whereNull('deleted_at');
                    })
                    ->with('user')->orderBy('id', 'desc')->limit(10)->get();

            $familyAsMember = FamilyMember::where('member_id', $currentUser)->whereNotIn('user_id', $blockedUserIds)
                ->whereHas('member', function($query) {
                    $query->whereNull('deleted_at');
                })
                ->with('member')->orderBy('id', 'desc')->limit(10)->get()
                ->map(function ($item) {
                    $item->user = $item->member;
                    unset($item->member);
                    return $item;
                });
            
            $mergedFamily = $familyAsUser->merge($familyAsMember)->unique('id')->unique('user.id')->sortByDesc('id')->take(10)->values();

            // $mergedFamily = $familyAsUser->merge($familyAsMember)->sortByDesc('id')->take(10)->values();
            $data->family = $mergedFamily;
            
            
            
            
            
            if(!$data){
                return $this->errorResponse("No details get", 'data_not_found', 400);
            }
            return $this->successResponse("Get details successfully",200,$data);
     
        // }catch (\Exception $e) {
        //     return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        // } 
    }
    
    
    public function inviteGuestUser(Request $request){
        try{
           $validator = Validator::make($request->all(), [
                'email' => 'required|email:rfc,dns',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                foreach ($errors->all() as $key => $value) {
                    return response()->json(['message' => $value, 'status' => 'failed'], 400);
                }
            }
            
            $isExist = User::where('email',$request->email)->first();
            if($isExist){
                return $this->successResponse("The user already exists.",200,null);
            }
            $currentuser = Auth::user();
            $addGuest = new InviteGuestUser;
            $addGuest->sender_id =  $currentuser->id;
            $addGuest->email = $request->email;
            $addGuest->save();
            
            $date = date('m/d/Y');
            $infos = new ConnectionRequest;
            $infos->sender_id = $currentuser->id;
            $infos->user_id = null;
            $infos->group_id = "1";
            $infos->msg = $currentuser->first_name . " has sent you a request to join the family on " . $date;
            $infos->is_verify = false;
            $infos->status = "Invitation sent";
            $infos->guest_email = $request->email;
            $infos->save();
            
            
            
            if(!$addGuest){
                return $this->successResponse("Something went wrong saving and sending an email to a guest user",200);
            }
            $parts = explode('@', $request->email);
            $username = $parts[0];
            $guestName = ucfirst($username);
            
            Mail::to($request->email)->send(new InviteGuestUserMail($currentuser,$guestName));
            return $this->successResponse("The data has been saved and email has been sent to the guest user",200,$addGuest);
            
        }catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        }
    }
    
    
    public function getGroupMember(Request $request){
        try{
            $currentUser = Auth::id();
            if ($request->group_id == 1 || $request->group_id == 2) {
                
                $existingGroupsForMember = MemberGroup::where('group_id', $request->group_id)->where(['user_id'=>$currentUser])->get();
                $existingGroupsForUser = MemberGroup::where('group_id', $request->group_id)->where(['member_id'=> $currentUser])->get();
                $memberGroups = $existingGroupsForMember->merge($existingGroupsForUser);
                
                $userGroups = $memberGroups;
                $userInfos = [];
        
                foreach ($userGroups as $group) {
                    if (Auth::id() != $group->user_id) {
                        $userInfo = User::where('id', $group->user_id)
                            ->select('id', 'first_name', 'last_name', 'image')
                            ->first();
                    } else {
                        $userInfo = User::where('id', $group->member_id)
                            ->select('id', 'first_name', 'last_name', 'image')
                            ->first();
                    }
                    if ($userInfo) {
                        $userInfos[] = $userInfo;
                    }
                }
            } else {
                    $memberGroups = MemberGroup::where('group_id', $request->group_id)->get();
                    $familyMembers = FamilyMember::where('group_id', $request->group_id)->get();
                    
                    $userInfos = [];
                    foreach($memberGroups as $memberGroup){
                        $userInfo = User::where('id', $memberGroup->member_id)->select('id','first_name','last_name','image')->first();
                        if ($userInfo) {
                            $userInfos[] = $userInfo;
                        }
                    }

                    foreach($familyMembers as $familyMember){
                        $userInfo = User::where('id', $familyMember->user_id)->select('id','first_name','last_name','image')->first();
                        if ($userInfo) {
                            $userInfos[] = $userInfo;
                        }
                    }
                    
                    $userGroups = $memberGroups->merge($familyMembers);
                    foreach ($userGroups as $group) {
                        $userInfo = User::where('id', $group->user_id)->select('id','first_name','last_name','image')->first();
                        if ($userInfo) {
                            $userInfos[] = $userInfo;
                        }
                    }
            }
           
            if ($request->group_id !== "1" && $request->group_id !== "2") {
                $getUserGroup = UserGroup::where(['id' => $request->group_id])->first();
                if ($getUserGroup) {
                    $userGroupInfo = User::where('id', $getUserGroup->user_id)
                        ->select('id', 'first_name', 'last_name', 'image')
                        ->first();
                    if ($userGroupInfo) {
                        // Directly push userGroupInfo into userInfos
                        $userInfos[] = $userGroupInfo;
                    }
                }
            }
            
            if (empty($userInfos)) {
                $getUserGroup = UserGroup::where(['user_id' => $currentUser, 'id' => $request->group_id])
                    ->with('user')->get();
                return $this->successResponse("not any member", 200, $getUserGroup);
            }
            
            $userInfos = collect($userInfos)->unique('id')->values();

            return $this->successResponse("Get Member successfully", 200, $userInfos);
        
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        }
            
    }
    
    
    public function test(Request $request){
        try{
            // $currentUser = Auth::user()->id ;
            $blockedUserIds = $request->attributes->get('blocked_user_ids', []);
            $currentUser = ($request->user_id) ? $request->user_id : Auth::user()->id;

             
            $getUser = FamilyMember::where(function($query) use ($currentUser) {
                    $query->where('user_id', $currentUser)
                          ->orWhere('member_id', $currentUser);
                })
                ->with(['user', 'user.assignusergroup.group_name', 'member'])
                ->when($request->search, function ($query) use ($request) {
                    $searchTerm = '%' . $request->search . '%';
                    $query->where(function($query) use ($searchTerm) {
                        $query->whereHas('user', function ($query) use ($searchTerm) {
                            $query->where('first_name', 'like', $searchTerm)
                                  ->orWhere('last_name', 'like', $searchTerm);
                        })->orWhereHas('member', function ($query) use ($searchTerm) {
                            $query->where('first_name', 'like', $searchTerm)
                                  ->orWhere('last_name', 'like', $searchTerm);
                        });
                    });
                })
                ->orderBy('id', 'desc');
    
            
            $paginatedData = $getUser->whereNotIn('user_id', $blockedUserIds)->paginate(10);
            
            $simplifiedData = $paginatedData->map(function ($familyMember) use ($request, $currentUser) {
                if($familyMember->member_id ==  $currentUser){
                   $groupExists = MemberGroup::where(function($query) use ($request, $familyMember, $currentUser) {
                        $query->where([
                                ['group_id', '=', $request->group_id],
                                ['user_id', '=', $familyMember->user_id],
                                ['member_id', '=', $currentUser]
                            ]);
                        })->orWhere(function($query) use ($request, $familyMember, $currentUser) {
                            $query->where([
                                ['group_id', '=', $request->group_id],
                                ['user_id', '=', $currentUser],
                                ['member_id', '=', $familyMember->user_id]
                            ]);
                        })->exists();
                }else{
                    $groupExists = MemberGroup::where(function($query) use ($request, $familyMember, $currentUser) {
                                $query->where([
                                    ['group_id', '=', $request->group_id],
                                    ['user_id', '=', $familyMember->member_id],
                                    ['member_id', '=', $currentUser]
                                ]);
                            })->orWhere(function($query) use ($request, $familyMember, $currentUser) {
                                $query->where([
                                    ['group_id', '=', $request->group_id],
                                    ['user_id', '=', $currentUser],
                                    ['member_id', '=', $familyMember->member_id]
                                ]);
                            })->exists();
                }
              
                if($familyMember->member_id == $currentUser){
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
                            'is_exist' => $groupExists,
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
                            'is_exist' => $groupExists,
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
            
            return $this->successResponse("Get all family member",200,$simplifiedData, $paginatedData);
        
        }catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        } 
    }  
    
    
    
    // public function getFamilyAndFriends(Request $request) {
    //     try {
            
    //         $validator = Validator::make($request->all(), [
    //             'group_id' => 'required',
    //         ]);
    
    //         if ($validator->fails()) {
    //             return response()->json(['message' => $validator->errors()->first(),'status' => 'failed'], 400);
    //         }
            
            
    //         $userId = Auth::id();
    //         $groupId = $request->group_id;
            
    //         // Get family members where the user is either the user or the member
    //         $familyMembers = FamilyMember::where('user_id', $userId)->with('user')->get();
    //         $familyMembersAsMember = FamilyMember::where('member_id', $userId)->with('member')->get();
    //         $familyMembersAsMember->transform(function ($item) {
    //             $item->user = $item->member;
    //             unset($item->member);
    //             return $item;
    //         });
            

    //         $familyData = $familyMembers->merge($familyMembersAsMember)->values();
            
            
    //         foreach($familyData as $data){
  
    //                 $data->is_exist = false;

    //         }
            

    //         $groupMembers = MemberGroup::where('user_id', $userId)->where('group_id', $groupId)->with('user')->get();
    //         $groupMembersAsMember = MemberGroup::where('member_id', $userId)->where('group_id', $groupId)->with('member')->get();
    //         $groupMembersAsMember->transform(function ($item) {
    //             $item->user = $item->member;
    //             unset($item->member);
    //             return $item;
    //         });
            

    //         $groupData = $groupMembers->merge($groupMembersAsMember)->values();
            
            
    //         foreach($groupData as $data){
                
    //             if($data->group_id == $groupId){
    //                 $data->is_exist = true;
    //             }else{
    //                 $data->is_exist = false;
    //             }
    //         }
            
    //         $familyUserIds = $familyData->pluck('user.id')->all();
            
    //         $filteredGroupData = $groupData->filter(function ($item) use ($familyUserIds) {
    //             return !in_array($item->user->id, $familyUserIds);
    //         });
            
    //         // $mergedUser = $filteredGroupData;
            
    //         // // Step 3: Merge the filtered $groupData with $familyData
    //         $mergedUser = $familyData->merge($filteredGroupData)->values();
    //         foreach($groupData as $data){
    //             $groupMembers = MemberGroup::where('user_id', $data->member_id)->where('member_id',$data->user_id)->where('group_id', $data->group_id)->first();
    //             $groupMembersAsMember = MemberGroup::where('member_id',$data->member_id)->where('group_id', $data->group_id)->where('user_id', $data->user_id)->first();
    //             if($groupMembers && $groupMembersAsMember){
    //                  $data->is_exist = true;
    //             }else{
    //                 $data->is_exist = false;
    //             }
    //         }
            
            
    //         // $mergedUser = $familyData->merge($groupData)->values();
            
            
            
        

    //         $perPage = $request->input('per_page', 10);
    //         $currentPage = $request->input('page', 1);
            
    //         $slicedData = $mergedUser->slice(($currentPage - 1) * $perPage, $perPage)->values();
    //         $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
    //             $slicedData,
    //             $mergedUser->count(),
    //             $perPage,
    //             $currentPage,
    //             ['path' => $request->url(), 'query' => $request->query()] 
    //         );
            
    //         return response()->json([
    //             "message" => 'Get all family and friends members',
    //             "status" => "success",
    //             "error_type" => "",
    //             "data" => $paginatedData->items(),
    //             'total_records' => $paginatedData->total(),
    //             'total_pages' => $paginatedData->lastPage(),
    //             'current_page' => $paginatedData->currentPage(),
    //             'per_page' => $paginatedData->perPage(),
    //         ]);
    
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
    //     }
    // }
    
    
    public function getFamilyAndFriends(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
    
            $userId = Auth::id();
            $groupId = $request->group_id;
    
            // Get family members
            $familyMembers = FamilyMember::where('user_id', $userId)->with('user')->get();
            $familyMembersAsMember = FamilyMember::where('member_id', $userId)->with('member')->get();
            $familyMembersAsMember->transform(function ($item) {
                $item->user = $item->member;
                unset($item->member);
                return $item;
            });
    
            $familyData = $familyMembers->merge($familyMembersAsMember)->values();
            $familyUserIds = $familyData->pluck('user.id')->toArray();
    
            // Get group members
            $groupMembers = MemberGroup::where('user_id', $userId)->where('group_id', $groupId)->with('user')->get();
            $groupMembersAsMember = MemberGroup::where('member_id', $userId)->where('group_id', $groupId)->with('member')->get();
            $groupMembersAsMember->transform(function ($item) {
                $item->user = $item->member;
                unset($item->member);
                return $item;
            });
    
            $groupData = $groupMembers->merge($groupMembersAsMember)->values();
    
            // Filter out family members that are in the group
            $filteredFamilyData = $familyData->filter(function ($item) use ($groupData) {
                return !$groupData->contains('user.id', $item->user->id);
            })->values();
            
            foreach ($filteredFamilyData as $data) {
                $data->is_exist = false; // All family members in filtered data
            }
    
            // Set is_exist flag for group members
            foreach ($groupData as $data) {
                $data->is_exist = true;
            }
    
            // Merge filtered family data with group data
            $mergedUser = $filteredFamilyData->merge($groupData)->values();
            
            if($request->search){
                
               $searchTerm = strtolower($request->search);

                $mergedUser = $mergedUser->filter(function ($item) use ($searchTerm) {
                    return stripos($item['user']['first_name'] ?? '', $searchTerm) !== false || 
                           stripos($item['user']['last_name'] ?? '', $searchTerm) !== false;
                });
            }
            
    
            // Pagination logic
            $perPage = $request->input('per_page', 10);
            $currentPage = $request->input('page', 1);
            $slicedData = $mergedUser->slice(($currentPage - 1) * $perPage, $perPage)->values();
            
            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
                $slicedData,
                $mergedUser->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
    
            return response()->json([
                "message" => 'Get all family and friends members',
                "status" => "success",
                "data" => $paginatedData->items(),
                'total_records' => $paginatedData->total(),
                'total_pages' => $paginatedData->lastPage(),
                'current_page' => $paginatedData->currentPage(),
                'per_page' => $paginatedData->perPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        }
    }

    
    public function userGroupList(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(),'status' => 'failed'], 400);
            }
    
            $loggedInUserId = Auth::id();
            $requestedUserId = $request->user_id;

            $userGroupIds = UserGroup::where('user_id', $loggedInUserId)->pluck('id');
            $defaultAssignedGroups = AssignUserGroup::where('user_id', $loggedInUserId)->pluck('user_group_id');
            $groupIds = $userGroupIds->merge($defaultAssignedGroups)->unique();
            

            $existingGroupsForMember = MemberGroup::whereIn('group_id', $groupIds)->where(['member_id'=> $requestedUserId,'user_id'=>$loggedInUserId])->pluck('group_id');
            $existingGroupsForUser = MemberGroup::whereIn('group_id', $groupIds)->where(['member_id'=> $loggedInUserId,'user_id'=>$requestedUserId])->pluck('group_id');
            
            $mergedGroupIds = $existingGroupsForMember->merge($existingGroupsForUser)->unique();
            $groupList = UserGroup::whereIn('id', $groupIds)->whereNotIn('id', $mergedGroupIds)->get();
    
            $perPage = 10;
            $currentPage = $request->input('page', 1);
    
            $slicedData = $groupList->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
                $slicedData,
                $groupList->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
    
            return response()->json([
                "message" => 'Group listing retrieved successfully',
                "status" => "success",
                "data" => $paginatedData->items(),
                'total_records' => $paginatedData->total(),
                'total_pages' => $paginatedData->lastPage(),
                'current_page' => $paginatedData->currentPage(),
                'per_page' => $paginatedData->perPage(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage(),'status' => 'fail','data' => null], 500);
        }
    }
    
    
    public function moveUserGroup(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'remove_group_id' => 'required',
                'add_group_id' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }
    
            $loggedInUserId = Auth::id();
            $requestedUserId = $request->user_id;
            $removeGroupId = $request->remove_group_id;
            $addGroupId = $request->add_group_id;
            
            

    
            $existingMemberGroup = MemberGroup::where('group_id', $removeGroupId)->where(['member_id' => $requestedUserId, 'user_id' => $loggedInUserId])->first();
            $existingUserGroup = MemberGroup::where('group_id', $removeGroupId)->where(['user_id' => $requestedUserId, 'member_id' => $loggedInUserId])->first();

            

            if ($existingMemberGroup) {
                $existingMemberGroup->delete();
            }
    
            if ($existingUserGroup) {
                $existingUserGroup->delete();
            }

    
            $newMemberGroup = new MemberGroup;
            $newMemberGroup->user_id = $loggedInUserId;
            $newMemberGroup->group_id = $addGroupId;
            $newMemberGroup->member_id = $requestedUserId;
    
            if (!$newMemberGroup->save()) {
                return $this->successResponse("Something went wrong, user not moved", 400, null);
            }
    
            return $this->successResponse("User moved successfully to the new group", 200, $newMemberGroup);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage(), 'status' => 'fail', 'data' => null], 500);
        } 
    }


    public function userList(Request $request)
    {
        try {
            // Custom pagination params
            $limit = (int) $request->get('limit', 30); // default 30
            $page  = (int) $request->get('page', 1);   // default 1
            $offset = ($page - 1) * $limit;
            $authUser = Auth::user();
            $search = $request->get('search'); // search keyword

            // Get all user IDs that are already connected (follower/following)
            $relatedUserIds = Follow::where(function($q) use ($authUser) {
                                        $q->where('follower_id', $authUser->id)
                                        ->orWhere('following_id', $authUser->id);
                                    })
                                    ->pluck('follower_id')
                                    ->merge(
                                        Follow::where(function($q) use ($authUser) {
                                            $q->where('follower_id', $authUser->id)
                                            ->orWhere('following_id', $authUser->id);
                                        })->pluck('following_id')
                                    )
                                    ->unique()
                                    ->toArray();

            // Exclude myself
            $relatedUserIds[] = $authUser->id;

            // Base query
            $query = User::select('id','first_name','last_name','email','username','image')
                        ->whereNotIn('id', $relatedUserIds)
                        ->whereNull('deleted_at')
                        ->where('role_id', 2);

            // Apply search filter if provided
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Get total count
            $totalUsers = $query->count();

            // Get paginated result
            $users = $query->orderBy('id', 'desc')
                        ->skip($offset)
                        ->take($limit)
                        ->get();

            $data = [
                'user_id'     => $authUser->id,
                'count'       => $totalUsers,
                'page'        => $page,
                'limit'       => $limit,
                'total_pages' => ceil($totalUsers / $limit),
                'users'       => $users
            ];

            return response()->json([
                'message' => 'User List fetched successfully',
                'status'  => "success",
                'data'    => $data
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Something Went Wrong! ".$e->getMessage(),
                'status'  => 'failed'
            ], 400);
        }
    }


    
    
    

    
}
