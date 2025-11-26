<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class RewardVerificationController extends Controller
{
    /**
     * Tampilkan halaman verifikasi kode reward untuk admin
     */
    public function index()
    {
        try {
            // Ambil semua user rewards dengan status belum dipakai
            $pendingRewards = DB::table('user_rewards')
                ->join('rewards', 'user_rewards.reward_id', '=', 'rewards.reward_id')
                ->join('users', 'user_rewards.user_id', '=', 'users.user_id')
                ->where('user_rewards.redeemed_status', 'belum dipakai')
                ->select(
                    'user_rewards.*',
                    'rewards.reward_name',
                    'rewards.reward_type',
                    'rewards.reward_image',
                    'rewards.points_required',
                    'users.name as user_name',
                    'users.email as user_email'
                )
                ->orderByDesc('user_rewards.created_at')
                ->paginate(10, ['*'], 'pending');
            
            // Ambil riwayat yang sudah terpakai
            $usedRewards = DB::table('user_rewards')
                ->join('rewards', 'user_rewards.reward_id', '=', 'rewards.reward_id')
                ->join('users', 'user_rewards.user_id', '=', 'users.user_id')
                ->where('user_rewards.redeemed_status', 'terpakai')
                ->select(
                    'user_rewards.*',
                    'rewards.reward_name',
                    'rewards.reward_type',
                    'users.name as user_name'
                )
                ->orderByDesc('user_rewards.updated_at')
                ->paginate(10, ['*'], 'used');

            Log::info('Admin membuka halaman verifikasi reward', ['admin_id' => Auth::id()]);

            return view('Contents.reward_verification.index', compact('pendingRewards', 'usedRewards'));
        } catch (Exception $e) {
            Log::error('Gagal menampilkan data verifikasi reward', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    /**
     * Verifikasi dan ubah status reward menjadi terpakai
     */
    public function verify(Request $request, $userRewardId)
    {
        try {
            $userReward = DB::table('user_rewards')->where('user_reward_id', $userRewardId)->first();
            
            if (!$userReward) {
                return back()->with('error', 'Data penukaran tidak ditemukan.');
            }
            
            if ($userReward->redeemed_status === 'terpakai') {
                return back()->with('error', 'Kode voucher ini sudah digunakan sebelumnya.');
            }
            
            // Update status menjadi terpakai
            DB::table('user_rewards')
                ->where('user_reward_id', $userRewardId)
                ->update([
                    'redeemed_status' => 'terpakai',
                    'updated_at' => now(),
                ]);
            
            Log::info('Admin memverifikasi reward', [
                'admin_id' => Auth::id(),
                'user_reward_id' => $userRewardId,
                'voucher_code' => $userReward->voucher_code
            ]);
            
            return redirect()->route('admin.reward_verification.index')
                ->with('swal_success_crud', 'Reward berhasil diverifikasi dan ditandai sebagai terpakai.');
                
        } catch (Exception $e) {
            Log::error('Gagal memverifikasi reward', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat memverifikasi reward.');
        }
    }

    /**
     * Cari kode voucher (untuk verifikasi cepat)
     */
    public function search(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string|max:100',
        ]);

        try {
            $voucherCode = strtoupper(trim($request->voucher_code));
            
            $userReward = DB::table('user_rewards')
                ->join('rewards', 'user_rewards.reward_id', '=', 'rewards.reward_id')
                ->join('users', 'user_rewards.user_id', '=', 'users.user_id')
                ->where('user_rewards.voucher_code', $voucherCode)
                ->select(
                    'user_rewards.*',
                    'rewards.reward_name',
                    'rewards.reward_type',
                    'rewards.reward_image',
                    'rewards.points_required',
                    'users.name as user_name',
                    'users.email as user_email'
                )
                ->first();
            
            if (!$userReward) {
                return back()->with('error', 'Kode voucher tidak ditemukan.');
            }
            
            Log::info('Admin mencari kode voucher', [
                'admin_id' => Auth::id(),
                'voucher_code' => $voucherCode
            ]);
            
            return back()->with('search_result', $userReward);
            
        } catch (Exception $e) {
            Log::error('Gagal mencari kode voucher', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat mencari kode.');
        }
    }

    /**
     * Hapus reward yang sudah terverifikasi/terpakai
     */
    public function destroy($userRewardId)
    {
        try {
            $userReward = DB::table('user_rewards')->where('user_reward_id', $userRewardId)->first();
            
            if (!$userReward) {
                return back()->with('error', 'Data penukaran tidak ditemukan.');
            }
            
            // Hanya bisa hapus yang sudah terpakai
            if ($userReward->redeemed_status !== 'terpakai') {
                return back()->with('error', 'Hanya reward yang sudah terpakai yang bisa dihapus.');
            }
            
            // Hapus data
            DB::table('user_rewards')->where('user_reward_id', $userRewardId)->delete();
            
            Log::info('Admin menghapus reward terverifikasi', [
                'admin_id' => Auth::id(),
                'user_reward_id' => $userRewardId,
                'voucher_code' => $userReward->voucher_code
            ]);
            
            return redirect()->route('admin.reward_verification.index')
                ->with('swal_success_crud', 'Reward yang sudah terpakai berhasil dihapus.');
                
        } catch (Exception $e) {
            Log::error('Gagal menghapus reward terverifikasi', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    /**
     * Hapus semua reward yang sudah terpakai (bulk delete)
     */
    public function destroyAll()
    {
        try {
            $deleted = DB::table('user_rewards')
                ->where('redeemed_status', 'terpakai')
                ->delete();
            
            Log::info('Admin menghapus semua reward terverifikasi', [
                'admin_id' => Auth::id(),
                'total_deleted' => $deleted
            ]);
            
            return redirect()->route('admin.reward_verification.index')
                ->with('swal_success_crud', "Berhasil menghapus {$deleted} reward yang sudah terpakai.");
                
        } catch (Exception $e) {
            Log::error('Gagal menghapus semua reward terverifikasi', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}