<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Rules\NotCurrentPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Menampilkan data profil pengguna.
     * * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            Log::info('[API ProfileController@show] Berhasil menampilkan profil.');
            return $this->sendSuccess('Data profil berhasil diambil.', $this->formatUser($user));
        } catch (\Exception $e) {
            Log::error('[API ProfileController@show] Gagal menampilkan profil. Error: ' . $e->getMessage());
            return $this->sendError('Gagal mengambil data profil.', 500);
        }
    }

    /**
     * Memperbarui data profil pengguna.
     * * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        // Fix method PUT form-data
        if ($request->isMethod('put') || $request->isMethod('patch')) {
             // Laravel handle ini otomatis, tapi request harus multipart/form-data
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];

        if ($request->filled('new_password')) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['new_password'] = [
                'required', 
                'confirmed', 
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
                new NotCurrentPassword(),
            ];
        }

        try {
            return DB::transaction(function () use ($request, $rules, $user) {
                $validated = $request->validate($rules);

                // 1. Upload Avatar
                if ($request->hasFile('avatar')) {
                    if ($user->avatar && $user->avatar !== 'default_avatar.jpg' && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    $user->avatar = $request->file('avatar')->store('avatars', 'public');
                }

                // 2. Password
                if ($request->filled('new_password')) {
                    $user->password = Hash::make($request->new_password);
                }

                // 3. Nama
                $user->name = $request->name;
                $user->save();
                Log::info('[API ProfileController@update] Profil berhasil diperbarui.');
                return $this->sendSuccess('Profil berhasil diperbarui.', ['user' => $this->formatUser($user)]);
            });

        } catch (ValidationException $e) {
            return $this->sendValidationError($e);
        } catch (\Exception $e) {
            Log::error('[API ProfileController@update] Error: ' . $e->getMessage());
            return $this->sendError('Gagal memperbarui profil.', 500);
        }
    }

    // --- HELPER ---

    private function sendSuccess($message, $data = null, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function sendError($message, $code = 400, $data = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function sendValidationError(ValidationException $e)
    {
        $errors = $e->errors();
        $message = collect($errors)->flatten()->first();
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    // Format User Konsisten (Wajib ada user_id)
    private function formatUser($user)
    {
        return [
            'user_id' => (int) $user->user_id, // Casting ke int agar aman
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
        ];
    }
}