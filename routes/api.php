<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::apiResource('articles', ArticleController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
Route::middleware(['auth:sanctum', 'can:approve-admin'])->post('/users/{user}/approve-admin', [UserController::class, 'approveAdmin']);
Route::middleware(['auth:sanctum', 'can:approve-student'])->post('/users/{user}/approve-student', [UserController::class, 'approveStudent']);