<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\FormatResponseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use App\Services\UploadImage;
use Illuminate\Support\Facades\Hash;
class AdminUserController extends Controller
{
    use FormatResponseTrait;
    protected $UploadImage;

    public function __construct(UploadImage $UploadImage)
    {
        $this->UploadImage = $UploadImage;
    }

    public function index(Request $request)
    {
        
        if(Auth::user()->role_id != 1)
        {
             return redirect()
             ->route('dashboard')
             ->with('error', 'You are not authorized to access this page.');
        }
        $detail['admin_users'] = User::with('role')->where('role_id',4)->paginate(10);
        return view('admin.adminUser.index',$detail);
    }

    public function create(Request $request)
    {
        $detail['roles'] = Role::get();
        return view('admin.adminUser.create',$detail);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name'  => 'required|max:255',
            'email'      => 'required',
            'phone'      => 'required',
            'password'   => 'required',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }   
    
        try 
        {
            $checkEmail = User::where('email',$request->email)->where('role_id',1)->first();
            if($checkEmail)
            {
                return response()->json(['message' => "Email Already exist", 'status' => 'failed', 'data' => []], 500);
            }
            // Create User
            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->role_id = 4; 
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();
            if ($request->hasFile('image')) 
            {
            
                // Update user's image URL
                $res = $this->UploadImage->saveMedia($request->file('image'),$user->id);
                $user->image = $res;
                $user->save(); // Save the updated user record
            }
            
            return redirect()->route('admin_user')->with('success', 'Admin User Created Successfully');
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed', 'data' => []], 500);
        }
    }

    public function edit($id)
    {
       $user = User::find($id);
       return view('admin.adminUser.edit',compact('user'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'required',
            'password'   => 'nullable|min:6',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Fetch admin user
            $user = User::where('id', $id)
                        ->where('role_id', 1)
                        ->first();

            if (!$user) {
                return redirect()->back()->with('error', 'Admin User not found');
            }

            // Check email uniqueness
            $emailExists = User::where('email', $request->email)
                               ->where('id', '!=', $user->id)
                               ->where('role_id', 1)
                               ->exists();

            if ($emailExists) {
                return redirect()->back()
                    ->with('error', 'Email already exists')
                    ->withInput();
            }

            // Update fields
            $user->first_name = $request->first_name;
            $user->last_name  = $request->last_name;
            $user->email      = $request->email;
            $user->phone      = $request->phone;

            // Image upload
            if ($request->hasFile('image')) {
                $imagePath = $this->UploadImage->saveMedia(
                    $request->file('image'),
                    $user->id
                );
                $user->image = $imagePath;
            }

            // Password update
            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return redirect()
                ->route('admin_user')
                ->with('success', 'Admin User Updated Successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with(
                'error',
                'Something went wrong. Please try again.'
            );
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->id;

            $user = User::withTrashed()->find($id);

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User not found.'
                ]);
            }

            // Permanent delete
            $user->forceDelete();

            return response()->json([
                'status' => 200,
                'message' => 'User permanently deleted successfully.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Internal Server Error.'
            ]);
        }
    }


}
