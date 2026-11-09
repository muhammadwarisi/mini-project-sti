<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// --- Protected Routes (Harus Login / Bawa Token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    // Ambil data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
