<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserAvatarController extends Controller
{

    public function upload(Request $request)
    {
        $user = Auth::user();
        if(!is_null($user->avatar) && Storage::exists($user->avatar) ) {
            Storage::delete($user->avatar);
        }
        $path = $request->file('avatar')->store('avatars');
        $user->update(['avatar' => $path]);
        return response()->json([
            'status'=> 'success',
            'message' => 'Avatar uploaded successfully!',
            'path' => $path,
        ]);
    }

    public function destroy()
    {
        $user = Auth::user();
        Storage::delete($user->avatar);
        $user->update(['avatar' => null]);
        return response()->json([
            'status'=> 'success',
            'message' => 'Avatar destroy successfully!',
        ]);
    }
}
