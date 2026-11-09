<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// --- Protected Routes (Harus Login / Bawa Token Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    // Ambil data user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Fitur Tugas (Index & Show bisa diakses Guru & Siswa)
    Route::get('/tasks', [TaskController::class, 'index']);

    // Fitur CRUD Tugas (Hanya Guru yang bisa berdasarkan Policy)
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Fitur Siswa ubah status tugas selesai/belum
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'toggleComplete']);
});
