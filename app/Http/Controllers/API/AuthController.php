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
                'password' => 'required|string|min:8|confirmed'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true
            ]);

            $user->assignRole(\Spatie\Permission\Models\Role::findByName('employee', 'employee'));


            return response()->json([
                'message' => 'Kullanıcı başarıyla kaydedildi',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kayıt sırasında hata oluştu',
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

           $token = $user->createToken('api-token', ['*'])->plainTextToken;

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

