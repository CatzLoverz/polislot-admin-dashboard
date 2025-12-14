<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ParkAmenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ParkAmenityController extends Controller
{
    /**
     * Simpan satu fasilitas baru.
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate([
                    'park_subarea_id' => 'required|exists:park_subareas,park_subarea_id',
                    'park_amenity_name' => 'required|string|max:255',
                ]);

                $amenity = ParkAmenity::create([
                    'park_subarea_id' => $request->park_subarea_id,
                    'park_amenity_name' => $request->park_amenity_name,
                ]);

                // Return ID agar bisa tombol hapus berfungsi tanpa refresh
                return response()->json([
                    'status' => 'success', 
                    'message' => 'Fasilitas ditambahkan.',
                    'data' => $amenity
                ]);
            });
        } catch (Exception $e) {
            Log::error('[ParkAmenityController@store] Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan.'], 500);
        }
    }

    /**
     * Hapus satu fasilitas.
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $amenity = ParkAmenity::findOrFail($id);
                $amenity->delete();
                
                return response()->json(['status' => 'success', 'message' => 'Fasilitas dihapus.']);
            });
        } catch (Exception $e) {
            Log::error('[ParkAmenityController@destroy] Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus.'], 500);
        }
    }
}