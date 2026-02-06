<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyAvatarRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserAvatarController extends Controller
{

    public function upload(UploadAvatarRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $model = User::find($validated['user_id']);
        if ($model->id !== $user->id) {
            $user = $model;
        }
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

    public function destroy(DestroyAvatarRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $model = User::find($validated['user_id']);
        if($model->id !== $user->id) {
            $user = $model;
        }
        Storage::delete($user->avatar);
        $user->update(['avatar' => null]);
        return response()->json([
            'status'=> 'success',
            'message' => 'Avatar destroy successfully!',
        ]);
    }
}
