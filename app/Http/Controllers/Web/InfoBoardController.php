<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class InfoBoardController extends Controller
{
    /**
     * Tampilkan semua data info board.
     */
    public function index()
    {
        try {
            $infoBoards = DB::table('info_board')
                ->join('users', 'info_board.admin_id', '=', 'users.user_id')
                ->select('info_board.*', 'users.name as admin_name')
                ->orderByDesc(DB::raw('COALESCE(info_board.updated_at, info_board.created_at)'))
                ->paginate(3);

            Log::info('Menampilkan daftar info board', ['user_id' => Auth::id()]);

            return view('Contents.info_board.index', compact('infoBoards'));
        } catch (Exception $e) {
            Log::error('Gagal menampilkan data info board', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    /**
     * Tampilkan form tambah info board.
     */
    public function create()
    {
        Log::info('Membuka form tambah info board', ['user_id' => Auth::id()]);
        return view('Contents.info_board.create');
    }

    /**
     * Simpan data baru info board.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        try {
            DB::table('info_board')->insert([
                'admin_id' => Auth::id(),
                'title' => $request->title,
                'content' => $request->content,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Berhasil menambah info board', [
                'user_id' => Auth::id(),
                'title' => $request->title
            ]);

            return redirect()->route('admin.info_board.index')
                ->with('swal_success_crud', 'Informasi berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Gagal menambah info board', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menambahkan informasi.');
        }
    }

    /**
     * Tampilkan form edit info board tertentu.
     */
    public function edit($id)
    {
        try {
            $info = DB::table('info_board')->where('info_id', $id)->first();

            if (!$info) {
                Log::warning('Info board tidak ditemukan untuk diedit', ['info_id' => $id]);
                return redirect()->route('admin.info_board.index')->with('error', 'Informasi tidak ditemukan.');
            }

            Log::info('Membuka form edit info board', ['user_id' => Auth::id(), 'info_id' => $id]);

            return view('Contents.info_board.edit', compact('info'));
        } catch (Exception $e) {
            Log::error('Gagal membuka form edit info board', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Update info board.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        try {
            $updated = DB::table('info_board')
                ->where('info_id', $id)
                ->update([
                    'title' => $request->title,
                    'content' => $request->content,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                Log::info('Info board berhasil diperbarui', [
                    'user_id' => Auth::id(),
                    'info_id' => $id,
                ]);
                return redirect()->route('admin.info_board.index')
                    ->with('swal_success_crud', 'Informasi berhasil diperbarui.');
            } else {
                Log::warning('Gagal memperbarui info board - data tidak ditemukan', ['info_id' => $id]);
                return back()->with('error', 'Data tidak ditemukan.');
            }
        } catch (Exception $e) {
            Log::error('Gagal memperbarui info board', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }
    }

    /**
     * Hapus info board.
     */
    public function destroy($id)
    {
        try {
            $deleted = DB::table('info_board')->where('info_id', $id)->delete();

            if ($deleted) {
                Log::info('Info board berhasil dihapus', [
                    'user_id' => Auth::id(),
                    'info_id' => $id
                ]);
                return redirect()->route('admin.info_board.index')
                    ->with('swal_success_crud', 'Informasi berhasil dihapus.');
            } else {
                Log::warning('Gagal menghapus info board - data tidak ditemukan', ['info_id' => $id]);
                return back()->with('error', 'Data tidak ditemukan.');
            }
        } catch (Exception $e) {
            Log::error('Gagal menghapus info board', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}
