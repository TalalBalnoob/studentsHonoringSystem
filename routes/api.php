<?php

use App\Http\Controllers\Admin\AdminFieldController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);

// Public student form submission endpoint
Route::post('/students', [StudentController::class, 'store']);

// Admin endpoints (require authentication and admin role)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Student management
    Route::get('/admin/students', [AdminStudentController::class, 'index']);
    Route::get('/admin/students/export/csv', [AdminStudentController::class, 'exportCsv']);
    Route::get('/admin/students/{id}', [AdminStudentController::class, 'show']);
    Route::post('/admin/students', [AdminStudentController::class, 'store']);
    Route::put('/admin/students/{id}', [AdminStudentController::class, 'update']);
    Route::delete('/admin/students/{id}', [AdminStudentController::class, 'destroy']);

    // Dynamic fields management
    Route::get('/admin/fields', [AdminFieldController::class, 'index']);
    Route::post('/admin/fields', [AdminFieldController::class, 'store']);
    Route::delete('/admin/fields/{id}', [AdminFieldController::class, 'destroy']);

    Route::post('/logout', [LoginController::class, 'logout']);
});

// Authenticated user endpoint
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
