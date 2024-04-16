<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|confirmed',
                'role' => 'required|in:siswa,admin',
                'nis' => 'required_if:role,siswa|unique:users,nis',
                'nip' => 'required_if:role,admin,superadmin|unique:users,nip'
            ]);
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 'pending',
                'nis' => $request->role === 'siswa' ? $request->nis : null,
                'nip' => ($request->role === 'admin' || $request->role === 'superadmin') ? $request->nip : null
            ]);
    
            return response()->json($user, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $credentials = $request->only('email', 'password');

        // Mencoba autentikasi
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Autentikasi berhasil, cek status pengguna
        $user = $request->user();
        if ($user->status == 'pending') {
            return response()->json([
                'message' => 'Account is pending approval. Please contact an administrator.'
            ], 403); // 403 Forbidden, karena akses ditolak
        }

        // Jika status pengguna bukan 'pending', keluarkan token
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
