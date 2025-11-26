<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class RewardController extends Controller
{
    /**
     * Tampilkan semua data rewards.
     */
    public function index()
    {
        try {
            $rewards = DB::table('rewards')
                ->orderByDesc(DB::raw('COALESCE(rewards.updated_at, rewards.created_at)'))
                ->paginate(6);

            Log::info('Menampilkan daftar rewards', ['user_id' => Auth::id()]);

            return view('Contents.rewards.index', compact('rewards'));
        } catch (Exception $e) {
            Log::error('Gagal menampilkan data rewards', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    /**
     * Tampilkan form tambah reward.
     */
    public function create()
    {
        Log::info('Membuka form tambah reward', ['user_id' => Auth::id()]);
        return view('Contents.rewards.create');
    }

    /**
     * Simpan data baru reward.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reward_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_required' => 'required|integer|min:0',
            'reward_type' => 'required|in:merchandise,voucher',
            'reward_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            $imagePath = null;
            
            // Upload gambar jika ada
            if ($request->hasFile('reward_image')) {
                $imagePath = $request->file('reward_image')->store('rewards', 'public');
            }

            DB::table('rewards')->insert([
                'reward_name' => $request->reward_name,
                'description' => $request->description,
                'points_required' => $request->points_required,
                'reward_type' => $request->reward_type,
                'reward_image' => $imagePath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Berhasil menambah reward', [
                'user_id' => Auth::id(),
                'reward_name' => $request->reward_name
            ]);

            return redirect()->route('admin.rewards.index')
                ->with('swal_success_crud', 'Reward berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menambah reward', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menambahkan reward.');
        }
    }

    /**
     * Tampilkan form edit reward tertentu.
     */
    public function edit($id)
    {
        try {
            $reward = DB::table('rewards')->where('reward_id', $id)->first();

            if (!$reward) {
                Log::warning('Reward tidak ditemukan untuk diedit', ['reward_id' => $id]);
                return redirect()->route('admin.rewards.index')->with('error', 'Reward tidak ditemukan.');
            }

            Log::info('Membuka form edit reward', ['user_id' => Auth::id(), 'reward_id' => $id]);

            return view('Contents.rewards.edit', compact('reward'));
        } catch (Exception $e) {
            Log::error('Gagal membuka form edit reward', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Update reward.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'reward_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_required' => 'required|integer|min:0',
            'reward_type' => 'required|in:merchandise,voucher',
            'reward_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            $reward = DB::table('rewards')->where('reward_id', $id)->first();

            if (!$reward) {
                Log::warning('Reward tidak ditemukan untuk diupdate', ['reward_id' => $id]);
                return back()->with('error', 'Data tidak ditemukan.');
            }

            $imagePath = $reward->reward_image;

            // Upload gambar baru jika ada
            if ($request->hasFile('reward_image')) {
                // Hapus gambar lama jika ada
                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file('reward_image')->store('rewards', 'public');
            }

            $updated = DB::table('rewards')
                ->where('reward_id', $id)
                ->update([
                    'reward_name' => $request->reward_name,
                    'description' => $request->description,
                    'points_required' => $request->points_required,
                    'reward_type' => $request->reward_type,
                    'reward_image' => $imagePath,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                Log::info('Reward berhasil diperbarui', [
                    'user_id' => Auth::id(),
                    'reward_id' => $id,
                ]);
                return redirect()->route('admin.rewards.index')
                    ->with('swal_success_crud', 'Reward berhasil diperbarui.');
            } else {
                Log::warning('Gagal memperbarui reward - tidak ada perubahan', ['reward_id' => $id]);
                return back()->with('info', 'Tidak ada perubahan data.');
            }
        } catch (Exception $e) {
            Log::error('Gagal memperbarui reward', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }
    }

    /**
     * Hapus reward.
     */
    public function destroy($id)
    {
        try {
            $reward = DB::table('rewards')->where('reward_id', $id)->first();

            if (!$reward) {
                Log::warning('Reward tidak ditemukan untuk dihapus', ['reward_id' => $id]);
                return back()->with('error', 'Data tidak ditemukan.');
            }

            // Hapus gambar jika ada
            if ($reward->reward_image && Storage::disk('public')->exists($reward->reward_image)) {
                Storage::disk('public')->delete($reward->reward_image);
            }

            $deleted = DB::table('rewards')->where('reward_id', $id)->delete();

            if ($deleted) {
                Log::info('Reward berhasil dihapus', [
                    'user_id' => Auth::id(),
                    'reward_id' => $id
                ]);
                return redirect()->route('admin.rewards.index')
                    ->with('swal_success_crud', 'Reward berhasil dihapus.');
            } else {
                Log::warning('Gagal menghapus reward', ['reward_id' => $id]);
                return back()->with('error', 'Gagal menghapus data.');
            }
        } catch (Exception $e) {
            Log::error('Gagal menghapus reward', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}