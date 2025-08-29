<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // User information view
    public function userInformation()
    {
        return view('admin.userInformation');
    }

    // Delete account view
    public function deleteAccount(Request $request)
    {
        $status = $request->status;
        return view('admin.deleteAccount', compact('status'));
    }
}
