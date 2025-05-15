<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FcmController extends Controller
{
    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->user('api');
        $user->fcm_token = $request->token;
        $user->save();

        return response()->json(['message' => 'Device token updated successfully']);
    }
}
