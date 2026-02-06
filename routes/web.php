<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect('/user/register');
});
Route::group(['prefix' => 'user'], function () {
    Route::get('/login', function () {
        return view('login');
    })->name('login');
    Route::get('/register', function () {
        return view('register');
    })->name('register');
    Route::get('/edit', function () {
        return view('updateUser');
    })->name('user.edit');
});

Route::get('/user/profile', function ( Request $request ) {
    return view('profile', ['user' => $request->id ?? $request->user()]);
})->name('profile');

Route::get('/admin/dashboard', function () {
    return view('admin-dashboard');
})->name('admin.dashboard');
