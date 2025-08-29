<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */

    public function viewLogin()
    {
        return view('auth.login');
    }
    
    public function adminLogin()
    {
        return view('auth.adminLogin');
    }


    public function adminStore(Request $request)
    {
        $data = $request->all();
        if(!$token = JWTAuth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return back()->with('error','Your credentials are invalid please check.');
        }
        
        $request->session()->regenerate();
        $user = Auth::user();
        
        $datas = [
            'email' => $request->email,
            'password' => $request->password
        ];
        $bearer_token = $token;
        
        if($user->role_id == '1'){
            // append bearer_token
            Auth::attempt($datas);
            $user->bearer_token = $bearer_token;
            $request->session()->put('user', $user);
            return redirect()->intended('dashboard');
        }
        else{
        	Auth::guard('web')->logout();
        	return back()->with('error','Your credentials are invalid please check.');
        }
        
    }
    
    public function store(Request $request)
    {
        $data = $request->all();
        if(!$token = JWTAuth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return back()->with('error','Your credentials are invalid please check.');
        }
        
        $request->session()->regenerate();
        $user = Auth::user();
        
        $datas = [
            'email' => $request->email,
            'password' => $request->password
        ];
        $bearer_token = $token;
        
        if($user->role_id == '3' || $user->role_id == '2') {
            // append bearer_token
            Auth::attempt($datas);
            $user->bearer_token = $bearer_token;
            $request->session()->put('user', $user);
            return redirect()->intended('stickers');
        } else {
        	Auth::guard('web')->logout();
        	$request->session()->invalidate();
            return back()->with('error','Your credentials are invalid please check.');
        }
        
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        
        if (Auth::check()) {
            $roleId = Auth::user()->role_id;
        } else {
            return redirect()->route('login');
        }
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        
        if($roleId == 1){
            return redirect()->route('admin.login');
        }
        return redirect()->route('login');
    }
}
