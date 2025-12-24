<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ValidationController extends Controller
{
    /**
     * Menampilkan halaman daftar pengaturan validasi.
     * * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // 1. Cek Request AJAX untuk DataTables
        if ($request->ajax()) {
            // Pastikan data default (ID 1) selalu ada
            if (Validation::count() == 0) {
                Validation::create(['validation_points' => 10]);
            }

            // Query Data
            $data = Validation::query();

            // Return DataTables JSON
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('updated_at', function($row){
                    return $row->updated_at ? $row->updated_at->format('d-M-Y H:i') : '-';
                })
                ->addColumn('geofencing', function($row){
                    $status = $row->validation_is_geofence_active 
                        ? '<span class="badge badge-success">Aktif</span>' 
                        : '<span class="badge badge-secondary">Nonaktif</span>';
                    return $status;
                })
                ->addColumn('action', function($row){
                    $points = $row->validation_points;
                    $isGeofence = $row->validation_is_geofence_active ? 1 : 0;
                    $updateUrl = route('admin.validation.update', $row->validation_id);

                    $btnEdit = '<button class="btn btn-link btn-primary btn-lg btn-edit" 
                                    data-id="'.$row->validation_id.'"
                                    data-points="'.$points.'"
                                    data-geofence="'.$isGeofence.'"
                                    data-update-url="'.$updateUrl.'"
                                    data-toggle="tooltip" 
                                    title="Edit Pengaturan"> 
                                    <i class="fa fa-edit"></i>
                                </button>';

                    return '<div class="form-button-action d-flex justify-content-center">'.$btnEdit.'</div>';
                })
                ->rawColumns(['geofencing', 'action'])
                ->make(true);
        }

        // 2. Return View Utama (Jika bukan AJAX)
        return view('Contents.Validation.index');
    }

    /**
     * Memproses pembaruan data poin validasi.
     * * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $validated = $request->validate([
                    'validation_points' => 'required|integer|min:1',
                    'validation_is_geofence_active' => 'nullable|boolean'
                ]);

                $validation = Validation::findOrFail($id);
                
                // Handle checkbox (jika tidak dicentang, value null/absent, kita anggap false)
                $isGeofence = $request->has('validation_is_geofence_active');

                $validation->update([
                    'validation_points' => $validated['validation_points'],
                    'validation_is_geofence_active' => $isGeofence,
                ]);

                Log::info('[WEB ValidationController@update] Sukses: Pengaturan validasi diperbarui.');

                return redirect()->route('admin.validation.index')
                    ->with('swal_success_crud', 'Pengaturan validasi berhasil diperbarui.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal.');
        } catch (Exception $e) {
            Log::error('[WEB ValidationController@update] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal memperbarui data.');
        }
    }
}