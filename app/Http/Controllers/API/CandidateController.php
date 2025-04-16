<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Candidate;
use App\Mail\CandidateApprovedMail;
use App\Mail\CandidateRejectedMail;
use Illuminate\Support\Facades\Mail;

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

    public function update(Request $request)
    {
        try {
            $candidate = $request->user(); // auth:candidate ile gelen user

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => "sometimes|email|unique:candidates,email,{$candidate->id}",
                'password' => 'sometimes|string|min:8|confirmed'
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            }

            $candidate->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Bilgileriniz güncellendi.',
                'candidate' => $candidate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Güncelleme sırasında hata oluştu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $candidate = $request->user();
            $candidate->tokens()->delete();
            $candidate->delete();

            return response()->json([
                'status' => true,
                'message' => 'Hesabınız silindi.'
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Silme işlemi başarısız.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index (Request $request) {

        try {

            $candidates = Candidate::latest()->select('id', 'name', 'email', 'created_at', 'cv_path')->get();

            return response()->json([
                'status' => true,
                'message' => 'Adaylar listelendi',
                'candidates' => $candidates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Adaylar listelenemedi',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function show($id) {

        try{

            $candidate = Candidate::select('id', 'name', 'email', 'created_at', 'cv_path')->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Aday detayları',
                'data' => [
                    'name' => $candidate->name,
                    'email' => $candidate->email,
                    'cv_url' => $candidate->cv_path ? asset('storage/' . $candidate->cv_path) : null,
                    'applied_at' => $candidate->created_at->format('d.m.Y H:i')
                ]
                ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Aday detayları getirilirken bir hata oluştu',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function approve($id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->status = 'approved';
        $candidate->save();

        // İsteğe bağlı e-posta bildirimi
        Mail::to($candidate->email)->send(new CandidateApprovedMail($candidate));

        return response()->json([
            'message' => 'Aday onaylandı.',
            'status' => $candidate->status
        ]);
    }


    public function reject($id)
{
    $candidate = Candidate::findOrFail($id);
    $candidate->status = 'rejected';
    $candidate->save();

    // İsteğe bağlı e-posta bildirimi
    Mail::to($candidate->email)->send(new CandidateRejectedMail($candidate));

    return response()->json([
        'message' => 'Aday reddedildi.',
        'status' => $candidate->status
    ]);
}


public function updateNote(Request $request, $id)
{
    $request->validate([
        'note' => 'required|string'
    ]);

    $candidate = Candidate::findOrFail($id);
    $candidate->note = $request->note;
    $candidate->save();

    return response()->json([
        'message' => 'Not eklendi',
        'note' => $candidate->note
    ]);
}


public function toggleFavorite($id)
{
    $candidate = Candidate::findOrFail($id);
    $candidate->is_favorite = !$candidate->is_favorite;
    $candidate->save();

    return response()->json([
        'message' => $candidate->is_favorite ? 'Favorilere eklendi' : 'Favorilerden çıkarıldı',
        'is_favorite' => $candidate->is_favorite
    ]);
}


}
