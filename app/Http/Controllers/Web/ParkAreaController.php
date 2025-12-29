<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ParkArea;
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
                Log::error('[WEB ParkAreaController@index] Gagal memuat DataTables: ' . $e->getMessage());
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
    public function create()
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
    public function store(Request $request)
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

                Log::info('[WEB ParkAreaController@store] Sukses: Area parkir baru dibuat.', [
                    'id' => $parkArea->park_area_id, 
                    'code' => $parkArea->park_area_code
                ]);

                return redirect()->route('admin.park-area.index')
                    ->with('swal_success_crud', 'Area Parkir berhasil dibuat.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()
                ->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error('[WEB ParkAreaController@store] Gagal: ' . $e->getMessage());
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
            // Eager Load: Subarea -> Validasi (1 jam terakhir) & Komentar (terbaru + user info) & Amenities
            $area = ParkArea::with([
                'parkSubarea.parkAmenity',
                'parkSubarea.userValidation' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'parkSubarea.subareaComment.user' => function($query) {
                    $query->select('user_id', 'name', 'avatar');
                }
            ])->findOrFail($id);

            $mapsApiKey = config('services.google.js_api_key');

            // --- LOGIKA WARNA POLYGON ---
            // Kita inject attribute 'status_color' ke setiap subarea object
            foreach ($area->parkSubarea as $sub) {
                $allValidations = $sub->userValidation; // Collection validasi 1 jam terakhir
                
                if ($allValidations->isEmpty()) {
                    $sub->status_color = '#1572e8'; // Default Blue (Netral/Belum ada info)
                } else {
                    // 1. Ambil Validasi Paling Baru sebagai "Anchor"
                    $latestValidation = $allValidations->first(); // Karena sudah di-orderby desc
                    $anchorTime = $latestValidation->created_at;

                    // 2. Tentukan Batas Waktu (1 Jam sebelum Validasi Terakhir)
                    $cutoffTime = $anchorTime->copy()->subHour();

                    // 3. Filter Validasi yang masuk dalam rentang [Cutoff -> Anchor]
                    //    Kita filter dari collection yang sudah di-load (PHP side filtering)
                    $validVotes = $allValidations->filter(function ($val) use ($cutoffTime) {
                        return $val->created_at >= $cutoffTime;
                    });

                    // 1. Hitung Vote per Status
                    $counts = $validVotes->countBy('user_validation_content');
                    
                    // 2. Cari Nilai Vote Tertinggi (Mayoritas)
                    $maxVote = $counts->max();

                    // 3. Cari Status apa saja yang punya nilai Max tersebut
                    $candidates = $counts->keys()->filter(function($key) use ($counts, $maxVote) {
                        return $counts[$key] === $maxVote;
                    });

                    $status = 'banyak'; // Default jika logic fall-through

                    // 4. Penentuan Pemenang
                    if ($candidates->count() === 1) {
                        $status = $candidates->first();
                    } else {
                        // Jika SERI (Tie), ambil status dari vote TERBARU di antara kandidat yang seri
                        $latestDecider = $validVotes->first(function ($vote) use ($candidates) {
                            return $candidates->contains($vote->user_validation_content);
                        });
                        
                        if ($latestDecider) {
                            $status = $latestDecider->user_validation_content;
                        }
                    }

                    // 5. Mapping Status ke Warna
                    if ($status === 'penuh') {
                        $sub->status_color = '#f25961'; // Merah (Penuh)
                    } elseif ($status === 'terbatas') {
                        $sub->status_color = '#ffad46'; // Kuning (Terbatas)
                    } else {
                        $sub->status_color = '#31ce36'; // Hijau (Banyak Kosong)
                    }
                }
            }

            return view('Contents.ParkArea.show', compact('area', 'mapsApiKey'));

        } catch (Exception $e) {
            Log::error('[WEB ParkAreaController@show] Error: ' . $e->getMessage());
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
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $area = ParkArea::findOrFail($id);
                $name = $area->park_area_name;
                
                $area->delete();

                Log::info('[WEB ParkAreaController@destroy] Sukses: Area parkir dihapus.', ['id' => $id, 'name' => $name]);

                return redirect()->route('admin.park-area.index')
                    ->with('swal_success_crud', 'Area Parkir berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error('[WEB ParkAreaController@destroy] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus data.');
        }
    }
}