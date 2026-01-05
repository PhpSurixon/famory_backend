<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'bots_enabled' => (bool) Settings::get('bots_enabled', false)
        ]);
    }

    public function toggleBots(Request $request)
    {
        $request->validate([
            'bots_enabled' => 'required|boolean'
        ]);

        Settings::set('bots_enabled', $request->bots_enabled);

        return response()->json([
            'status' => 'success',
            'bots_enabled' => $request->bots_enabled
        ]);
    }
}

