<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackCategory; // Tambahkan Model Kategori
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class FeedbackController extends Controller
{
    /**
     * Menampilkan halaman daftar feedback.
     * * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // 1. Query Dasar
            $query = Feedback::with('feedbackCategory')
                ->select('feedbacks.*')
                ->orderBy('created_at', 'desc');

            // 2. Logika Filter (Jika ada input dari dropdown)
            if ($request->has('category_filter') && !empty($request->category_filter)) {
                $query->where('fbk_category_id', $request->category_filter);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('category_name', function($row){
                    return $row->feedbackCategory->fbk_category_name ?? '-';
                })
                ->editColumn('created_at', function($row){
                    return $row->created_at ? $row->created_at->format('d M Y, H:i') : '-';
                })
                ->addColumn('action', function($row){
                    $title = e($row->feedback_title);
                    
                    // Tombol Hapus
                    $btnDelete = '<form action="'.route('admin.feedback.destroy', $row->feedback_id).'" 
                                        method="POST" 
                                        class="delete-form d-inline" 
                                        data-entity-name="'.$title.'">
                                        '.csrf_field().'
                                        '.method_field('DELETE').'
                                        <button type="submit" 
                                            class="btn btn-link btn-danger btn-lg" 
                                            data-toggle="tooltip" 
                                            title="Hapus '.$title.'">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                  </form>';

                    return '<div class="form-button-action d-flex justify-content-center">'.$btnDelete.'</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // 3. Ambil Data Kategori untuk Dropdown
        $categories = FeedbackCategory::orderBy('fbk_category_name')->get();

        return view('Contents.feedback.index', compact('categories'));
    }

    /**
     * Memproses penghapusan data feedback.
     * * @param int $id
     * * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $feedback = Feedback::findOrFail($id);
                $feedback->delete();

                Log::info('[WEB FeedbackController@destroy] Sukses: Feedback berhasil dihapus.', ['id' => $id]);

                return redirect()->route('admin.feedback.index')
                    ->with('swal_success_crud', 'Feedback berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error('[WEB FeedbackController@destroy] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus data.');
        }
    }
}