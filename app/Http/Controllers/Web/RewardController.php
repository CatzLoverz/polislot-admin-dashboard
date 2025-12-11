<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class RewardController extends Controller
{
    /**
     * Menampilkan halaman daftar master reward.
     * * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Reward::select('*')->orderByDesc('created_at');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('reward_point_required', function($row){
                    return number_format($row->reward_point_required) . ' Poin';
                })
                ->editColumn('reward_image', function($row){
                    if($row->reward_image) {
                        $url = asset('storage/' . $row->reward_image);
                        return '<img src="'.$url.'" class="img-thumbnail" width="60" alt="Reward Image">';
                    }
                    return '<span class="text-muted text-small">No Image</span>';
                })
                ->editColumn('reward_type', function($row){
                    $badge = $row->reward_type == 'Voucher' ? 'info' : 'primary';
                    return '<span class="badge badge-'.$badge.'">'.$row->reward_type.'</span>';
                })
                ->addColumn('action', function($row){
                    $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                    $title = e($row->reward_name);
                    
                    $btnEdit = '<button class="btn btn-link btn-primary btn-lg btn-edit" 
                                    data-row="'.$json.'" 
                                    data-update-url="'.route('admin.rewards.update', $row->reward_id).'"
                                    data-toggle="tooltip" 
                                    title="Edit '.$title.'">
                                    <i class="fa fa-edit"></i>
                                </button>';
                    
                    $btnDelete = '<form action="'.route('admin.rewards.destroy', $row->reward_id).'" 
                                        method="POST" class="delete-form d-inline"
                                        data-entity-name=" '.$title.'">
                                    '.csrf_field().method_field('DELETE').'
                                    <button type="submit" class="btn btn-link btn-danger btn-lg" 
                                        data-toggle="tooltip" title="Hapus '.$title.'">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                  </form>';

                    return '<div class="form-button-action d-flex justify-content-center">'.$btnEdit.$btnDelete.'</div>';
                })
                ->rawColumns(['reward_image', 'reward_type', 'action'])
                ->make(true);
        }

        return view('Contents.rewards.index');
    }

    /**
     * Menyimpan data reward baru.
     * * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'reward_name' => 'required|string|max:255',
                    'reward_type' => 'required|in:Voucher,Barang',
                    'reward_point_required' => 'required|integer|min:1',
                    'reward_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                ]);

                if ($request->hasFile('reward_image')) {
                    $validated['reward_image'] = $request->file('reward_image')->store('rewards', 'public');
                }

                $reward = Reward::create($validated);

                Log::info('[WEB RewardController@store] Sukses: Reward berhasil ditambahkan.', ['reward_id' => $reward->reward_id]);
                
                return back()->with('swal_success_crud', 'Reward berhasil ditambahkan.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal, periksa inputan Anda.');
        } catch (Exception $e) {
            Log::error('[WEB RewardController@store] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal menyimpan data.');
        }
    }

    /**
     * Memperbarui data reward.
     * * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $reward = Reward::findOrFail($id);

                $validated = $request->validate([
                    'reward_name' => 'required|string|max:255',
                    'reward_type' => 'required|in:Voucher,Barang',
                    'reward_point_required' => 'required|integer|min:1',
                    'reward_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                ]);

                if ($request->hasFile('reward_image')) {
                    if ($reward->reward_image && Storage::disk('public')->exists($reward->reward_image)) {
                        Storage::disk('public')->delete($reward->reward_image);
                    }
                    $validated['reward_image'] = $request->file('reward_image')->store('rewards', 'public');
                }

                $reward->update($validated);

                Log::info('[WEB RewardController@update] Sukses: Reward berhasil diperbarui.', ['reward_id' => $id]);
                
                return back()->with('swal_success_crud', 'Reward berhasil diperbarui.');
            });

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('swal_error_crud', 'Validasi gagal.');
        } catch (Exception $e) {
            Log::error('[WEB RewardController@update] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal memperbarui data.');
        }
    }

    /**
     * Menghapus data reward.
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $reward = Reward::findOrFail($id);
                
                if ($reward->reward_image && Storage::disk('public')->exists($reward->reward_image)) {
                    Storage::disk('public')->delete($reward->reward_image);
                }

                $reward->delete();
                
                Log::info('[WEB RewardController@destroy] Sukses: Reward berhasil dihapus.', ['reward_id' => $id]);
                
                return back()->with('swal_success_crud', 'Reward berhasil dihapus.');
            });
        } catch (Exception $e) {
            Log::error('[WEB RewardController@destroy] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Gagal menghapus reward.');
        }
    }
}