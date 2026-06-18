<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\NotCurrentPassword;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman form untuk mengedit profil pengguna.
     */
    public function edit(): View
    {
        $user = Auth::user();

        return view('Contents.Profile.index', compact('user'));
    }

    /**
     * Memproses permintaan untuk memperbarui profil pengguna.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        Log::info('Menerima permintaan update profil.', ['user_id' => $user->user_id]);

        $passwordRules = [
            'required',
            'confirmed',
            new NotCurrentPassword,
            PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
        ];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];

        if ($request->filled('new_password')) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['new_password'] = $passwordRules;
        }

        try {
            return DB::transaction(function () use ($request, $user, $rules) {
                $validatedData = $request->validate($rules);
                Log::info('Validasi data profil berhasil.', ['user_id' => $user->user_id]);

                if ($request->hasFile('avatar')) {
                    Log::info('Mengunggah avatar baru.', ['user_id' => $user->user_id]);
                    if ($user->avatar && $user->avatar !== 'default_avatar.jpg' && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    $path = $request->file('avatar')->store('avatars', 'public');
                    $user->avatar = $path;
                }

                // update password jika diisi
                if (isset($validatedData['new_password'])) {
                    $user->password = Hash::make($validatedData['new_password']);
                    Log::info('Password diupdate.', ['user_id' => $user->user_id]);
                }

                /** @var User $user */
                $user->name = $validatedData['name'];
                $user->save();

                Auth::login($user);

                Log::info('Profil berhasil diperbarui.', ['user_id' => $user->user_id]);

                return redirect()->route('profile.edit')->with('swal_success_crud', 'Profil Anda berhasil diperbarui.');
            });

        } catch (ValidationException $e) {
            Log::warning('Validasi data gagal.', ['user_id' => $user->user_id, 'errors' => $e->errors()]);

            return back()->withErrors($e->errors())->withInput();
        } catch (Exception $e) {
            Log::error('Terjadi error sistem saat memperbarui profil.', ['user_id' => $user->user_id, 'error' => $e->getMessage()]);

            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server saat memperbarui profil.');
        }
    }
}
