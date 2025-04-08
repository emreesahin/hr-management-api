<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8|confirmed',

                'hire_date' => 'required|date',
                'birth_date' => 'required|date',
                'gender' => 'required|string|in:male,female,other',
                'national_id' => 'required|string',
                'address' => 'required|string',
                'phone' => 'required|string',
                'emergency_contact' => 'required|string',
                'position' => 'required|string',
                'salary' => 'required|numeric|min:0'
            ]);


            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true
            ]);

            $user->assignRole('employee');


            $lastId = \App\Models\Employee::max('id') + 1;
            $employeeNumber = 'EMP-' . now()->format('Ymd') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);


            $user->employee()->create([
                'employee_number' => $employeeNumber,
                'hire_date' => $request->hire_date,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'national_id' => $request->national_id,
                'address' => $request->address,
                'phone' => $request->phone,
                'emergency_contact' => $request->emergency_contact,
                'position' => $request->position,
                'salary' => $request->salary,
                'name' => $request->name // $user->name
            ]);

            return response()->json([
                'message' => 'Kullanıcı ve çalışan başarıyla oluşturuldu.',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kullanıcı kaydedilemedi',
                'error' => $e->getMessage()
            ], 500);
        }
    }



       // Login

       public function login(Request $request)
       {
           $request->validate([
               'email' => 'required|email',
               'password' => 'required'
           ]);

           $user = User::where('email', $request->email)->first();

           if (!$user || !Hash::check($request->password, $user->password)) {
               throw ValidationException::withMessages([
                   'email' => ['Geçersiz kimlik bilgileri']
               ]);
           }

           $user->tokens()->delete();

           $token = $user->createToken('api-token')->plainTextToken;

           return response()->json([
               'message' => 'Giriş başarılı',
               'token' => $token,
               'user' => $user
           ]);
       }

       // Logout

       public function logout(Request $request)
       {
           $request->user()->currentAccessToken()->delete();

           return response()->json([
               'message' => 'Çıkış başarılı'
           ]);
       }

       // User Info

        public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'permissions' => $request->user()->getAllPermissions()->pluck('name')
        ]);
    }

    }

