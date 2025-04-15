<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{


    // public function __construct()
    // {
    //     $this->middleware('permission:view users')->only('index');
    //     $this->middleware('permission:create users')->only('store');
    //     $this->middleware('permission:edit users')->only('update');
    //     $this->middleware('permission:delete users')->only('destroy');
    // }


    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|string']);

        $user->assignRole($request->role);

        return response()->json(['message' => 'Role assigned successfully']);
    }


    public function promoteToHr($userId)
{
    try {
        $admin = auth()->user();

        if (!$admin->hasRole('admin')) {
            return response()->json([
                'message' => 'Sadece admin kullanıcılar HR yetkisi verebilir.'
            ], 403);
        }

        $user = User::findOrFail($userId);

        if ($user->hasRole('hr')) {
            return response()->json([
                'message' => 'Bu kullanıcı zaten HR.'
            ], 400);
        }

        $user->assignRole(\Spatie\Permission\Models\Role::findByName('hr', 'hr'));

        return response()->json([
            'message' => 'Kullanıcı HR olarak yetkilendirildi.',
            'user' => $user->only(['id', 'name', 'email'])
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'HR yetkisi verilemedi.',
            'error' => $e->getMessage()
        ], 500);
    }
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
