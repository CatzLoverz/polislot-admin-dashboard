<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use Illuminate\Http\Request;

class IotStreamViewerController extends Controller
{
    /**
     * Menampilkan halaman IoT Stream Viewer.
     * MAC Address dipilih dari daftar perangkat yang terdaftar di database.
     */
    public function index(Request $request)
    {
        // Ambil semua device dari database, termasuk subarea dan area induknya
        $devices = IotDevice::with('subarea.parkArea')->get();
        
        // MAC Address yang dipilih (dari query string atau default ke device pertama)
        $targetMac = $request->query('mac', $devices->first()?->device_mac_address ?? '00:00:00:00:00:00');
        
        return view('Contents.IotStream.viewer', compact('devices', 'targetMac'));
    }

    /**
     * Mengirim perintah 'snapshot' ke perangkat IoT via MQTT
     */
    public function triggerSnapshot(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string'
        ]);

        $mac = $request->mac_address;
        $topic = "polislot/device/{$mac}/command";
        
        $payload = json_encode([
            'action' => 'snapshot',
            'timestamp' => time(),
            'requested_by' => auth()->user()->id ?? 'admin'
        ]);

        try {
            // Gunakan QoS 0 (fire and forget) agar tidak perlu mem-block menunggu balasan (ACK) dari Broker
            \PhpMqtt\Client\Facades\MQTT::publish($topic, $payload, 0); 
            
            return response()->json([
                'success' => true,
                'message' => "Perintah snapshot berhasil dikirim ke perangkat {$mac}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal menghubungi MQTT Broker: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengirim pesan Live Chat (Proof of Concept WebSockets)
     */
    public function sendChat(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string',
            'username' => 'required|string|max:50',
            'message' => 'required|string|max:500'
        ]);

        $mac = $request->mac_address;
        $topic = "polislot/device/{$mac}/command";
        
        $payload = json_encode([
            'action' => 'chat',
            'timestamp' => time(),
            'username' => $request->username,
            'message' => $request->message
        ]);

        try {
            // 1. Kirim pesan chat via MQTT ke IoT Device
            \PhpMqtt\Client\Facades\MQTT::publish($topic, $payload, 0);
            
            // 2. Broadcast ke Web UI kita sendiri agar muncul di layar (Reverb)
            broadcast(new \App\Events\ChatMessageSent($request->username, $request->message));
            
            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal mengirim chat: " . $e->getMessage()
            ], 500);
        }
    }
}
