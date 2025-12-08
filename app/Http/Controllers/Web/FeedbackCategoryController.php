<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FeedbackCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class FeedbackCategoryController extends Controller
{
    /**
     * Menampilkan halaman daftar kategori feedback.
     * * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = FeedbackCategory::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', function($row){
                    return $row->created_at ? $row->created_at->format('d M Y, H:i') : '-';
                })
                ->addColumn('action', function($row){
                    $name = e($row->fbk_category_name);

                    // Tombol Edit (Trigger Modal)
                    $btnEdit = '<button class="btn btn-link btn-primary btn-lg btn-edit" 
                                    data-id="'.$row->fbk_category_id.'"
                                    data-name="'.$name.'"
                                    data-update-url="'.route('admin.feedback-category.update', $row->fbk_category_id).'"
                                    data-toggle="tooltip" 
                                    title="Edit '.$name.'">
                                    <i class="fa fa-edit"></i>
                                </button>';
                    
                    // Tombol Hapus (SweetAlert)
                    $btnDelete = '<form action="'.route('admin.feedback-category.destroy', $row->fbk_category_id).'" 
                                        method="POST" 
                                        class="delete-form d-inline" 
                                        data-entity-name="Kategori: '.$name.'">
                                        '.csrf_field().'
                                        '.method_field('DELETE').'
                                        <button type="submit" 
                                            class="btn btn-link btn-danger btn-lg" 
                                            data-toggle="tooltip" 
                                            title="Hapus '.$name.'">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                  </form>';

                    return '<div class="form-button-action d-flex justify-content-center">'.$btnEdit.$btnDelete.'</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Contents.FeedbackCategory.index');
    }

    /**
     * Memproses penyimpanan kategori baru.
     * * @param \Illuminate\Http\Request $request
     * * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'fbk_category_name' => 'required|string|max:255|unique:feedback_categories,fbk_category_name',
                ]);

                FeedbackCategory::create([
                    'fbk_category_name' => $validated['fbk_category_name'],
                ]);

                Log::info('[WEB FeedbackCategoryController@store] Sukses: Kategori baru berhasil disimpan.');
                
                return redirect()->route('admin.feedback-category.index')
                    ->with('swal_success_crud', 'Kategori berhasil ditambahkan.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()
                ->with('swal_error_crud', 'Validasi gagal, pastikan nama kategori belum ada.');
        } catch (Exception $e) {
            Log::error('[WEB FeedbackCategoryController@store] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menambahkan kategori.')->withInput();
        }
    }

    /**
     * Memperbarui kategori.
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $validated = $request->validate([
                    'fbk_category_name' => 'required|string|max:255|unique:feedback_categories,fbk_category_name,'.$id.',fbk_category_id',
                ]);

                $category = FeedbackCategory::findOrFail($id);
                $category->update([
                    'fbk_category_name' => $validated['fbk_category_name'],
                ]);

                Log::info('[WEB FeedbackCategoryController@update] Sukses: Kategori berhasil diperbarui.');

                return redirect()->route('admin.feedback-category.index')
                    ->with('swal_success_crud', 'Kategori berhasil diperbarui.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()
                ->with('swal_error_crud', 'Validasi gagal, nama kategori mungkin sudah ada.');
        } catch (Exception $e) {
            Log::error('[WEB FeedbackCategoryController@update] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal memperbarui data.');
        }
    }

    /**
     * Menghapus kategori.
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $category = FeedbackCategory::findOrFail($id);
                
                $category->delete();

                Log::info('[WEB FeedbackCategoryController@destroy] Sukses: Kategori berhasil dihapus.');

                return redirect()->route('admin.feedback-category.index')
                    ->with('swal_success_crud', 'Kategori berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error('[WEB FeedbackCategoryController@destroy] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus data.');
        }
    }
}