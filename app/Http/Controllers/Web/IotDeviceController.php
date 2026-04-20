<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
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
            $data = IotDevice::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $deviceName = e($row->device_name);
                    
                    // Tombol Terminal
                    $btnTerminal = '<a href="'.route('admin.iot-devices.terminal', $row->device_id).'" 
                                        class="btn btn-link btn-info btn-lg" 
                                        data-toggle="tooltip" 
                                        title="Buka Terminal '.$deviceName.'">
                                        <i class="fas fa-terminal"></i> Buka Terminal
                                    </a>';

                    // Tombol Edit
                    $btnEdit = '<button class="btn btn-link btn-primary btn-lg btn-edit" 
                                    data-id="'.$row->device_id.'"
                                    data-name="'.$deviceName.'"
                                    data-url="'.e($row->ssh_url).'"
                                    data-update-url="'.route('admin.iot-devices.update', $row->device_id).'"
                                    data-toggle="tooltip" 
                                    title="Edit '.$deviceName.'"> 
                                    <i class="fa fa-edit"></i>
                                </button>';
                    
                    // Tombol Delete
                    $btnDelete = '<form action="'.route('admin.iot-devices.destroy', $row->device_id).'" 
                                        method="POST" 
                                        class="delete-form d-inline" 
                                        data-entity-name=" '.$deviceName.'">
                                        '.csrf_field().'
                                        '.method_field('DELETE').'
                                        <button type="submit" 
                                            class="btn btn-link btn-danger btn-lg" 
                                            data-toggle="tooltip" 
                                            title="Hapus '.$deviceName.'">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                  </form>';

                    return '<div class="form-button-action d-flex justify-content-center">'.$btnTerminal.$btnEdit.$btnDelete.'</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Contents.IotDevice.index');
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
                    'device_name' => 'required|string|max:255',
                    'ssh_url'     => 'required|url|max:255',
                ]);

                IotDevice::create([
                    'device_name' => $validated['device_name'],
                    'ssh_url'     => $validated['ssh_url'],
                ]);

                Log::info('[WEB IotDeviceController@store] Sukses: Data perangkat IoT baru berhasil disimpan.');
                return redirect()->route('admin.iot-devices.index')
                    ->with('swal_success_crud', 'Perangkat IoT berhasil ditambahkan.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
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
                    'device_name' => 'required|string|max:255',
                    'ssh_url'     => 'required|url|max:255',
                ]);

                $device = IotDevice::findOrFail($id);
                
                $device->update([
                    'device_name' => $validated['device_name'],
                    'ssh_url'     => $validated['ssh_url'],
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

    /**
     * Menampilkan halaman Terminal SSH
     */
    public function terminal($id)
    {
        $device = IotDevice::findOrFail($id);
        return view('Contents.IotDevice.terminal', compact('device'));
    }
}
