<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:view users')->only('index');
        $this->middleware('permission:create users')->only('store');
        $this->middleware('permission:edit users')->only('update');
        $this->middleware('permission:delete users')->only('destroy');
    }


    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|string']);

        $user->assignRole($request->role);

        return response()->json(['message' => 'Role assigned successfully']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
