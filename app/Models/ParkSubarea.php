<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;
use App\Events\IotCommandSent;
use App\Events\IotThresholdUpdated;

class ParkSubarea extends Model
{
    protected $table = 'park_subareas';
    protected $primaryKey = 'park_subarea_id';
    
    protected $fillable = [
        'park_area_id',
        'park_subarea_name',
        'park_subarea_polygon',
        'max_slots',
        'detection_polygon',
        'current_count',
        'threshold_banyak',
        'threshold_terbatas',
    ];

    protected $casts = [
        'park_subarea_polygon' => 'array',
        'detection_polygon' => 'array',
        'max_slots' => 'integer',
        'current_count' => 'integer',
        'threshold_banyak' => 'double',
        'threshold_terbatas' => 'double',
    ];

    public function parkArea()
    {
        return $this->belongsTo(ParkArea::class, 'park_area_id', 'park_area_id');
    }

    public function parkAmenity()
    {
        return $this->hasMany(ParkAmenity::class, 'park_subarea_id', 'park_subarea_id');
    }

    public function userValidation()
    {
        return $this->hasMany(UserValidation::class, 'park_subarea_id', 'park_subarea_id');
    }

    public function subareaComment()
    {
        return $this->hasMany(SubareaComment::class, 'park_subarea_id', 'park_subarea_id');
    }

    public function iotDevice()
    {
        return $this->hasOne(IotDevice::class, 'park_subarea_id', 'park_subarea_id');
    }

    /**
     * Mendapatkan status dan warna subarea terkini secara real-time.
     * Logika ini digabungkan dari controller untuk menghindari duplikasi kode.
     * 
     * @return array
     */
    public function getLiveStatus(): array
    {
        $status = 'netral';
        $isValidated = false;
        $hasUserReport = false;

        // 1. Filter validasi dalam 5 menit terakhir (urutkan terbaru dahulu untuk tie-breaker)
        $cutoffTime = now()->subMinutes(5);
        $validVotes = $this->userValidation()
            ->where('created_at', '>=', $cutoffTime)
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Tentukan status dari deteksi AI perangkat IoT
        $hasOnlineIot = false;
        $cvStatus = 'netral';
        $occupancy = 0.0;

        if ($this->iotDevice) {
            $mac = $this->iotDevice->device_mac_address;
            $deviceStatus = IotDevice::syncStatus($mac);
            if ($deviceStatus === 'online') {
                $hasOnlineIot = true;
                if ($this->max_slots > 0) {
                    $occupancy = ($this->current_count / $this->max_slots) * 100;
                    if ($occupancy < ($this->threshold_banyak ?? 30.0)) {
                        $cvStatus = 'banyak';
                    } elseif ($occupancy >= ($this->threshold_terbatas ?? 80.0)) {
                        $cvStatus = 'penuh';
                    } else {
                        $cvStatus = 'terbatas';
                    }
                } else {
                    $cvStatus = 'banyak';
                }
            }
        }

        // 3. Bandingkan dengan hasil vote validasi pengguna
        if ($validVotes->isNotEmpty()) {
            $counts = $validVotes->countBy('user_validation_content');
            $maxVote = $counts->max();
            $candidates = $counts->keys()->filter(function($key) use ($counts, $maxVote) {
                return $counts[$key] === $maxVote;
            });

            $votedStatus = 'banyak';
            if ($candidates->count() === 1) {
                $votedStatus = $candidates->first();
            } else {
                // Tie-breaker: ambil vote terakhir dari kandidat yang seri
                $latestDecider = $validVotes->first(function ($vote) use ($candidates) {
                    return $candidates->contains($vote->user_validation_content);
                });
                if ($latestDecider) {
                    $votedStatus = $latestDecider->user_validation_content;
                }
            }

            $status = $votedStatus;

            if ($hasOnlineIot) {
                if ($votedStatus === $cvStatus) {
                    $isValidated = true;
                } else {
                    $hasUserReport = true;
                }
            }
        } else {
            // Jika tidak ada vote, fallback ke status deteksi IoT (bila online), atau netral
            if ($hasOnlineIot) {
                $status = $cvStatus;
            } else {
                $status = 'netral';
            }
        }

        // 4. Konversi status ke kode warna
        $status_color = '#1572e8'; // Default: Netral (Biru)
        if ($status === 'penuh') {
            $status_color = '#f25961'; // Merah
        } elseif ($status === 'terbatas') {
            $status_color = '#ffad46'; // Orange/Kuning
        } elseif ($status === 'banyak') {
            $status_color = '#31ce36'; // Hijau
        }

        // Tentukan fallback status (jika tidak ada vote)
        $fallbackStatus = 'netral';
        if ($hasOnlineIot) {
            $fallbackStatus = $cvStatus;
        }

        $fallbackStatusColor = '#1572e8'; // Default: Netral (Biru)
        if ($fallbackStatus === 'penuh') {
            $fallbackStatusColor = '#f25961'; // Merah
        } elseif ($fallbackStatus === 'terbatas') {
            $fallbackStatusColor = '#ffad46'; // Orange/Kuning
        } elseif ($fallbackStatus === 'banyak') {
            $fallbackStatusColor = '#31ce36'; // Hijau
        }

        $validationExpiresAt = null;
        $lastValidationTime = null;
        $validationRemainingSeconds = 0;
        if ($validVotes->isNotEmpty()) {
            $latestVote = $validVotes->first();
            $expiresAt = $latestVote->created_at->addMinutes(5);
            $validationExpiresAt = $expiresAt->toIso8601String();
            $lastValidationTime = $latestVote->created_at->toIso8601String();
            $validationRemainingSeconds = max(0, now()->diffInSeconds($expiresAt, false));
        }

        return [
            'status'                         => $status,
            'status_color'                   => $status_color,
            'is_validated'                   => $isValidated,
            'has_user_report'                => $hasUserReport,
            'has_online_iot'                 => $hasOnlineIot,
            'validation_expires_at'          => $validationExpiresAt,
            'last_validation_time'           => $lastValidationTime,
            'validation_remaining_seconds'   => $validationRemainingSeconds,
            'fallback_status'                => $fallbackStatus,
            'fallback_status_color'          => $fallbackStatusColor,
        ];
    }

    /**
     * Mengevaluasi pergeseran threshold WMA jika ada minimal 3 validation dalam 5 menit terakhir.
     */
    public function evaluateThresholdShift()
    {
        if ($this->max_slots <= 0) {
            return;
        }

        // 1. Ambil semua validation dalam 5 menit terakhir
        $validations = $this->userValidation()
            ->where('created_at', '>=', now()->subMinutes(5))
            ->get();

        if ($validations->count() < 3) {
            return; // Minimal 3 user untuk mengubah threshold
        }

        // 2. Hitung mayoritas vote
        $counts = $validations->countBy('user_validation_content');
        $maxVote = $counts->max();
        
        // Cari status dengan vote terbanyak
        $candidates = $counts->keys()->filter(function($key) use ($counts, $maxVote) {
            return $counts[$key] === $maxVote;
        });

        // Jika seri/tie, tidak ada mayoritas tunggal -> batalkan pergeseran threshold
        if ($candidates->count() !== 1) {
            return;
        }

        $votedStatus = $candidates->first();

        // 3. Tentukan status CV saat ini
        $occupancy = ($this->current_count / $this->max_slots) * 100;
        $cvStatus = 'netral';
        if ($occupancy < ($this->threshold_banyak ?? 30.0)) {
            $cvStatus = 'banyak';
        } elseif ($occupancy >= ($this->threshold_terbatas ?? 80.0)) {
            $cvStatus = 'penuh';
        } else {
            $cvStatus = 'terbatas';
        }

        // Hanya geser threshold jika status vote berbeda dengan status CV
        if ($votedStatus !== $cvStatus) {
            $alpha = 0.05; // Weight untuk WMA (5%)
            
            if ($votedStatus === 'banyak') {
                $this->threshold_banyak = ($this->threshold_banyak * (1 - $alpha)) + ($occupancy * $alpha);
            } elseif ($votedStatus === 'terbatas') {
                $this->threshold_terbatas = ($this->threshold_terbatas * (1 - $alpha)) + ($occupancy * $alpha);
            } elseif ($votedStatus === 'penuh') {
                $this->threshold_terbatas = ($this->threshold_terbatas * (1 - $alpha)) + ($occupancy * $alpha);
            }

            // Batasi threshold agar tetap valid dan masuk akal
            $this->threshold_banyak = max(5.0, min(90.0, $this->threshold_banyak));
            $this->threshold_terbatas = max($this->threshold_banyak + 5.0, min(95.0, $this->threshold_terbatas));
            $this->save();

            Log::info("Threshold shifted for Subarea {$this->park_subarea_name} (ID: {$this->park_subarea_id}). New thresholds: banyak={$this->threshold_banyak}%, terbatas={$this->threshold_terbatas}%.");

            // 4. Kirim update_config secara otomatis ke device IoT via MQTT & WS jika device online
            $device = $this->iotDevice;
            if ($device) {
                $mac = $device->device_mac_address;

                // Broadcast event ke Web UI untuk silent refresh slider/treshold
                try {
                    broadcast(new IotThresholdUpdated($mac, $this->threshold_banyak, $this->threshold_terbatas));
                } catch (\Exception $e) {
                    Log::warning("Failed to broadcast threshold update: " . $e->getMessage());
                }

                $payloadData = [
                    'action'            => 'update_config',
                    'max_slots'         => (int) $this->max_slots,
                    'detection_polygon' => $this->detection_polygon ?? [],
                    'threshold_banyak'  => (float) $this->threshold_banyak,
                    'threshold_terbatas'=> (float) $this->threshold_terbatas,
                    'timestamp'         => time(),
                ];

                // Generate signature
                $key32 = substr(hash('sha256', config('services.iot.secret'), true), 0, 32);
                $payloadData['signature'] = hash_hmac('sha256', json_encode($payloadData, JSON_UNESCAPED_SLASHES), $key32);

                // Publish MQTT
                try {
                    $topic = "polislot/device/{$mac}/command";
                    $payload = json_encode($payloadData, JSON_UNESCAPED_SLASHES);
                    $mqtt = MQTT::connection('publisher');
                    $mqtt->publish($topic, $payload, 0);
                    $mqtt->disconnect();
                } catch (\Exception $e) {
                    Log::warning("Failed to sync config via MQTT: " . $e->getMessage());
                }

                // Broadcast WS
                try {
                    broadcast(new IotCommandSent($mac, 'update_config', $payloadData, $payloadData['signature']));
                } catch (\Exception $e) {
                    Log::warning("Failed to sync config via WS: " . $e->getMessage());
                }
            }
        }
    }
}