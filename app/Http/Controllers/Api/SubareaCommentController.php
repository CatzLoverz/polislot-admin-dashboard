<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\SubareaComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class SubareaCommentController extends Controller
{
    /**
     * Menampilkan daftar komentar berdasarkan Subarea dengan Paginasi.
     * Mirip dengan HistoryController, output diformat ulang.
     * * @param Request $request (park_subarea_id, limit, page)
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Validasi: Kita butuh ID Subarea untuk tahu komentar mana yang diambil
        $request->validate([
            'park_subarea_id' => 'required|exists:park_subareas,park_subarea_id',
            'limit' => 'integer|min:1|max:100'
        ]);

        try {
            $subareaId = $request->park_subarea_id;
            $limit = $request->input('limit', 10); // Default 10 komentar per halaman

            // Query dengan Eager Loading User
            $comments = SubareaComment::with('user:user_id,name,avatar')
                ->where('park_subarea_id', $subareaId)
                ->orderBy('created_at', 'desc')
                ->paginate($limit);

            // Format data (Transformasi Collection)
            $formattedData = $comments->getCollection()->map(function ($item) {
                return [
                    'id'      => $item->subarea_comment_id,
                    'user'    => [
                        'name'   => $item->user->name ?? 'Unknown User',
                        'avatar' => $item->user->avatar ?? null, // Frontend bisa handle default avatar
                    ],
                    'content' => $item->subarea_comment_content,
                    'image'   => $item->subarea_comment_image ? asset('storage/' . $item->subarea_comment_image) : null,
                    'date'    => $item->created_at->format('d M Y'),
                    'time'    => $item->created_at->format('H:i'),
                    'timestamp' => $item->created_at->toIso8601String(),
                ];
            });

            // Susun Metadata Pagination
            $responseData = [
                'list' => $formattedData,
                'pagination' => [
                    'current_page' => $comments->currentPage(),
                    'last_page'    => $comments->lastPage(),
                    'per_page'     => $comments->perPage(),
                    'total'        => $comments->total(),
                ]
            ];

            return $this->sendSuccess('Daftar komentar berhasil diambil.', $responseData);

        } catch (Exception $e) {
            Log::error('[API SubareaComment@index] Error: ' . $e->getMessage());
            return $this->sendError('Gagal memuat komentar.', 500);
        }
    }

    /**
     * Menyimpan komentar baru (Store).
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'park_subarea_id'         => 'required|exists:park_subareas,park_subarea_id',
            'subarea_comment_content' => 'required|string|max:500',
            'subarea_comment_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $imagePath = null;

            // Logika Upload Image Baru
            if ($request->hasFile('subarea_comment_image')) {
                $imagePath = $request->file('subarea_comment_image')->store('Comments', 'public');
            }

            $comment = SubareaComment::create([
                'user_id'                 => $request->user()->user_id,
                'park_subarea_id'         => $request->park_subarea_id,
                'subarea_comment_content' => $request->subarea_comment_content,
                'subarea_comment_image'   => $imagePath
            ]);

            $comment->load('user:user_id,name,avatar');

            Log::info('[API SubareaComment@store] Sukses: Comment baru ditambahkan.');
            return $this->sendSuccess('Komentar terkirim.', $comment);

        } catch (Exception $e) {
            Log::error('[API SubareaComment@store] Error: ' . $e->getMessage());
            return $this->sendError('Gagal mengirim komentar.', 500);
        }
    }

    /**
     * Memperbarui komentar (Update).
     * * Mengganti konten atau gambar. Gambar lama akan dihapus jika ada gambar baru.
     * * @param Request $request
     * @param int $id ID SubareaComment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SubareaComment $comment)
    {
        $request->validate([
            'subarea_comment_content' => 'required|string|max:500',
            'subarea_comment_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Cek Kepemilikan (Authorization)
            if ($comment->user_id !== $request->user()->user_id) {
                return $this->sendError('Anda tidak berhak mengedit komentar ini.', 403);
            }

            $dataToUpdate = [
                'subarea_comment_content' => $request->subarea_comment_content
            ];

            // Logika Update Image (Hapus Lama -> Simpan Baru)
            if ($request->hasFile('subarea_comment_image')) {
                // 1. Hapus gambar lama jika ada
                if ($comment->subarea_comment_image && Storage::disk('public')->exists($comment->subarea_comment_image)) {
                    Storage::disk('public')->delete($comment->subarea_comment_image);
                }

                // 2. Simpan gambar baru
                $dataToUpdate['subarea_comment_image'] = $request->file('subarea_comment_image')->store('Comments', 'public');
            }

            $comment->update($dataToUpdate);
            $comment->load('user:user_id,name,avatar');

            Log::info('[API SubareaComment@update] Sukses: Comment Berhasil Diupdate.');
            return $this->sendSuccess('Komentar berhasil diperbarui.', $comment);

        } catch (Exception $e) {
            Log::error('[API SubareaComment@update] Error: ' . $e->getMessage());
            return $this->sendError('Gagal memperbarui komentar.', 500);
        }
    }

    /**
     * Menghapus komentar (Destroy).
     * * Menghapus data di database beserta file gambarnya di storage.
     * * @param Request $request
     * @param int $id ID SubareaComment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, SubareaComment $comment)
    {
        try {

            // Cek Kepemilikan (Authorization)
            if ($comment->user_id !== $request->user()->user_id) {
                return $this->sendError('Anda tidak berhak menghapus komentar ini.', 403);
            }

            // Logika Hapus Image
            if ($comment->subarea_comment_image && Storage::disk('public')->exists($comment->subarea_comment_image)) {
                Storage::disk('public')->delete($comment->subarea_comment_image);
            }

            $comment->delete();

            Log::info('[API SubareaComment@destroy] Sukses: Comment baru ditambahkan.');
            return $this->sendSuccess('Komentar berhasil dihapus.');

        } catch (Exception $e) {
            Log::error('[API SubareaComment@destroy] Error: ' . $e->getMessage());
            return $this->sendError('Gagal menghapus komentar.', 500);
        }
    }
}