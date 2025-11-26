<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class MissionController extends Controller
{
    /**
     * Tampilkan semua data mission.
     */
    public function index()
    {
        try {
            // Menggunakan query builder
            $missions = DB::table('missions')
                ->select('missions.*')
                ->orderByDesc('missions.created_at')
                ->paginate(10);

            Log::info('Menampilkan daftar mission', ['user_id' => Auth::id()]);

            return view('Contents.missions.index', compact('missions'));
        } catch (Exception $e) {
            Log::error('Gagal menampilkan data mission', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    /**
     * Tampilkan form create mission.
     */
    public function create()
    {
        try {
            Log::info('Menampilkan form create mission', ['user_id' => Auth::id()]);
            
            return view('Contents.missions.create');
        } catch (Exception $e) {
            Log::error('Gagal menampilkan form create mission', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat halaman.');
        }
    }

    /**
     * Simpan data baru mission.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'mission_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'mission_type' => 'required|string|max:100|unique:missions,mission_type', 
            'target_value' => 'required|integer|min:0',
            'reward_points' => 'required|integer|min:0',
            'period_type' => 'required|in:daily,weekly,one_time',
            'reset_time' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::table('missions')->insert([
                'mission_name' => $validatedData['mission_name'],
                'description' => $validatedData['description'],
                'mission_type' => $validatedData['mission_type'],
                'target_value' => $validatedData['target_value'],
                'reward_points' => $validatedData['reward_points'],
                'period_type' => $validatedData['period_type'],
                'reset_time' => $validatedData['reset_time'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'is_active' => $request->has('is_active') ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Berhasil menambah mission', [
                'user_id' => Auth::id(),
                'mission_name' => $request->mission_name
            ]);

            return redirect()->route('admin.missions.index')
                ->with('swal_success_crud', 'Mission berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menambah mission', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Gagal menambahkan mission.');
        }
    }

    /**
     * Tampilkan form edit mission.
     */
    public function edit($id)
    {
        try {
            $mission = DB::table('missions')
                ->where('mission_id', $id)
                ->first();

            if (!$mission) {
                Log::warning('Mission tidak ditemukan untuk edit', ['mission_id' => $id]);
                return redirect()->route('admin.missions.index')
                    ->with('error', 'Mission tidak ditemukan.');
            }

            Log::info('Menampilkan form edit mission', [
                'user_id' => Auth::id(),
                'mission_id' => $id
            ]);

            return view('Contents.missions.edit', compact('mission'));
        } catch (Exception $e) {
            Log::error('Gagal menampilkan form edit mission', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat halaman.');
        }
    }

    /**
     * Update mission.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'mission_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'mission_type' => 'required|string|max:100|unique:missions,mission_type,'.$id.',mission_id', 
            'target_value' => 'required|integer|min:0',
            'reward_points' => 'required|integer|min:0',
            'period_type' => 'required|in:daily,weekly,one_time',
            'reset_time' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            // Cek apakah mission ada
            $mission = DB::table('missions')->where('mission_id', $id)->first();
            
            if (!$mission) {
                Log::warning('Mission tidak ditemukan untuk update', ['mission_id' => $id]);
                return redirect()->route('admin.missions.index')
                    ->with('error', 'Mission tidak ditemukan.');
            }

            // Update mission
            DB::table('missions')
                ->where('mission_id', $id)
                ->update([
                    'mission_name' => $validatedData['mission_name'],
                    'description' => $validatedData['description'],
                    'mission_type' => $validatedData['mission_type'],
                    'target_value' => $validatedData['target_value'],
                    'reward_points' => $validatedData['reward_points'],
                    'period_type' => $validatedData['period_type'],
                    'reset_time' => $validatedData['reset_time'],
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'is_active' => $request->has('is_active') ? 1 : 0,
                    'updated_at' => now(),
                ]);

            Log::info('Mission berhasil diperbarui', [
                'user_id' => Auth::id(),
                'mission_id' => $id,
            ]);
            
            return redirect()->route('admin.missions.index')
                ->with('swal_success_crud', 'Mission berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Gagal memperbarui mission', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }
    }

    /**
     * Hapus mission.
     */
    public function destroy($id)
    {
        try {
            // Hapus progress user terkait misi ini (opsional, tergantung logic bisnis)
            DB::table('user_missions')->where('mission_id', $id)->delete();
            
            // Hapus misi utama
            $deleted = DB::table('missions')->where('mission_id', $id)->delete();

            if ($deleted) {
                Log::info('Mission berhasil dihapus', [
                    'user_id' => Auth::id(),
                    'mission_id' => $id
                ]);
                return redirect()->route('admin.missions.index')
                    ->with('swal_success_crud', 'Mission berhasil dihapus.');
            } else {
                Log::warning('Gagal menghapus mission - data tidak ditemukan', ['mission_id' => $id]);
                return back()->with('error', 'Data tidak ditemukan.');
            }
        } catch (Exception $e) {
            Log::error('Gagal menghapus mission', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}