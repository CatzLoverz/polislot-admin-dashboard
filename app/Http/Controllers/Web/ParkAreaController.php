<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ParkArea;
use App\Models\IotDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ParkAreaController extends Controller
{
    /**
     * Menampilkan halaman daftar area parkir.
     * Menggunakan Yajra DataTables untuk memuat data.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $data = ParkArea::get();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        $title = e($row->park_area_name);
                        
                        // Tombol Detail/Kelola
                        $btnShow = '<a href="'.route('admin.park-area.show', $row->park_area_id).'" 
                                       class="btn btn-link btn-primary btn-lg" 
                                       data-toggle="tooltip" 
                                       title="Kelola Area & Peta">
                                       <i class="fa fa-map-marked-alt"></i>
                                    </a>';

                        // Tombol Hapus
                        $btnDelete = '<form action="'.route('admin.park-area.destroy', $row->park_area_id).'" 
                                            method="POST" 
                                            class="delete-form d-inline" 
                                            data-entity-name="Area: '.$title.'">
                                            '.csrf_field().'
                                            '.method_field('DELETE').'
                                            <button type="submit" 
                                                class="btn btn-link btn-danger btn-lg" 
                                                data-toggle="tooltip" 
                                                title="Hapus Area">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                      </form>';

                        return '<div class="form-button-action d-flex justify-content-center">'.$btnShow.$btnDelete.'</div>';
                    })
                    ->rawColumns(['action'])
                    ->make(true);

            } catch (Exception $e) {
                Log::error('Gagal memuat DataTables: ' . $e->getMessage());
                return response()->json(['error' => 'Gagal memuat data.'], 500);
            }
        }

        return view('Contents.ParkArea.index');
    }

    /**
     * Menampilkan halaman form pembuatan area parkir baru.
     *
     * @return \Illuminate\View\View
     */
    public function create(): \Illuminate\View\View
    {
        $mapsApiKey = config('services.google.js_api_key');
        return view('Contents.ParkArea.create', compact('mapsApiKey'));
    }

    /**
     * Menyimpan data area parkir baru ke database.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'park_area_name' => 'required|string|max:255',
                    'park_area_code' => 'required|string|max:50|unique:park_areas,park_area_code',
                    'center_lat'     => 'required|numeric',
                    'center_lng'     => 'required|numeric',
                    'zoom_level'     => 'required|integer',
                ]);

                $parkArea = ParkArea::create([
                    'park_area_name' => $validated['park_area_name'],
                    'park_area_code' => $validated['park_area_code'],
                    'park_area_data' => [
                        'lat'  => (float) $validated['center_lat'],
                        'lng'  => (float) $validated['center_lng'],
                        'zoom' => (int) $validated['zoom_level']
                    ]
                ]);

                Log::info('Area parkir baru dibuat.', [
                    'id' => $parkArea->park_area_id, 
                    'code' => $parkArea->park_area_code
                ]);

                return redirect()->route('admin.park-area.index')
                    ->with('swal_success_crud', 'Area Parkir berhasil dibuat.');
            });

        } catch (ValidationException $e) {
            Log::error('Terjadi kesalahan', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput()
                ->with('swal_error_crud', 'Validasi gagal, ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menyimpan data area.')->withInput();
        }
    }

    /**
     * Menampilkan detail area parkir dan peta interaktif untuk manajemen subarea.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        try {
            // Eager Load: Subarea -> Validasi (1 jam terakhir) & Komentar (terbaru + user info) & Amenities & IoT
            $area = ParkArea::with([
                'parkSubarea.parkAmenity',
                'parkSubarea.iotDevice',
                'parkSubarea.userValidation' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'parkSubarea.subareaComment.user' => function($query) {
                    $query->select('user_id', 'name', 'avatar');
                }
            ])->findOrFail($id);

            $mapsApiKey = config('services.google.js_api_key');

            // --- LOGIKA WARNA POLYGON ---
            // Kita inject attribute 'status_color', 'is_validated', dan 'has_user_report' ke setiap subarea object
            foreach ($area->parkSubarea as $sub) {
                $live = $sub->getLiveStatus();
                $sub->is_validated = $live['is_validated'];
                $sub->has_user_report = $live['has_user_report'];
                $sub->status_color = $live['status_color'];
                $sub->validation_expires_at = $live['validation_expires_at'];
                $sub->last_validation_time = $live['last_validation_time'];
                $sub->validation_remaining_seconds = $live['validation_remaining_seconds'];
                $sub->fallback_status = $live['fallback_status'];
                $sub->fallback_status_color = $live['fallback_status_color'];
                $sub->iot_status = $sub->iotDevice ? ($live['has_online_iot'] ? 'online' : 'offline') : null;
            }

            return view('Contents.ParkArea.show', compact('area', 'mapsApiKey'));

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('admin.park-area.index')
                ->with('swal_error_crud', 'Terjadi kesalahan memuat data.');
        }
    }

    /**
     * Menghapus area parkir beserta subareanya (Cascade Delete di DB).
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id): \Illuminate\Http\RedirectResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $area = ParkArea::findOrFail($id);
                $name = $area->park_area_name;
                
                $area->delete();

                Log::info('Area parkir dihapus.', ['id' => $id, 'name' => $name]);

                return redirect()->route('admin.park-area.index')
                    ->with('swal_success_crud', 'Area Parkir berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus data.');
        }
    }
}