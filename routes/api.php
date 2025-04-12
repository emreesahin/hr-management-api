<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\PayrollController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\API\UserController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Authenticated Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

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
        // İleride: Route::get('/pending', [LeaveController::class, 'pending']) gibi onaylama işlemleri
    });

    // HR Dashboard
    Route::prefix('hr')->middleware('role:hr|admin')->group(function () {
        Route::get('/dashboard', 'HrController@dashboard');
        Route::get('/statistics', 'HrController@statistics');
    });
});

// Fallback Route
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint not found',
        'status' => 404
    ], 404);
});
