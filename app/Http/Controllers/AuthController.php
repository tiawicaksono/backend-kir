<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'menus' => $user->getEffectiveMenus()
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        // ✅ VALIDATION
        $validated = $request->validate([
            'old_password' => ['required'],
            'password' => [
                'required',
                'confirmed', // harus kirim password_confirmation dari FE
                Password::min(6)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        // ✅ CEK OLD PASSWORD
        if (!Hash::check($validated['old_password'], $user->password)) {
            return response()->json([
                'message' => 'Old password is incorrect'
            ], 422);
        }

        // ✅ UPDATE PASSWORD
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'message' => 'Password successfully changed'
        ]);
    }

    public function loginToken(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json(['token' => $token]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    public function logoutToken(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // revoke semua token
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Logged out'
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->session()->regenerate(); // penting biar cookie Laravel diupdate

        return response()->json(['message' => 'Logged in']);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully'
        ])->withCookie(cookie()->forget('laravel-session'));
    }
}
