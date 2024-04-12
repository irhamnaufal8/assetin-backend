<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
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

