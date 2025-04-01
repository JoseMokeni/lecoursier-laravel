<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Takes the credentials and returns a token
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        // Validate the credentials
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        // Check if the user exists
        $user = User::where('username', $credentials['username'])->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        // Check if the password is correct
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Check if the user is active
        if ($user->status !== 'active') {
            return response()->json(['error' => 'User is inactive'], 403);
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        // Generate a token for the user using sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the token and user information
        return response()->json([
            'token' => $token,
            'user' => [
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }
}
