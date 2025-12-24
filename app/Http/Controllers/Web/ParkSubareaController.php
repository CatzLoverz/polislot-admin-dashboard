<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class ParkSubareaController extends Controller
{
    /**
     * Menyimpan subarea (Polygon) baru ke database.
     * Biasanya dipanggil via AJAX dari Map Editor.
     *
     * @param Request $request
     * @param int $areaId ID dari ParkArea induk
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $park_area)
    {
        try {
            return DB::transaction(function () use ($request, $park_area) {
                // Validasi Input
                $request->validate([
                    'name'    => 'required|string|max:255',
                    'polygon' => 'required|json' // Harus string JSON valid koordinat
                ]);

                // Pastikan Area Induk valid
                $area = ParkArea::findOrFail($park_area);

                // Buat Subarea Baru
                $subarea = ParkSubarea::create([
                    'park_area_id'         => $area->park_area_id,
                    'park_subarea_name'    => $request->name,
                    'park_subarea_polygon' => json_decode($request->polygon),
                ]);

                Log::info('[WEB ParkSubareaController@store] Sukses: Subarea baru ditambahkan.', [
                    'area_id'    => $park_area,
                    'subarea_id' => $subarea->park_subarea_id,
                    'name'       => $subarea->park_subarea_name
                ]);

                return response()->json([
                    'status'  => 'success', 
                    'message' => 'Subarea berhasil disimpan.',
                    'data'    => $subarea
                ]);
            });

        } catch (ValidationException $e) {
            Log::warning('[WEB ParkSubareaController@store] Gagal: Validasi error.', ['errors' => $e->errors()]);
            return response()->json(['status' => 'error', 'message' => 'Validasi gagal.'], 422);
        } catch (Exception $e) {
            Log::error('[WEB ParkSubareaController@store] Gagal: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan server.'], 500);
        }
    }

    /**
     * Memperbarui data subarea (Nama atau Polygon).
     *
     * @param Request $request
     * @param int $id ID ParkSubarea
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $subarea = ParkSubarea::findOrFail($id);

                $request->validate([
                    'name'      => 'required|string|max:255',
                    'polygon'   => 'nullable|json',
                    'amenities' => 'nullable|array',
                    'amenities.*'=> 'string|max:255'
                ]);

                $dataToUpdate = [
                    'park_subarea_name' => $request->name,
                ];

                // Update polygon hanya jika dikirim
                if ($request->filled('polygon')) {
                    $dataToUpdate['park_subarea_polygon'] = json_decode($request->polygon);
                }

                $subarea->update($dataToUpdate);

                // === LOGIKA SYNC AMENITIES ===
                if ($request->has('amenities')) {
                    $newAmenities = $request->amenities ?? [];
                    
                    // 1. Hapus yang tidak ada di list baru
                    $subarea->parkAmenity()
                        ->whereNotIn('park_amenity_name', $newAmenities)
                        ->delete();

                    // 2. Tambah yang belum ada
                    $existingNames = $subarea->parkAmenity()->pluck('park_amenity_name')->toArray();
                    foreach ($newAmenities as $amenityName) {
                        if (!in_array($amenityName, $existingNames)) {
                            $subarea->parkAmenity()->create([
                                'park_amenity_name' => $amenityName
                            ]);
                        }
                    }
                }

                Log::info('[WEB ParkSubareaController@update] Sukses: Subarea & Fasilitas diperbarui.', [
                    'subarea_id' => $id,
                    'name'       => $subarea->park_subarea_name
                ]);

                return response()->json([
                    'status'  => 'success', 
                    'message' => 'Subarea berhasil diperbarui.'
                ]);
            });

        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Validasi gagal.'], 422);
        } catch (Exception $e) {
            Log::error('[WEB ParkSubareaController@update] Gagal: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan server.'], 500);
        }
    }

    /**
     * Menghapus subarea.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $subarea = ParkSubarea::findOrFail($id);
                $areaId = $subarea->park_area_id; // Simpan ID induk untuk redirect
                $name = $subarea->park_subarea_name;

                $subarea->delete();

                Log::info('[WEB ParkSubareaController@destroy] Sukses: Subarea dihapus.', [
                    'subarea_id' => $id, 
                    'name' => $name
                ]);

                return redirect()->route('admin.park-area.show', $areaId)
                    ->with('swal_success_crud', 'Subarea berhasil dihapus.');
            });

        } catch (Exception $e) {
            Log::error('[WEB ParkSubareaController@destroy] Gagal: ' . $e->getMessage());
            return back()->with('swal_error_crud', 'Gagal menghapus subarea.');
        }
    }
}