<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class FeedbackController extends Controller
{
    /**
     * Tampilkan semua feedback.
     */
    public function index()
    {
        try {
            $feedbacks = DB::table('feedback')
                ->orderByDesc(DB::raw('COALESCE(feedback.updated_at, feedback.created_at)'))
                ->paginate(10);

            Log::info('Menampilkan daftar feedback', ['user_id' => Auth::id()]);

            return view('Contents.feedback.index', compact('feedbacks'));
        } catch (Exception $e) {
            Log::error('Gagal menampilkan data feedback', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }
    /**
     * Hapus feedback.
     */
    public function destroy($id)
    {
        try {
            $feedback = DB::table('feedback')->where('feedback_id', $id)->first();

            if (!$feedback) {
                Log::warning('Feedback tidak ditemukan untuk dihapus', ['feedback_id' => $id]);
                return back()->with('error', 'Data tidak ditemukan.');
            }

            $deleted = DB::table('feedback')->where('feedback_id', $id)->delete();

            if ($deleted) {
                Log::info('Feedback berhasil dihapus', [
                    'user_id'     => Auth::id(),
                    'feedback_id' => $id
                ]);

                return redirect()->route('admin.feedback.index')
                    ->with('swal_success_crud', 'Feedback berhasil dihapus.');
            } else {
                Log::warning('Gagal menghapus feedback', ['feedback_id' => $id]);
                return back()->with('error', 'Gagal menghapus data.');
            }
        } catch (Exception $e) {
            Log::error('Gagal menghapus feedback', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}
