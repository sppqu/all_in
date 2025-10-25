<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('user.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'nomor_wa' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->nomor_wa = $request->nomor_wa;

        if ($request->hasFile('avatar')) {
            // delete old
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->save();

        \App\Helpers\ActivityLogger::log('update', 'profile', 'Update profile', [], 'user', $user->id);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}


