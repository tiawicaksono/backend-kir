<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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
        try {
            $user = $request->user();
            if ($user) {
                Auth::guard('web')->logout(); // Logout user
                // Hapus token session (kalau pakai token)
                $request->session()->invalidate(); // Hapus session
                $request->session()->regenerateToken(); // Regenerate CSRF token
            }

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
