<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar' => 'sometimes|string|nullable'
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->filled('avatar')) {
            $user->avatar = $request->avatar;
        } else if ($request->exists('avatar')) {
            $user->avatar = null;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }

    public function listPendingUsers(Request $request)
    {
        // Memperbolehkan superadmin dan admin melihat daftar siswa yang pending
        $userRole = $request->user()->role;

        // Awal validasi akses
        if (!in_array($userRole, ['superadmin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $role = $request->query('role');
        $validRoles = ['siswa', 'admin']; // Valid roles yang bisa difilter

        // Logika penanganan role default untuk admin
        if ($userRole === 'admin' && !$role) {
            // Jika admin tidak menyertakan filter role, default ke 'siswa'
            $role = 'siswa';
        }

        // Batasi admin untuk hanya bisa melihat siswa kecuali dinyatakan lain
        if ($userRole === 'admin' && !in_array($role, ['siswa'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($role && !in_array($role, $validRoles)) {
            return response()->json(['message' => 'Invalid role specified'], 400);
        }

        // Query untuk pengguna dengan status 'pending' dan role yang sesuai, jika ada
        $users = User::where('status', 'pending')
                    ->when($role, function ($query) use ($role) {
                        return $query->where('role', $role);
                    })
                    ->get();

        return response()->json($users);
    }

    public function approveAdmin(Request $request, User $user)
    {
        if ($request->user()->role === 'superadmin' && $user->role === 'admin') {
            $user->status = 'approved';
            $user->save();
            return response()->json(['message' => 'Admin approved successfully']);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function approveStudent(Request $request, User $user)
    {
        if (($request->user()->role === 'admin' || $request->user()->role === 'superadmin') && $user->role === 'siswa') {
            $user->status = 'approved';
            $user->save();
            return response()->json(['message' => 'Student approved successfully']);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}

