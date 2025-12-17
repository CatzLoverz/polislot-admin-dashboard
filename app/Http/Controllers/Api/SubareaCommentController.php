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
    public function update(Request $request, $id)
    {
        $request->validate([
            'subarea_comment_content' => 'required|string|max:500',
            'subarea_comment_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $comment = SubareaComment::findOrFail($id);

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
    public function destroy(Request $request, $id)
    {
        try {
            $comment = SubareaComment::findOrFail($id);

            // Cek Kepemilikan (Authorization)
            if ($comment->user_id !== $request->user()->user_id) {
                return $this->sendError('Anda tidak berhak menghapus komentar ini.', 403);
            }

            // Logika Hapus Image
            if ($comment->subarea_comment_image && Storage::disk('public')->exists($comment->subarea_comment_image)) {
                Storage::disk('public')->delete($comment->subarea_comment_image);
            }

            $comment->delete();

            return $this->sendSuccess('Komentar berhasil dihapus.');

        } catch (Exception $e) {
            Log::error('[API SubareaComment@destroy] Error: ' . $e->getMessage());
            return $this->sendError('Gagal menghapus komentar.', 500);
        }
    }
}