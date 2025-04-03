<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\AuthController;



// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth Routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Department Management Routes
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])
            ->middleware('permission:view departments');

        Route::post('/', [DepartmentController::class, 'store'])
            ->middleware('permission:create departments');

        Route::get('/{department}', [DepartmentController::class, 'show'])
            ->middleware('permission:view departments')
            ->where('department', '[0-9]+');

        Route::put('/{department}', [DepartmentController::class, 'update'])
            ->middleware('permission:edit departments')
            ->where('department', '[0-9]+');

        Route::delete('/{department}', [DepartmentController::class, 'destroy'])
            ->middleware('permission:delete departments')
            ->where('department', '[0-9]+');

        // Special Department Endpoints
        Route::post('/{department}/assign', [DepartmentController::class, 'assignEmployee'])
            ->middleware('permission:assign employees');

        Route::get('/{department}/hierarchy', [DepartmentController::class, 'hierarchy'])
            ->middleware('permission:view departments');

        Route::get('/{department}/report', [DepartmentController::class, 'report'])
            ->middleware('permission:view department reports');
    });

});

// Fallback Route
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint not found'
    ], 404);
});
