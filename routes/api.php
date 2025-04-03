<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(['auth:sanctum'])->group(function(){
    Route::apiResource('users', UserController::class);

    Route::get('/roles', function(){
        return response()->json(\Spatie\Permission\Model\Role::all());
    })->middleware('permission:view roles');
});


