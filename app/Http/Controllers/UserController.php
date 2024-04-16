<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function listPendingUsers(Request $request)
    {
        // Memperbolehkan superadmin dan admin melihat daftar siswa yang pending
        $userRole = $request->user()->role;

        if (!in_array($userRole, ['superadmin', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $role = $request->query('role');
        $validRoles = ['siswa', 'admin']; // Valid roles yang bisa difilter

        // Batasi admin untuk hanya bisa melihat siswa
        if ($userRole === 'admin' && ($role !== 'siswa' || !$role)) {
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

