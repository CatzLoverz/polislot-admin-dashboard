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
            $data = Mission::select('*')->orderBy('created_at', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('mission_points', fn($row) => number_format($row->mission_points) . ' Poin')
                ->editColumn('mission_type', function($row){
                    $color = $row->mission_type === 'TARGET' ? 'info' : 'warning';
                    return "<span class='badge badge-{$color}'>{$row->mission_type}</span>";
                })
                ->addColumn('cycle_info', function($row){
                    return Mission::CYCLES[$row->mission_reset_cycle] ?? $row->mission_reset_cycle;
                })
                ->addColumn('rules_detail', function($row){
                    if ($row->mission_type === 'TARGET') {
                        return "Target: <b>{$row->mission_threshold}</b> <br><small class='text-muted'>Metric: {$row->mission_metric_code}</small>";
                    } else {
                        $mode = $row->mission_is_consecutive ? '(Berurut)' : '(Acak)';
                        return "Durasi: <b>{$row->mission_threshold} Hari</b> {$mode} <br><small class='text-muted'>Metric: {$row->mission_metric_code}</small>";
                    }
                })
                ->editColumn('mission_is_active', function($row){
                    return $row->mission_is_active 
                        ? '<span class="badge badge-success">Aktif</span>' 
                        : '<span class="badge badge-danger">Non-Aktif</span>';
                })
                ->addColumn('action', function($row){
                    $title = e($row->mission_title);
                    $jsonData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    
                    $btnEdit = '<button class="btn btn-link btn-primary btn-lg btn-edit" 
                                    data-row="'.$jsonData.'" 
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
                ->rawColumns(['mission_type', 'mission_is_active', 'rules_detail', 'action'])
                ->make(true);
        }

        $metrics = Mission::METRICS;
        $cycles = Mission::CYCLES;
        return view('Contents.missions.index', compact('metrics', 'cycles'));
    }

    /**
     * Memproses penyimpanan data Misi baru.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'mission_title'       => 'required|string|max:255',
                    'mission_description' => 'nullable|string',
                    'mission_points'      => 'required|integer|min:0',
                    'mission_type'        => 'required|in:TARGET,SEQUENCE',
                    'mission_metric_code' => ['required', Rule::in(array_keys(Mission::METRICS))],
                    'mission_reset_cycle' => ['required', Rule::in(array_keys(Mission::CYCLES))],
                    'mission_threshold'   => 'required|integer|min:1',
                    'mission_is_consecutive' => 'nullable', 
                    'mission_is_active'   => 'nullable',
                ]);

                $validated['mission_is_active'] = $request->has('mission_is_active');
                $validated['mission_is_consecutive'] = $request->has('mission_is_consecutive');

                Mission::create($validated);

                return redirect()->back()->with('swal_success_crud', 'Misi berhasil ditambahkan.');
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error('[WEB MissionController@store] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menyimpan data.');
        }
    }

    /**
     * Memproses pembaruan data Misi.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $mission = Mission::findOrFail($id);

                $validated = $request->validate([
                    'mission_title'       => 'required|string|max:255',
                    'mission_description' => 'nullable|string',
                    'mission_points'      => 'required|integer|min:0',
                    'mission_type'        => 'required|in:TARGET,SEQUENCE',
                    'mission_metric_code' => ['required', Rule::in(array_keys(Mission::METRICS))],
                    'mission_reset_cycle' => ['required', Rule::in(array_keys(Mission::CYCLES))],
                    'mission_threshold'   => 'required|integer|min:1',
                    'mission_is_consecutive' => 'nullable',
                    'mission_is_active'   => 'nullable',
                ]);

                $validated['mission_is_active'] = $request->has('mission_is_active');
                $validated['mission_is_consecutive'] = $request->has('mission_is_consecutive');

                $mission->update($validated);

                return redirect()->back()->with('swal_success_crud', 'Misi berhasil diperbarui.');
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal.');
        } catch (Exception $e) {
            Log::error('[WEB MissionController@update] Gagal: ' . $e->getMessage());
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

                Log::info('[WEB MissionController@destroy] Sukses: Mission dihapus.', ['id' => $id, 'title' => $title]);

                return redirect()->route('admin.missions.index')->with('swal_success_crud', 'Misi berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error('[WEB MissionController@destroy] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus data.');
        }
    }
}