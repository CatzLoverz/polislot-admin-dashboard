<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\TestWebSocket;
use Illuminate\Support\Facades\Log;
use Exception;

class TestWsController extends Controller
{
    /**
     * Menampilkan halaman tester websocket.
     */
    public function index()
    {
        return view('Contents.IotDevice.test-ws');
    }

    /**
     * Men-trigger event untuk disebarkan via WebSocket.
     */
    public function push(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:255'
            ]);

            // Dispatch event secara sinkron "sekarang" juga (ShouldBroadcastNow)
            TestWebSocket::dispatch($validated['message']);

            return response()->json([
                'success' => true, 
                'message' => 'Pesan terkirim ke Reverb WebSocket Server!'
            ]);

        } catch (Exception $e) {
            Log::error('[TestWsController] Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage()
            ], 500);
        }
    }
}
