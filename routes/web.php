<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/create', function () {
        return view('createUser');
    })->name('user.create');
    Route::get('/edit', function () {
        return view('updateUser');
    })->name('user.edit');
});

Route::get('/user/profile', function () {
    return view('profile');
})->name('profile');
