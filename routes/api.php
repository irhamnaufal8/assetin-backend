<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LoanController;

Route::middleware(['auth:sanctum'])->get('/loans/user', [LoanController::class, 'getUserLoans']);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('inventories', InventoryController::class);
Route::apiResource('loans', LoanController::class);
Route::apiResource('articles', ArticleController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
Route::middleware(['auth:sanctum', 'can:approve-admin'])->post('/users/{user}/approve-admin', [UserController::class, 'approveAdmin']);
Route::middleware(['auth:sanctum', 'can:approve-student'])->post('/users/{user}/approve-student', [UserController::class, 'approveStudent']);
Route::get('/users/pending', [UserController::class, 'listPendingUsers'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->get('/users/me', [AuthController::class, 'me']);

Route::get('/inventories/category/{categoryId}', [InventoryController::class, 'getByCategory']);

Route::middleware(['auth:sanctum', 'can:approve-student'])->post('/loans/{loan}/approve', [LoanController::class, 'approveLoan']);
Route::middleware(['auth:sanctum', 'can:approve-student'])->post('/loans/{loan}/start', [LoanController::class, 'startLoan']);
Route::middleware(['auth:sanctum', 'can:approve-student'])->post('/loans/{loan}/finish', [LoanController::class, 'finishLoan']);