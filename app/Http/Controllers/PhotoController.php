<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/photos');
            $url = Storage::url($path);

            return response()->json([
                'message' => 'Photo uploaded successfully',
                'path' => $url
            ], 201);
        }

        return response()->json(['message' => 'Upload failed'], 500);
    }
}
