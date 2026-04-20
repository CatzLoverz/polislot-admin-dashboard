<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use App\Models\ParkSubarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class IotDeviceController extends Controller
{
    /**
     * Menampilkan halaman daftar manajemen perangkat IoT.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = IotDevice::with('subarea.parkArea');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('subarea_name', function($row){
                    $areaName = $row->subarea->parkArea->park_area_name ?? '-';
                    $subareaName = $row->subarea->park_subarea_name ?? '-';
                    return $areaName . ' - ' . $subareaName;
                })
                ->addColumn('action', function($row){
                    // Tombol Edit
                    $btnEdit = '<button class="btn btn-link btn-primary btn-lg btn-edit" 
                                    data-id="'.$row->device_id.'"
                                    data-subarea="'.$row->park_subarea_id.'"
                                    data-url="'.e($row->device_url).'"
                                    data-mac="'.e($row->device_mac_address).'"
                                    data-update-url="'.route('admin.iot-devices.update', $row->device_id).'"
                                    data-toggle="tooltip" 
                                    title="Edit Perangkat"><i class="fa fa-edit"></i></button>';
                    
                    // Tombol Delete
                    $btnDelete = '<form action="'.route('admin.iot-devices.destroy', $row->device_id).'" 
                                        method="POST" 
                                        class="delete-form d-inline" 
                                        data-entity-name=" Perangkat ini">
                                        '.csrf_field().'
                                        '.method_field('DELETE').'
                                        <button type="submit" 
                                            class="btn btn-link btn-danger btn-lg" 
                                            data-toggle="tooltip" 
                                            title="Hapus Perangkat"><i class="fa fa-trash"></i></button>
                                  </form>';

                    return '<div class="form-button-action d-flex justify-content-center">'.$btnEdit.$btnDelete.'</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Ambil data subarea yang belum memiliki IoT Device terpasang, ATAU bisa juga diambil semua
        // Tergantung requirement, kita ambil semua untuk sekarang dan biarkan validasi mencegah duplikasi.
        // Atau lebih baik ambil yang belum punya device untuk modal form Add (Create)
        $availableSubareas = ParkSubarea::with('parkArea')
                            ->doesntHave('iotDevice')
                            ->get()
                            ->map(function ($subarea) {
                                $areaName = $subarea->parkArea ? $subarea->parkArea->park_area_name : 'Unknown Area';
                                return [
                                    'id' => $subarea->park_subarea_id,
                                    'text' => $areaName . ' - ' . $subarea->park_subarea_name
                                ];
                            });

        // Semua subarea untuk keperluan Edit
        $allSubareas = ParkSubarea::with('parkArea')
                            ->get()
                            ->map(function ($subarea) {
                                $areaName = $subarea->parkArea ? $subarea->parkArea->park_area_name : 'Unknown Area';
                                return [
                                    'id' => $subarea->park_subarea_id,
                                    'text' => $areaName . ' - ' . $subarea->park_subarea_name
                                ];
                            });

        return view('Contents.IotDevice.index', compact('availableSubareas', 'allSubareas'));
    }

    /**
     * Memproses penyimpanan data IoT device baru.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'park_subarea_id'     => 'required|exists:park_subareas,park_subarea_id|unique:iot_devices,park_subarea_id',
                    'device_url'          => 'nullable|string|max:255',
                    'device_mac_address'  => 'nullable|string|max:255',
                ]);

                IotDevice::create([
                    'park_subarea_id'    => $validated['park_subarea_id'],
                    'device_url'         => $validated['device_url'],
                    'device_mac_address' => $validated['device_mac_address'],
                ]);

                Log::info('[WEB IotDeviceController@store] Sukses: Data perangkat IoT baru berhasil disimpan.');
                return redirect()->route('admin.iot-devices.index')
                    ->with('swal_success_crud', 'Perangkat IoT berhasil ditambahkan ke subarea tersebut.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda. (Mungkin Subarea sudah memiliki perangkat)');
        } catch (Exception $e) {
            Log::error('[WEB IotDeviceController@store] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menambahkan data perangkat.')->withInput();
        }
    }

    /**
     * Memproses pembaruan data IoT device.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $validated = $request->validate([
                    'park_subarea_id'     => 'required|exists:park_subareas,park_subarea_id|unique:iot_devices,park_subarea_id,' . $id . ',device_id',
                    'device_url'          => 'nullable|string|max:255',
                    'device_mac_address'  => 'nullable|string|max:255',
                ]);

                $device = IotDevice::findOrFail($id);
                
                $device->update([
                    'park_subarea_id'    => $validated['park_subarea_id'],
                    'device_url'         => $validated['device_url'],
                    'device_mac_address' => $validated['device_mac_address'],
                ]);

                Log::info('[WEB IotDeviceController@update] Sukses: Data perangkat IoT berhasil diperbarui.');

                return redirect()->route('admin.iot-devices.index')
                    ->with('swal_success_crud', 'Data perangkat IoT berhasil diperbarui.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error('[WEB IotDeviceController@update] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal memperbarui data perangkat.');
        }
    }

    /**
     * Memproses penghapusan data IoT device.
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $device = IotDevice::findOrFail($id);
                $device->delete();

                Log::info('[WEB IotDeviceController@destroy] Sukses: Data perangkat IoT berhasil dihapus.');

                return redirect()->route('admin.iot-devices.index')
                    ->with('swal_success_crud', 'Data perangkat IoT berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error('[WEB IotDeviceController@destroy] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus data perangkat.');
        }
    }
}
