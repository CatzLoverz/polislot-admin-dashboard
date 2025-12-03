<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InfoBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class InfoBoardController extends Controller
{
    /**
     * Menampilkan halaman daftar semua info board.
     * * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = InfoBoard::with('user')->orderBy('created_at', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('creator_name', function($row){
                    return $row->user->name ?? 'Unknown';
                })
                ->editColumn('created_at', function($row){
                    return $row->created_at ? $row->created_at->format('d-M-Y H:i') : '-';
                })
                ->editColumn('updated_at', function($row){
                    return $row->updated_at ? $row->updated_at->format('d-M-Y H:i') : '-';
                })
                ->addColumn('action', function($row){
                    $infoTitle = e($row->info_title);
                    // Tombol Edit
                    $btnEdit = '<button class="btn btn-link btn-primary btn-lg btn-edit" 
                                    data-id="'.$row->info_id.'"
                                    data-title="'.$infoTitle.'"
                                    data-content="'.e($row->info_content).'"
                                    data-update-url="'.route('admin.info-board.update', $row->info_id).'"
                                    data-toggle="tooltip" 
                                    title="Edit '.$infoTitle.'"> 
                                    <i class="fa fa-edit"></i>
                                </button>';
                    
                    // Tombol Delete
                    $btnDelete = '<form action="'.route('admin.info-board.destroy', $row->info_id).'" 
                                        method="POST" 
                                        class="delete-form d-inline" 
                                        data-entity-name=" '.$infoTitle.'">
                                        '.csrf_field().'
                                        '.method_field('DELETE').'
                                        <button type="submit" 
                                            class="btn btn-link btn-danger btn-lg" 
                                            data-toggle="tooltip" 
                                            title="Hapus '.$infoTitle.'">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                  </form>';

                    return '<div class="form-button-action d-flex justify-content-center">'.$btnEdit.$btnDelete.'</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Contents.InfoBoard.index');
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
                    'info_content' => 'required|string',
                ]);

                InfoBoard::create([
                    'user_id'      => Auth::id(),
                    'info_title'   => $validated['info_title'],
                    'info_content' => $validated['info_content'],
                ]);

                Log::info('[WEB InfoBoardController@store] Sukses: Data info board baru berhasil disimpan.');
                return redirect()->route('admin.info-board.index')
                    ->with('swal_success_crud', 'Informasi berhasil ditambahkan.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error('[WEB InfoBoardController@store] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menambahkan informasi.')->withInput();
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
                $validated = $request->validate([
                    'info_title'   => 'required|string|max:255',
                    'info_content' => 'required|string',
                ]);

                $infoBoard = InfoBoard::findOrFail($id);
                
                $infoBoard->update([
                    'info_title'   => $validated['info_title'],
                    'info_content' => $validated['info_content'],
                ]);

                Log::info('[WEB InfoBoardController@update] Sukses: Data info board berhasil diperbarui.');

                return redirect()->route('admin.info-board.index')
                    ->with('swal_success_crud', 'Informasi berhasil diperbarui.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error('[WEB InfoBoardController@update] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal memperbarui data.');
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
            Log::error('[WEB InfoBoardController@destroy] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus data.');
        }
    }
}