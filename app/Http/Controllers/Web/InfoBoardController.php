<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InfoBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class InfoBoardController extends Controller
{
    /**
     * Menampilkan halaman daftar semua info board.
     * * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        try {
            $infoBoards = InfoBoard::with('user')
                ->orderBy('updated_at', 'desc')
                ->paginate(3);

            Log::info('[WEB InfoBoardController@index] Sukses: Halaman daftar info board berhasil dimuat.');

            return view('Contents.InfoBoard.index', compact('infoBoards'));

        } catch (Exception $e) {
            Log::error('[WEB InfoBoardController@index] Gagal: Terjadi kesalahan sistem.', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    /**
     * Menampilkan form untuk menambahkan info board baru.
     * * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('Contents.InfoBoard.create');
    }

    /**
     * Memproses penyimpanan data info board baru.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'info_title'   => 'required|string|max:255',
                    'content' => 'required|string',
                ]);

                InfoBoard::create([
                    'user_id'      => Auth::id(),
                    'info_title'   => $validated['title'],
                    'info_content' => $validated['content'],
                ]);

                Log::info('[WEB InfoBoardController@store] Sukses: Data info board baru berhasil disimpan.');
                return redirect()->route('admin.info-board.index')
                    ->with('swal_success_crud', 'Informasi berhasil ditambahkan.');
            });

        } catch (ValidationException $e) {
            Log::warning('[WEB InfoBoardController@store] Gagal: Validasi input tidak terpenuhi.');
            return back()->withErrors($e->errors())->withInput();

        } catch (Exception $e) {
            Log::error('[WEB InfoBoardController@store] Gagal: Terjadi kesalahan sistem.', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menambahkan informasi.');
        }
    }

    /**
     * Menampilkan form untuk mengedit info board.
     * * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        try {
            $info = InfoBoard::findOrFail($id);
            return view('Contents.info_board.edit', compact('info'));

        } catch (Exception $e) {
            Log::error('[WEB InfoBoardController@edit] Gagal: Data tidak ditemukan atau error sistem.', ['error' => $e->getMessage()]);
            return redirect()->route('admin.info-board.index')->with('error', 'Informasi tidak ditemukan.');
        }
    }

    /**
     * Memproses pembaruan data info board.
     * * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                // Validasi Input
                $validated = $request->validate([
                    'title'   => 'required|string|max:255',
                    'content' => 'required|string',
                ]);

                $infoBoard = InfoBoard::findOrFail($id);
                
                $infoBoard->update([
                    'info_title'   => $validated['title'],
                    'info_content' => $validated['content'],
                ]);

                Log::info('[WEB InfoBoardController@update] Sukses: Data info board berhasil diperbarui.');

                return redirect()->route('admin.info-board.index')
                    ->with('swal_success_crud', 'Informasi berhasil diperbarui.');
            });

        } catch (ValidationException $e) {
            Log::warning('[WEB InfoBoardController@update] Gagal: Validasi input edit tidak terpenuhi.');
            return back()->withErrors($e->errors())->withInput();

        } catch (Exception $e) {
            Log::error('[WEB InfoBoardController@update] Gagal: Terjadi kesalahan sistem.', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }
    }

    /**
     * Memproses penghapusan data info board.
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $infoBoard = InfoBoard::findOrFail($id);
                $infoBoard->delete();

                Log::info('[WEB InfoBoardController@destroy] Sukses: Data info board berhasil dihapus.');

                return redirect()->route('admin.info-board.index')
                    ->with('swal_success_crud', 'Informasi berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error('[WEB InfoBoardController@destroy] Gagal: Terjadi kesalahan sistem.', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}