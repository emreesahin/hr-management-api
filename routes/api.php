<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\PayrollController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CandidateController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::prefix('candidate')->group(function () {
    Route::post('/register', [CandidateController::class, 'register']);
    Route::post('/login', [CandidateController::class, 'login']);

});

// Authenticated Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me/update', [AuthController::class, 'update']);
    Route::delete('/me/destroy', [AuthController::class, 'destroy']);

    // User Management
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:view users'); //
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:create users'); //
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:edit users'); //
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:delete users'); //

    // Promote User to HR (sadece admin görsün diye group içinde zaten)
    Route::post('/users/{user}/promote-hr', [UserController::class, 'promoteToHr']);

    // Department Management
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::put('/{department}', [DepartmentController::class, 'update']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);
        Route::post('/{department}/assign', [DepartmentController::class, 'assignEmployee']);
        Route::get('/{department}/hierarchy', [DepartmentController::class, 'hierarchy']);
        Route::get('/{department}/report', [DepartmentController::class, 'report']);
        Route::get('/{department}/cost-analysis', [DepartmentController::class, 'costAnalysis']);
    });

    // Employee Management
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::get('/{employee}', [EmployeeController::class, 'show']);
        Route::put('/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
        Route::post('/{employee}/assign', [EmployeeController::class, 'assignToDepartment']);
        Route::put('/{employee}/departments/{department}', [EmployeeController::class, 'updateDepartmentAssignment']);
        Route::get('/{employee}/history', [EmployeeController::class, 'assignmentHistory']);
    });

    // Payroll Management
    Route::prefix('payrolls')->group(function () {
        Route::get('/', [PayrollController::class, 'index']);
        Route::get('/{id}/download', [PayrollController::class, 'download']);
        Route::middleware('role:admin|hr')->group(function () {
            Route::post('/', [PayrollController::class, 'store']);
        });
    });

    // Leave Management (izin sistemi)
    Route::prefix('leaves')->group(function () {
        Route::post('/', [LeaveController::class, 'store']);
        Route::put('/{id}/approve', [LeaveController::class, 'approve']);
        Route::put('/{id}/reject', [LeaveController::class, 'reject']);
    });

    // // HR Dashboard
    // Route::prefix('hr')->middleware('role:hr|admin')->group(function () {
    //     Route::get('/dashboard', 'HrController@dashboard');
    //     Route::get('/statistics', 'HrController@statistics');
    // });

    // Candidate Management
    Route::middleware('auth:candidate')->prefix('candidates')->group(function () {
        Route::post('/logout', [CandidateController::class, 'logout']);
        Route::post('/cv', [CandidateController::class, 'uploadCV']);
        Route::put('/me/update', [CandidateController::class, 'update']);
        Route::delete('/me/destroy', [CandidateController::class, 'destroy']);
    });

    Route::middleware(['role:hr|admin'])->prefix('candidates-hr')->group(function () {
        Route::get('/candidates', [CandidateController::class, 'index']);
        Route::get('/candidates/{id}', [CandidateController::class, 'show']);
        Route::put('/candidates/{id}/approve', [CandidateController::class, 'approve']);
        Route::put('/candidates/{id}/reject', [CandidateController::class, 'reject']);
        Route::put('/candidates/{id}/note', [CandidateController::class, 'updateNote']);
        Route::post('/candidates/{id}/favorite', [CandidateController::class, 'toggleFavorite']);
    });


});

// Fallback Route
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint not found',
        'status' => 404
    ], 404);
});
