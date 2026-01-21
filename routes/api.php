<?php

use App\Http\Controllers\BanController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::apiResource('/user', UserController::class);
Route::apiResource('/ban', BanController::class);

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::patch('/user/setRole/{id}', [UserController::class, 'setRole']);
Route::post('/user/edit', [UserController::class, 'edit']);
Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/refresh', [UserController::class, 'refresh']);

});
