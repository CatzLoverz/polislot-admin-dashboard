<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\IotStreamReceived;
use Illuminate\Support\Facades\Log;

class IotStreamController extends Controller
{
    /**
     * Endpoint untuk menerima frame dari perangkat IoT Python
     * dan mem-broadcast-nya via Reverb WebSockets.
     */
    public function receiveStream(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string',
            'frame'       => 'required|string', // Base64 encoded image
        ]);

        try {
            // Broadcast the frame to connected clients using the device MAC Address
            broadcast(new IotStreamReceived($request->mac_address, $request->frame));
            
            return response()->json([
                'status' => 'success',
                'message' => 'Frame broadcasted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('[API IotStreamController] Error broadcasting stream: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to broadcast frame.'
            ], 500);
        }
    }
}
