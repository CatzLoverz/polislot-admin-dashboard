<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Rules\NotCurrentPassword;
use App\Services\MissionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    protected $missionService;

    /**
     * Konstruktor.
     *
     * @param MissionService $missionService Service misi
     */
    public function __construct(MissionService $missionService)
    {
        $this->missionService = $missionService;
    }

    /**
     * Menampilkan data profil pengguna.
     *
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->sendSuccess('Data profil berhasil diambil.', $this->formatUser($user));
        } catch (Exception $e) {
            Log::error('Gagal menampilkan profil. Error: '.$e->getMessage());

            return $this->sendError('Gagal mengambil data profil.', 500);
        }
    }

    /**
     * Memperbarui data profil pengguna (nama/avatar/password).
     *
     * @param  Request  $request  Input (name, avatar, new_password, etc)
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
                new NotCurrentPassword,
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
                    try {
                        $this->missionService->updateProgress($user->user_id, 'PROFILE_UPDATE');

                    } catch (Exception $e) {
                        // Kita catch error misi agar tidak membatalkan update profil utama
                        // Log errornya saja untuk debugging
                        Log::error('Gagal trigger misi: '.$e->getMessage());
                    }
                }

                // 2. Password
                if ($request->filled('new_password')) {
                    $user->password = Hash::make($request->new_password);
                }

                // 3. Nama
                $user->name = $request->name;
                $user->save();
                Log::info('Profil berhasil diperbarui.', ['user_id' => $user->user_id]);

                return $this->sendSuccess('Profil berhasil diperbarui.', ['user' => $this->formatUser($user)]);
            });

        } catch (ValidationException $e) {
            Log::warning('Validasi error.', ['errors' => $e->errors()]);

            return $this->sendValidationError($e);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return $this->sendError('Gagal memperbarui profil.', 500);
        }
    }

    /**
     * Menghapus akun pengguna secara permanen (hard delete).
     *
     * Seluruh data terkait (misi, reward, riwayat, validasi, komentar,
     * FAQ) turut terhapus via foreign key cascade pada level database.
     *
     * @param  Request  $request  Input berisi confirmation (email/nama pengguna)
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        $input = strtolower(trim($validated['confirmation']));
        $matches = hash_equals(strtolower($user->email), $input)
            || hash_equals(strtolower($user->name), $input);

        if (! $matches) {
            return $this->sendError('Konfirmasi tidak sesuai.', 422);
        }

        try {
            return DB::transaction(function () use ($user) {
                $user->tokens()->delete();

                if ($user->avatar && $user->avatar !== 'default_avatar.jpg' && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $user->delete();
                Log::info('Akun berhasil dihapus.', ['user_id' => $user->user_id]);

                return $this->sendSuccess('Akun berhasil dihapus.');
            });
        } catch (Exception $e) {
            Log::error('Gagal menghapus akun. Error: '.$e->getMessage());

            return $this->sendError('Gagal menghapus akun.', 500);
        }
    }
}
