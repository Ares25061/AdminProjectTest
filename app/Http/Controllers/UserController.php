<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\EditUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\SetRoleRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Roles;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::paginate($request->per_page, page:$request->page);
        if ($users->count() === 0) {
            return response()->json(['error' => 'Users not found'], 404);
        }

        return response()->json(['status'=> 'success','users' => $users], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $user->refresh();
        return response()->json([
            'status'=> 'success',
            'message' => 'User created!',
            'user' => $user,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $user = User::with('bans')->find($id);
        if (is_null($user)) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json([
            'status'=> 'success',
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EditUserRequest $request)
    {
        $user = Auth::user();
        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        $validated = $request->validated();
        $user->update([
            ...$validated,
            'email_verified_at'=> !empty($validated['email']) ? null : $user->email_verified_at,
        ]);
        return response()->json([
            'status'=> 'success',
            'message' => 'User edited!',
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $validated = $request->validated();
        $user->update([
            ...$validated,
            'email_verified_at'=> !empty($validated['email']) ? null : $user->email_verified_at,
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $user->password
        ]);
        return response()->json([
            'status'=> 'success',
            'message' => 'User updated!',
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json([
            'status'=> 'success',
            'message' => 'User deleted',
        ]);
    }

    public function register(CreateUserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function login(LoginUserRequest $request)
    {
        $validated = $request->validated();
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password']
        ];
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
    public function setRole(int $id,SetRoleRequest $request)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $user->role=Roles::from($request->role);
        $user->save();
        return response()->json([
            'status'=> 'success',
            'message' => 'User set role!',
            'user' => $user
        ]);
    }
}
