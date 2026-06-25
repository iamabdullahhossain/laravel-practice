<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * User Registration
     */
    public function register(Request $request)
    {
        // ইনপুট ভ্যালিডেশন
        $request->validate([
            'name' => 'required|string|max:255',
            // regex: /^[a-z0-9_\-\.]+$/ এর মাধ্যমে শুধুমাত্র lowercase, numbers, underscores, dashes, এবং dots অনুমতি দেওয়া হয়েছে
            'username' => 'required|string|unique:users,username|min:3|max:30|regex:/^[a-z0-9_\-\.]+$/',
            'password' => 'required|string|min:6',
        ], [
            'username.regex' => 'Username must be lowercase and contain no spaces. Only letters, numbers, _, -, and . are allowed.',
        ]);

        // ইউজার তৈরি
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        // Sanctum Bearer Token জেনারেট করা
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
            ]
        ], 201);
    }

    /**
     * User Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // ইউজারনেম দিয়ে ইউজার খুঁজে বের করা
        $user = User::where('username', $request->username)->first();

        // পাসওয়ার্ড যাচাই করা
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid login credentials.'
            ], 401);
        }

        // টোকেন জেনারেট করা
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
            ]
        ]);
    }

    /**
     * User Logout
     */
    public function logout(Request $request)
    {
        // ইউজারের বর্তমান ব্যবহৃত টোকেনটি ডিলিট করা
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }
}
