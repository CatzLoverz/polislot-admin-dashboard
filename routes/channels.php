<?php

use App\Models\IotDevice;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Presence Channel untuk IoT Device (WebSocket).
 * 
 * Channel ini digunakan oleh:
 * 1. Python IoT Client (pysher) → join saat online, leave saat offline
 * 2. Web Admin UI (Echo.js) → join untuk mendeteksi status device secara real-time
 * 
 * Autentikasi IoT device dilakukan di IotWsAuthController (custom auth endpoint).
 * Channel ini hanya menghandle autentikasi Web Admin User (Laravel auth).
 */
Broadcast::channel('iot.device.{macAddress}', function ($user, $macAddress) {
    // Web admin user yang sudah login bisa join presence channel device manapun
    if ($user) {
        return [
            'id'   => $user->id,
            'name' => $user->name,
            'type' => 'admin',
        ];
    }

    return false;
});
