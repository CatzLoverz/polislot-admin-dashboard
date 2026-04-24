<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserFaq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class UserFaqController extends Controller
{
    /**
     * Menampilkan halaman daftar semua FAQ.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = UserFaq::with('user');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('creator_name', function ($row) {
                    return $row->user->name ?? 'Unknown';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-M-Y H:i') : '-';
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('d-M-Y H:i') : '-';
                })
                ->addColumn('action', function ($row) {
                    $faqQuestion = e($row->faq_question);

                    // Tombol Edit
                    $btnEdit = '<button class="btn btn-link btn-primary btn-lg btn-edit"
                                    data-id="' . $row->faq_id . '"
                                    data-question="' . $faqQuestion . '"
                                    data-answer="' . e($row->faq_answer) . '"
                                    data-update-url="' . route('admin.user-faq.update', $row->faq_id) . '"
                                    data-toggle="tooltip"
                                    title="Edit ' . $faqQuestion . '">
                                    <i class="fa fa-edit"></i>
                                </button>';

                    // Tombol Delete
                    $btnDelete = '<form action="' . route('admin.user-faq.destroy', $row->faq_id) . '"
                                        method="POST"
                                        class="delete-form d-inline"
                                        data-entity-name=" ' . $faqQuestion . '">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '
                                        <button type="submit"
                                            class="btn btn-link btn-danger btn-lg"
                                            data-toggle="tooltip"
                                            title="Hapus ' . $faqQuestion . '">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                  </form>';

                    return '<div class="form-button-action d-flex justify-content-center">' . $btnEdit . $btnDelete . '</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Contents.UserFaq.index');
    }

    /**
     * Memproses penyimpanan data FAQ baru.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'faq_question' => 'required|string|max:255',
                    'faq_answer'   => 'required|string',
                ]);

                UserFaq::create([
                    'user_id'      => Auth::id(),
                    'faq_question' => $validated['faq_question'],
                    'faq_answer'   => $validated['faq_answer'],
                ]);

                Log::info('[WEB UserFaqController@store] Sukses: Data FAQ baru berhasil disimpan.');

                return redirect()->route('admin.user-faq.index')
                    ->with('swal_success_crud', 'FAQ berhasil ditambahkan.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error('[WEB UserFaqController@store] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menambahkan FAQ.')->withInput();
        }
    }

    /**
     * Memproses pembaruan data FAQ.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $validated = $request->validate([
                    'faq_question' => 'required|string|max:255',
                    'faq_answer'   => 'required|string',
                ]);

                $faq = UserFaq::findOrFail($id);

                $faq->update([
                    'faq_question' => $validated['faq_question'],
                    'faq_answer'   => $validated['faq_answer'],
                ]);

                Log::info('[WEB UserFaqController@update] Sukses: Data FAQ berhasil diperbarui.');

                return redirect()->route('admin.user-faq.index')
                    ->with('swal_success_crud', 'FAQ berhasil diperbarui.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error('[WEB UserFaqController@update] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal memperbarui FAQ.');
        }
    }

    /**
     * Memproses penghapusan data FAQ.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $faq = UserFaq::findOrFail($id);
                $faq->delete();

                Log::info('[WEB UserFaqController@destroy] Sukses: Data FAQ berhasil dihapus.');

                return redirect()->route('admin.user-faq.index')
                    ->with('swal_success_crud', 'FAQ berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error('[WEB UserFaqController@destroy] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus FAQ.');
        }
    }
}