<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\HistoryService;
use App\Models\UserReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class RewardVerificationController extends Controller
{
    protected $historyService;

    public function __construct(HistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    /**
     * Menampilkan daftar antrian klaim reward.
     * * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = UserReward::with(['user', 'reward'])
                ->select('user_rewards.*');

            if ($request->filled('filter_status')) {
                $query->where('user_reward_status', $request->filter_status);
            }
            if ($request->filled('filter_type')) {
                $query->whereHas('reward', function($q) use ($request) {
                    $q->where('reward_type', $request->filter_type);
                });
            }

            if (!$request->order) {
                $query->orderByRaw("FIELD(user_reward_status, 'pending', 'accepted', 'rejected')")
                      ->orderBy('created_at', 'asc');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_name', fn($row) => $row->user->name ?? 'Unknown')
                ->addColumn('reward_info', function($row){
                    $name = $row->reward->reward_name ?? '-';
                    $type = $row->reward->reward_type ?? '-';
                    return "<b>{$name}</b> <br><small class='text-muted'>{$type}</small>";
                })
                ->editColumn('user_reward_code', fn($row) => "<code>{$row->user_reward_code}</code>")
                ->editColumn('user_reward_status', function($row){
                    $status = $row->user_reward_status;
                    $badges = ['pending' => 'warning', 'accepted' => 'success', 'rejected' => 'danger'];
                    $color = $badges[$status] ?? 'secondary';
                    return "<span class='badge badge-{$color}'>" . strtoupper($status) . "</span>";
                })
                ->editColumn('created_at', fn($row) => $row->created_at->format('d M Y H:i'))
                // PERUBAHAN: Menambahkan kolom Updated At
                ->editColumn('updated_at', function($row) {
                    // Jika status masih pending, tampilkan strip agar lebih bersih
                    if ($row->user_reward_status === 'pending') {
                        return '<span class="text-muted">-</span>';
                    }
                    return $row->updated_at ? $row->updated_at->format('d M Y H:i') : '-';
                })
                ->addColumn('action', function($row){
                    if ($row->user_reward_status !== 'pending') {
                        return '<span class="text-muted"><i class="fa fa-check-circle"></i> Selesai</span>';
                    }

                    $btnAcc = '<form action="'.route('admin.rewards.verify.process', $row->user_reward_id).'" method="POST" class="d-inline">
                                '.csrf_field().'
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="btn btn-icon btn-round btn-success btn-sm mr-1" 
                                    data-toggle="tooltip" title="Terima Klaim">
                                    <i class="fa fa-check"></i>
                                </button>
                               </form>';

                    $rewardName = $row->reward->reward_name ?? 'Reward ini';
                    $userName = $row->user->name ?? 'User';
                    $confirmMsg = "Tolak klaim <b>{$rewardName}</b> dari <b>{$userName}</b>? <br>Poin akan dikembalikan ke user.";

                    $btnRej = '<form action="'.route('admin.rewards.verify.process', $row->user_reward_id).'" method="POST" class="d-inline reject-form" data-msg="'.$confirmMsg.'">
                                '.csrf_field().'
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-icon btn-round btn-danger btn-sm" 
                                    data-toggle="tooltip" title="Tolak Klaim">
                                    <i class="fa fa-times"></i>
                                </button>
                               </form>';

                    return '<div class="form-button-action d-flex justify-content-center">'.$btnAcc.$btnRej.'</div>';
                })
                ->rawColumns(['reward_info', 'user_reward_code', 'user_reward_status', 'updated_at', 'action'])
                ->make(true);
        }

        return view('Contents.rewards.verify');
    }

    /**
     * Memproses persetujuan atau penolakan klaim reward.
     * * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $claim = UserReward::with(['user', 'reward'])->lockForUpdate()->findOrFail($id);
                $newStatus = $request->status;

                if (!in_array($newStatus, ['accepted', 'rejected'])) {
                    return back()->with('swal_error_crud', 'Status tidak valid.');
                }

                if ($claim->user_reward_status !== 'pending') {
                    return back()->with('swal_error_crud', 'Klaim ini sudah diproses sebelumnya.');
                }

                $claim->user_reward_status = $newStatus;
                $claim->save();

                if ($newStatus === 'accepted') {
                    $this->historyService->log(
                        $claim->user_id,
                        'redeem',
                        $claim->reward->reward_name,
                        null,
                        true
                    );
                    
                    $msg = 'Klaim berhasil diterima.';
                } 
                
                // KASUS 2: DITOLAK (REJECTED)
                elseif ($newStatus === 'rejected') {
                    $pointsToRefund = $claim->reward->reward_point_required ?? 0;
                    
                    if ($pointsToRefund > 0 && $claim->user) {
                        $claim->user->increment('current_points', $pointsToRefund);
                        Log::info('[WEB RewardVerificationController@process] Info: Poin dikembalikan ke user.', ['user_id' => $claim->user_id]);

                        // Catat History Refund
                        $this->historyService->log(
                            $claim->user_id, 
                            'redeem', 
                            $claim->reward->reward_name, 
                            $pointsToRefund,
                            false
                        );
                    }
                    $msg = 'Klaim ditolak, poin telah dikembalikan.';
                }
                
                Log::info('[WEB RewardVerificationController@process] Sukses: Status klaim diperbarui.', ['id' => $id, 'status' => $newStatus]);

                return back()->with('swal_success_crud', $msg);
            });

        } catch (Exception $e) {
            Log::error('[WEB RewardVerificationController@process] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return back()->with('swal_error_crud', 'Terjadi kesalahan sistem.');
        }
    }
}