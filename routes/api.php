<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\PayrollController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/', [DepartmentController::class, 'store']);

// Authenticated Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Department Management
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{department}', [DepartmentController::class, 'show']);
        Route::put('/{department}', [DepartmentController::class, 'update']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);

        // Special Department Endpoints
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

        // Employee-Department Assignment
        Route::post('/{employee}/assign', [EmployeeController::class, 'assignToDepartment']);
        Route::put('/{employee}/departments/{department}', [EmployeeController::class, 'updateDepartmentAssignment']);

        // Additional Employee Endpoints
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



    // HR Dashboard Endpoints
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



