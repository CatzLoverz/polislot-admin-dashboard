<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Rules\NotCurrentPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman form untuk mengedit profil pengguna.
     */
    public function edit()
    {
        $user = Auth::user();
        Log::info('[ProfileController@edit] Menampilkan form edit profil.', ['user_id' => $user->user_id]);
        
        return view('Contents.Profile.index', compact('user'));
    }


    /**
     * Memproses permintaan untuk memperbarui profil pengguna.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        Log::info('[ProfileController@update] Menerima permintaan update profil.', ['user_id' => $user->user_id]);

        $passwordRules = [
            'required',
            'confirmed',
            new NotCurrentPassword(),
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

        DB::beginTransaction();
        try {
            $validatedData = $request->validate($rules);
            Log::info('[ProfileController@update] Validasi data profil berhasil.', ['user_id' => $user->user_id]);

            if ($request->hasFile('avatar')) {
                Log::info('[ProfileController@update] Mengunggah avatar baru.', ['user_id' => $user->user_id]);
                if ($user->avatar && $user->avatar !== 'default_avatar.jpg' && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $path;
            }
            
            // update password jika diisi
            if (isset($validatedData['new_password'])) {
                $user->password = Hash::make($validatedData['new_password']);
                Log::info('[ProfileController@update] Password diupdate.', ['user_id' => $user->user_id]);
            }

            /** @var \App\Models\User $user */
            $user->name = $validatedData['name'];
            $user->save();

            Auth::login($user);
            DB::commit();

            Log::info('[ProfileController@update] SUKSES: Profil berhasil diperbarui.', ['user_id' => $user->user_id]);
            return redirect()->route('profile.edit')->with('swal_success_crud', 'Profil Anda berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('[ProfileController@update] GAGAL: Validasi data gagal.', [ 'user_id' => $user->user_id, 'errors' => $e->errors() ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ProfileController@update] GAGAL: Terjadi error sistem saat memperbarui profil.', [ 'user_id' => $user->user_id, 'error' => $e->getMessage() ]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan pada server saat memperbarui profil.');
        }
    }
}