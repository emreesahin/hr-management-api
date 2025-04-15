<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Candidate;

class CandidateController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:candidates,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $candidate = Candidate::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'message' => 'Kayıt başarılı',
            'data' => $candidate->only(['id', 'name', 'email'])
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $candidate = Candidate::where('email', $request->email)->first();

        if (!$candidate || !Hash::check($request->password, $candidate->password)) {
            throw ValidationException::withMessages([
                'email' => ['Geçersiz kimlik bilgileri']
            ]);
        }

        $candidate->tokens()->delete();

        $token = $candidate->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Giriş başarılı',
            'token' => $token,
            'candidate' => $candidate
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Çıkış başarılı']);
    }

    public function uploadCV(Request $request)
    {
        try {
            $request->validate([
                'cv' => 'required|mimes:pdf|max:2048',
            ]);

            // auth()->user() olarak doğrudan candidate gelir çünkü candidate guard ile tanımlı route olacak
            $candidate = auth()->user();

            $path = $request->file('cv')->store('cvs', 'public');

            $candidate->update(['cv_path' => $path]);

            return response()->json([
                'message' => 'CV yüklendi',
                'cv_url' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'CV yükleme hatası',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
