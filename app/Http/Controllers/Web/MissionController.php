<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MissionController extends Controller
{
    /**
     * Menampilkan halaman daftar semua misi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Mission::select('*')->orderBy('created_at', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('mission_points', fn ($row) => number_format($row->mission_points).' Poin')
                ->editColumn('mission_type', function ($row) {
                    $map = [
                        'TARGET'          => ['color' => 'info',    'label' => 'TARGET'],
                        'SEQUENCE'        => ['color' => 'warning',  'label' => 'SEQUENCE'],
                        'SEQUENCE_STREAK' => ['color' => 'danger',   'label' => 'STREAK'],
                    ];
                    $badge = $map[$row->mission_type] ?? ['color' => 'secondary', 'label' => $row->mission_type];

                    return "<span class='badge badge-{$badge['color']}'>{$badge['label']}</span>";
                })
                ->addColumn('cycle_info', function ($row) {
                    return Mission::CYCLES[$row->mission_reset_cycle] ?? $row->mission_reset_cycle;
                })
                ->addColumn('rules_detail', function ($row) {
                    $metric = Mission::METRICS[$row->mission_metric_code] ?? $row->mission_metric_code;
                    if ($row->mission_type === 'TARGET') {
                        return "Target: <b>{$row->mission_threshold}x</b> <br><small class='text-muted'>{$metric}</small>";
                    } elseif ($row->mission_type === 'SEQUENCE') {
                        return "Durasi: <b>{$row->mission_threshold} Hari</b> <span class='badge badge-secondary badge-sm'>Non-Streak</span><br><small class='text-muted'>{$metric}</small>";
                    } else {
                        return "Durasi: <b>{$row->mission_threshold} Hari</b> <span class='badge badge-danger badge-sm'>Streak</span><br><small class='text-muted'>{$metric}</small>";
                    }
                })
                ->editColumn('mission_is_active', function ($row) {
                    return $row->mission_is_active
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Non-Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $title    = e($row->mission_title);
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
        $cycles  = Mission::CYCLES;

        return view('Contents.Missions.index', compact('metrics', 'cycles'));
    }

    /**
     * Melakukan validasi aturan logika silang (cross-field) berdasarkan metric.
     * Memastikan kombinasi mission_type, mission_reset_cycle, dan mission_threshold
     * sesuai dengan aturan yang diizinkan per event metric.
     *
     * @param  string  $metric
     * @param  string  $type
     * @param  string  $cycle
     * @param  int     $threshold
     * @return array   Error messages jika tidak valid, array kosong jika valid.
     */
    private function validateCrossFieldRules(string $metric, string $type, string $cycle, int $threshold): array
    {
        $errors = [];

        if ($metric === 'LOGIN_ACTION') {
            // Login hanya boleh SEQUENCE atau SEQUENCE_STREAK
            if ($type === 'TARGET') {
                $errors['mission_type'] = 'Event Login hanya mendukung tipe SEQUENCE atau SEQUENCE STREAK.';
            }
        }

        if ($metric === 'PROFILE_UPDATE') {
            // Profile Update hanya boleh TARGET, cycle NONE, threshold 1
            if ($type !== 'TARGET') {
                $errors['mission_type'] = 'Event Perbarui Profil hanya mendukung tipe TARGET.';
            }
            if ($cycle !== 'NONE') {
                $errors['mission_reset_cycle'] = 'Event Perbarui Profil hanya mendukung siklus Sekali Saja (NONE).';
            }
            if ($threshold !== 1) {
                $errors['mission_threshold'] = 'Event Perbarui Profil hanya mendukung target 1 kali.';
            }
        }

        return $errors;
    }

    /**
     * Memproses penyimpanan data Misi baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'mission_title'       => 'required|string|max:255|unique:missions,mission_title',
                    'mission_description' => 'nullable|string',
                    'mission_points'      => 'required|integer|min:0',
                    'mission_type'        => ['required', Rule::in(array_keys(Mission::TYPES))],
                    'mission_metric_code' => ['required', Rule::in(array_keys(Mission::METRICS))],
                    'mission_reset_cycle' => ['required', Rule::in(array_keys(Mission::CYCLES))],
                    'mission_threshold'   => 'required|integer|min:1',
                    'mission_is_active'   => 'nullable',
                ]);

                // Validasi logika silang (cross-field) per aturan metric
                $crossErrors = $this->validateCrossFieldRules(
                    $validated['mission_metric_code'],
                    $validated['mission_type'],
                    $validated['mission_reset_cycle'],
                    (int) $validated['mission_threshold']
                );
                if (! empty($crossErrors)) {
                    throw ValidationException::withMessages($crossErrors);
                }

                $validated['mission_is_active'] = $request->has('mission_is_active');

                Mission::create($validated);

                return redirect()->route('admin.missions.index')->with('swal_success_crud', 'Misi berhasil ditambahkan.');
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return back()->with('swal_error_crud', 'Gagal menyimpan data.');
        }
    }

    /**
     * Memproses pembaruan data Misi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $mission = Mission::findOrFail($id);

                $validated = $request->validate([
                    'mission_title'       => ['required', 'string', 'max:255', Rule::unique('missions', 'mission_title')->ignore($mission->mission_id, 'mission_id')],
                    'mission_description' => 'nullable|string',
                    'mission_points'      => 'required|integer|min:0',
                    'mission_type'        => ['required', Rule::in(array_keys(Mission::TYPES))],
                    'mission_metric_code' => ['required', Rule::in(array_keys(Mission::METRICS))],
                    'mission_reset_cycle' => ['required', Rule::in(array_keys(Mission::CYCLES))],
                    'mission_threshold'   => 'required|integer|min:1',
                    'mission_is_active'   => 'nullable',
                ]);

                // Validasi logika silang (cross-field) per aturan metric
                $crossErrors = $this->validateCrossFieldRules(
                    $validated['mission_metric_code'],
                    $validated['mission_type'],
                    $validated['mission_reset_cycle'],
                    (int) $validated['mission_threshold']
                );
                if (! empty($crossErrors)) {
                    throw ValidationException::withMessages($crossErrors);
                }

                $validated['mission_is_active'] = $request->has('mission_is_active');

                $mission->update($validated);

                return redirect()->route('admin.missions.index')->with('swal_success_crud', 'Misi berhasil diperbarui.');
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal.');
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return back()->with('swal_error_crud', 'Gagal memperbarui data.');
        }
    }

    /**
     * Memproses penghapusan data Misi.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $mission = Mission::findOrFail($id);
                $title   = $mission->mission_title;

                $mission->delete();

                Log::info('Mission dihapus.', ['id' => $id, 'title' => $title]);

                return redirect()->route('admin.missions.index')->with('swal_success_crud', 'Misi berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return back()->with('swal_error_crud', 'Gagal menghapus data.');
        }
    }
}

