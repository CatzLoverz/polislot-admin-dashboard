<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Rules\NotCurrentPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Menampilkan profil pengguna (API JSON)
     */
    public function show()
    {
        /** @var User $user */
        $user = Auth::user();
        Log::info('[API ProfileController@show] Mengambil data profil.', ['user_id' => $user->user_id]);

        // Masking email (contoh: rafi***@gmail.com)
        $email = $user->email;
        $emailParts = explode('@', $email);
        $maskedLocal = substr($emailParts[0], 0, 3) . str_repeat('*', max(strlen($emailParts[0]) - 3, 0));
        $maskedEmail = $maskedLocal . '@' . $emailParts[1];

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil.',
            'data' => [
                'name' => $user->name,
                'email' => $maskedEmail,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : asset('storage/default_avatar.jpg'),
            ],
        ]);
    }

    public function update(Request $request)
{
    /** @var User $user */
    $user = Auth::user();
    Log::info('[API ProfileController@update] Menerima permintaan update profil (PUT).', ['user_id' => $user->user_id]);

    // Pastikan request dari multipart/form-data tetap bisa divalidasi
    if ($request->isMethod('put') || $request->isMethod('patch')) {
        $request->merge($request->all());
    }

    // Menggunakan koleksi rule
    $passwordRules = [
        'required',
        'confirmed',
        new NotCurrentPassword(), // Tidak boleh sama dengan password lama
        PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
    ];

    $rules = [
        'name' => ['required', 'string', 'max:255'],
        'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
    ];

    // Jika user ingin mengganti password
    if ($request->filled('new_password')) {
        // Menggunakan rule 'current_password' bawaan Laravel
        $rules['current_password'] = ['required', 'current_password']; 
        $rules['new_password'] = $passwordRules;
    }

    DB::beginTransaction();
    try {
        // Validasi data
        $validatedData = $request->validate($rules);
        Log::info('[API ProfileController@update] Validasi data berhasil.', ['user_id' => $user->user_id]);

        // ... (Logika Upload Avatar dan Update Nama/Password) ...
        
        // Upload avatar baru (jika ada)
        if ($request->hasFile('avatar')) {
            Log::info('[API ProfileController@update] Mengunggah avatar baru.', ['user_id' => $user->user_id]);

            // Hapus avatar lama (kecuali default)
            if ($user->avatar && $user->avatar !== 'default_avatar.jpg' && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // Update password jika diisi
        if (isset($validatedData['new_password'])) {
            $user->password = Hash::make($validatedData['new_password']);
            Log::info('[API ProfileController@update] Password diupdate.', ['user_id' => $user->user_id]);
        }

        // Update nama
        $user->name = $validatedData['name'];
        $user->save();
        
        DB::commit();

        Log::info('[API ProfileController@update] SUKSES: Profil berhasil diperbarui.', ['user_id' => $user->user_id]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                // Pastikan asset() bekerja dengan benar untuk URL storage
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : asset('storage/default_avatar.jpg'),
            ],
        ]);
    } catch (ValidationException $e) {
        DB::rollBack();
        
        $errors = $e->errors();
        $customErrorMessage = 'Validasi data gagal.';

        // --- PRIORITAS PENENTUAN PESAN TUNGGAL (REVISI) ---
        
        // 1. PRIORITAS TINGGI: Password lama salah ('current_password' rule gagal)
        if (isset($errors['current_password'])) {
            // Karena rule 'current_password' Laravel biasanya mengembalikan pesan "The current password field is incorrect." 
            // atau yang sudah dilokalisasi: "Kata sandi lama salah."
            $customErrorMessage = 'Kata sandi lama salah.'; 
        } 
        // 2. PRIORITAS KEDUA: Error pada Password Baru
        else if (isset($errors['new_password'])) {
            $newPasswordErrors = collect($errors['new_password']);

            // 2A. Cek Konfirmasi tidak cocok ('confirmed' rule). Cari kata kunci dari pesan default Laravel.
            $isConfirmationError = $newPasswordErrors->contains(function ($message) {
                // Mencari kata kunci 'confirmation' (English) atau 'konfirmasi' (Indo) jika lokalisasi gagal.
                return str_contains(strtolower($message), 'confirmation') || str_contains(strtolower($message), 'konfirmasi'); 
            });

            // 2B. Cek Password sama dengan yang lama ('NotCurrentPassword' rule). Cari kata kunci dari pesan default rule.
            $isSameAsCurrent = $newPasswordErrors->contains(function ($message) {
                // Mencari kata kunci 'different from the current' atau 'tidak boleh sama' dari pesan custom rule.
                return str_contains(strtolower($message), 'different from the current') || str_contains(strtolower($message), 'tidak boleh sama'); 
            });
            
            // Urutan prioritas penentuan pesan untuk new_password
            if ($isConfirmationError) {
                // Prioritas 1: Konfirmasi tidak cocok
                $customErrorMessage = 'Konfirmasi kata sandi baru tidak cocok.';
            } else if ($isSameAsCurrent) {
                // Prioritas 2: Password Baru Sama dengan Lama (INI YANG ANDA CARI)
                $customErrorMessage = 'Kata sandi baru tidak boleh sama dengan kata sandi sebelumnya.';
            } else {
                // Prioritas 3: Kompleksitas (Min, MixedCase, Numbers, Symbols, Zxcvbn)
                // Ini adalah fallback default untuk semua kegagalan validasi kompleksitas lainnya
                $customErrorMessage = 'Kata sandi baru tidak valid. Pastikan minimal 8 karakter dan mengandung huruf besar/kecil, angka, dan simbol.';
            }

        }
        // 3. PRIORITAS KETIGA: Error pada Nama
        else if (isset($errors['name'])) {
            // Ambil pesan error pertama dari field name
            $customErrorMessage = $errors['name'][0];
        }
        // 4. PRIORITAS KEEMPAT: Error pada Avatar
        else if (isset($errors['avatar'])) {
            // Ambil pesan error pertama dari field avatar
            $customErrorMessage = $errors['avatar'][0];
        }
        // 5. DEFAULT: Ambil pesan error pertama dari field manapun yang gagal
        else {
            $firstErrorKey = array_key_first($errors);
            // Pastikan ada error sebelum mencoba mengambil index [0]
            if ($firstErrorKey !== null && !empty($errors[$firstErrorKey])) {
                $customErrorMessage = $errors[$firstErrorKey][0];
            }
        }

        Log::warning('[API ProfileController@update] GAGAL: Validasi data gagal.', [
            'user_id' => $user->user_id,
            'errors' => $errors,
        ]);

        // Mengembalikan respons dengan satu pesan error tunggal
        return response()->json([
            'success' => false,
            'message' => $customErrorMessage, // HANYA SATU PESAN
            'errors' => $errors, // Tetap sertakan detail errors untuk debugging
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('[API ProfileController@update] GAGAL: Terjadi error sistem.', [
            'user_id' => $user->user_id,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan pada server saat memperbarui profil.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}