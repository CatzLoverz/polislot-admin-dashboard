<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class MissionController extends Controller
{
    /**
     * Menampilkan halaman daftar semua misi.
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Eager load relasi child (missionTarget & missionSequence) untuk efisiensi query
            $data = Mission::with(['missionTarget', 'missionSequence'])
                ->select('missions.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('mission_points', function($row){
                    return number_format($row->mission_points) . ' Poin';
                })
                ->editColumn('mission_type', function($row){
                    // Badge warna sesuai tipe
                    if ($row->mission_type === 'TARGET') {
                        return '<span class="badge badge-primary">Target (Akumulasi)</span>';
                    }
                    return '<span class="badge badge-secondary">Sequence (Trigger harian)</span>';
                })
                ->editColumn('mission_is_active', function($row){
                    return $row->mission_is_active 
                        ? '<span class="badge badge-success">Aktif</span>' 
                        : '<span class="badge badge-danger">Non-Aktif</span>';
                })
                ->addColumn('rule_detail', function($row){
                    // Menampilkan ringkasan aturan berdasarkan tipe misi
                    if ($row->mission_type === 'TARGET' && $row->missionTarget) {
                        return 'Target: ' . number_format($row->missionTarget->mission_target_amount) . ' (Total)';
                    } elseif ($row->mission_type === 'SEQUENCE' && $row->missionSequence) {
                        $status = $row->missionSequence->mission_is_consecutive ? 'Berurut' : 'Acak/Total';
                        return 'Durasi: ' . $row->missionSequence->mission_days_required . ' Hari <br><small class="text-muted">(' . $status . ')</small>';
                    }
                    return '-';
                })
                ->addColumn('action', function($row){
                    $title = e($row->mission_title);
                    
                    // Siapkan data attributes untuk Modal Edit (JS)
                    $targetAmount  = $row->missionTarget ? $row->missionTarget->mission_target_amount : '';
                    $daysRequired  = $row->missionSequence ? $row->missionSequence->mission_days_required : '';
                    // Ambil status consecutive (1 atau 0)
                    $isConsecutive = $row->missionSequence ? $row->missionSequence->mission_is_consecutive : '0'; 

                    $btnEdit = '<button class="btn btn-link btn-primary btn-lg btn-edit" 
                                    data-id="'.$row->mission_id.'"
                                    data-title="'.$title.'"
                                    data-description="'.e($row->mission_description).'"
                                    data-points="'.$row->mission_points.'"
                                    data-type="'.$row->mission_type.'"
                                    data-metric="'.$row->mission_metric_code.'"
                                    data-active="'.$row->mission_is_active.'"
                                    data-start="'.$row->mission_start_date?->format('Y-m-d\TH:i').'"
                                    data-end="'.$row->mission_end_date?->format('Y-m-d\TH:i').'"
                                    
                                    data-target-amount="'.$targetAmount.'"
                                    data-days-required="'.$daysRequired.'"
                                    data-is-consecutive="'.$isConsecutive.'"

                                    data-update-url="'.route('admin.missions.update', $row->mission_id).'"
                                    data-toggle="tooltip" 
                                    title="Edit '.$title.'"> 
                                    <i class="fa fa-edit"></i>
                                </button>';
                    
                    $btnDelete = '<form action="'.route('admin.missions.destroy', $row->mission_id).'" 
                                        method="POST" 
                                        class="delete-form d-inline" 
                                        data-entity-name=" '.$title.'">
                                        '.csrf_field().'
                                        '.method_field('DELETE').'
                                        <button type="submit" 
                                            class="btn btn-link btn-danger btn-lg" 
                                            data-toggle="tooltip" 
                                            title="Hapus '.$title.'">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                  </form>';

                    return '<div class="form-button-action d-flex justify-content-center">'.$btnEdit.$btnDelete.'</div>';
                })
                ->rawColumns(['mission_type', 'mission_is_active', 'rule_detail', 'action'])
                ->make(true);
        }

        // Ambil konstanta METRICS dari Model untuk mengisi dropdown di View
        $metrics = Mission::METRICS;
        $metricTypes = Mission::METRIC_TYPES;
        return view('Contents.missions.index', compact('metrics', 'metricTypes'));
    }

    /**
     * Memproses penyimpanan data Misi baru beserta aturan detailnya.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // 1. Validasi Input
                $validated = $request->validate([
                    'mission_title'       => 'required|string|max:255',
                    'mission_description' => 'nullable|string',
                    'mission_points'      => 'required|integer|min:0',
                    'mission_type'        => 'required|in:TARGET,SEQUENCE',
                    // Validasi Metric Code harus ada di daftar CONST Model
                    'mission_metric_code' => ['required', Rule::in(array_keys(Mission::METRICS))],
                    'mission_is_active'   => 'nullable|boolean',
                    'mission_start_date'  => 'nullable|date',
                    'mission_end_date'    => 'nullable|date|after_or_equal:mission_start_date',
                    
                    // Validasi Kondisional (Hanya wajib jika tipe sesuai)
                    'mission_target_amount'  => 'required_if:mission_type,TARGET|nullable|integer|min:1',
                    'mission_days_required'  => 'required_if:mission_type,SEQUENCE|nullable|integer|min:1',
                    'mission_is_consecutive' => 'nullable', // Checkbox mengirim value jika dicentang
                ]);

                // 2. Simpan Parent (Missions)
                $mission = Mission::create([
                    'mission_title'       => $validated['mission_title'],
                    'mission_description' => $validated['mission_description'],
                    'mission_points'      => $validated['mission_points'],
                    'mission_type'        => $validated['mission_type'],
                    'mission_metric_code' => $validated['mission_metric_code'], // Disimpan di Parent
                    'mission_is_active'   => $request->has('mission_is_active') ? 1 : 0,
                    'mission_start_date'  => $validated['mission_start_date'],
                    'mission_end_date'    => $validated['mission_end_date'],
                ]);

                // 3. Simpan Child sesuai Tipe
                if ($mission->mission_type === 'TARGET') {
                    $mission->missionTarget()->create([
                        'mission_target_amount' => $request->mission_target_amount,
                    ]);
                } elseif ($mission->mission_type === 'SEQUENCE') {
                    $mission->missionSequence()->create([
                        'mission_days_required'  => $request->mission_days_required,
                        // Cek checkbox (1 jika dicentang, 0 jika tidak)
                        'mission_is_consecutive' => $request->has('mission_is_consecutive') ? 1 : 0,
                        'mission_reset_time'     => '00:00:00', // Default reset tengah malam
                    ]);
                }

                Log::info('[MissionController@store] Sukses: Mission baru dibuat.', [
                    'id' => $mission->mission_id,
                    'title' => $mission->mission_title
                ]);

                return redirect()->route('admin.missions.index')
                    ->with('swal_success_crud', 'Misi berhasil ditambahkan.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error('[MissionController@store] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Terjadi kesalahan sistem saat menyimpan data.')->withInput();
        }
    }

    /**
     * Memproses pembaruan data Misi.
     * Mengupdate Parent dan Child yang terkait.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                // 1. Validasi Input (Serupa dengan store)
                $validated = $request->validate([
                    'mission_title'       => 'required|string|max:255',
                    'mission_description' => 'nullable|string',
                    'mission_points'      => 'required|integer|min:0',
                    'mission_metric_code' => ['required', Rule::in(array_keys(Mission::METRICS))],
                    'mission_is_active'   => 'nullable|boolean',
                    'mission_start_date'  => 'nullable|date',
                    'mission_end_date'    => 'nullable|date|after_or_equal:mission_start_date',
                    
                    'mission_target_amount'  => 'nullable|integer|min:1',
                    'mission_days_required'  => 'nullable|integer|min:1',
                    'mission_is_consecutive' => 'nullable',
                ]);

                // Eager load child agar bisa diupdate
                $mission = Mission::with(['missionTarget', 'missionSequence'])->findOrFail($id);

                // 2. Update Parent
                $mission->update([
                    'mission_title'       => $validated['mission_title'],
                    'mission_description' => $validated['mission_description'],
                    'mission_points'      => $validated['mission_points'],
                    'mission_metric_code' => $validated['mission_metric_code'],
                    'mission_is_active'   => $request->has('mission_is_active') ? 1 : 0,
                    'mission_start_date'  => $validated['mission_start_date'],
                    'mission_end_date'    => $validated['mission_end_date'],
                ]);

                // 3. Update Child (Tergantung tipe misi awal)
                if ($mission->mission_type === 'TARGET' && $mission->missionTarget) {
                    $mission->missionTarget->update([
                        'mission_target_amount' => $request->mission_target_amount,
                    ]);
                } elseif ($mission->mission_type === 'SEQUENCE' && $mission->missionSequence) {
                    $mission->missionSequence->update([
                        'mission_days_required'  => $request->mission_days_required,
                        'mission_is_consecutive' => $request->has('mission_is_consecutive') ? 1 : 0,
                    ]);
                }

                Log::info('[MissionController@update] Sukses: Mission diperbarui.', ['id' => $id]);

                return redirect()->route('admin.missions.index')
                    ->with('swal_success_crud', 'Misi berhasil diperbarui.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal.');
        } catch (Exception $e) {
            Log::error('[MissionController@update] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal memperbarui data.');
        }
    }

    /**
     * Memproses penghapusan data Misi.
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $mission = Mission::findOrFail($id);
                $title = $mission->mission_title;
                
                $mission->delete();

                Log::info('[MissionController@destroy] Sukses: Mission dihapus.', [
                    'id' => $id, 
                    'title' => $title
                ]);

                return redirect()->route('admin.missions.index')
                    ->with('swal_success_crud', 'Misi berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error('[MissionController@destroy] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus data.');
        }
    }
}