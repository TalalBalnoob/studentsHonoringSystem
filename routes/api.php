<?php

use App\Http\Controllers\Admin\AdminFieldController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Public student form submission - limited to 10 per hour per IP
Route::post('/students', [StudentController::class, 'store'])
    ->middleware('throttle:student-form');

// Admin login - limited to 10 attempts per minute per username
Route::post('/admin/login', [LoginController::class, 'login'])
    ->middleware('throttle:login');

// Admin endpoints - 60 requests/min per user
Route::middleware(['auth:sanctum', 'admin', 'throttle:api'])->group(function () {
    // Student management
    Route::get('/admin/students', [AdminStudentController::class, 'index']);
    Route::get('/admin/students/export/csv', [AdminStudentController::class, 'exportCsv']);
    Route::get('/admin/students/{id}', [AdminStudentController::class, 'show']);
    Route::post('/admin/students', [AdminStudentController::class, 'store']);
    Route::put('/admin/students/{id}', [AdminStudentController::class, 'update']);
    Route::delete('/admin/students/{id}', [AdminStudentController::class, 'destroy']);

    Route::post('/logout', [LoginController::class, 'logout']);
});

// Authenticated user endpoint - 60 req/min if authenticated, 20 if guest
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'throttle:api']);
