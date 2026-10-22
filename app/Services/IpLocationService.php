<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpLocationService
{
    /**
     * Mengekstrak alamat IP asli pengguna dari request header (termasuk Docker, Reverse Proxy, & Tunneling).
     *
     * @param  Request  $request  Object HTTP Request
     * @return string Alamat IP asli pengguna atau IP fallback
     */
    public function getRealIp(Request $request): string
    {
        // 1. Cek Header Cloudflare Tunnel / Proxy (Prioritas IP Publik)
        if ($cfIp = $request->header('CF-Connecting-IP')) {
            if ($this->isPublicIp($cfIp)) {
                return $cfIp;
            }
        }

        // 2. Cek Header X-Real-IP (Nginx, Ngrok, Reverse Proxy - Prioritas IP Publik)
        if ($realIp = $request->header('X-Real-IP')) {
            if ($this->isPublicIp($realIp)) {
                return $realIp;
            }
        }

        // 3. Cek Header X-Forwarded-For (Ambil IP publik pertama)
        $forwardedIps = [];
        if ($forwardedFor = $request->header('X-Forwarded-For')) {
            $forwardedIps = array_map('trim', explode(',', $forwardedFor));
            foreach ($forwardedIps as $ip) {
                if ($this->isPublicIp($ip)) {
                    return $ip;
                }
            }
        }

        // 4. Fallback ke IP non-publik dari Header jika diakses melalui Private Network / Local LAN / Docker Host
        if ($cfIp && $this->isValidIp($cfIp)) {
            return $cfIp;
        }

        if ($realIp && $this->isValidIp($realIp)) {
            return $realIp;
        }

        if (! empty($forwardedIps)) {
            foreach ($forwardedIps as $ip) {
                if ($this->isValidIp($ip)) {
                    return $ip;
                }
            }
        }

        // 5. Fallback ke IP bawaan Request
        return $request->ip() ?? '127.0.0.1';
    }

    /**
     * Mendapatkan nama kota dan negara berdasarkan alamat IP.
     *
     * @param  string|null  $ipAddress  Alamat IP target
     * @return string Format "Kota, Negara" atau "Tidak diketahui"
     */
    public function getLocation(?string $ipAddress): string
    {
        if (! $ipAddress) {
            return 'Tidak diketahui';
        }

        $targetIp = $ipAddress;

        // Jika IP lokal/private dan berada di environment local/testing,
        // hit ip-api tanpa parameter untuk mengambil IP publik koneksi lokal developer.
        if (! $this->isPublicIp($ipAddress)) {
            if (app()->environment('local', 'testing')) {
                $targetIp = null;
            } else {
                return 'Private Network';
            }
        }

        return $this->fetchLocation($targetIp);
    }

    /**
     * Memeriksa apakah alamat IP memiliki format IP yang valid (IPv4 atau IPv6).
     *
     * @param  string  $ip  Alamat IP
     * @return bool True jika IP valid
     */
    public function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Memeriksa apakah alamat IP adalah IP publik.
     *
     * @param  string  $ip  Alamat IP
     * @return bool True jika IP publik
     */
    public function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    /**
     * Melakukan HTTP Request ke ip-api.com untuk mengambil data lokasi.
     *
     * @param  string|null  $ip  Alamat IP publik atau null untuk IP WAN server
     * @return string Format "Kota, Negara"
     */
    private function fetchLocation(?string $ip): string
    {
        try {
            $url = $ip ? "http://ip-api.com/json/{$ip}" : 'http://ip-api.com/json/';
            $response = Http::timeout(5)->get($url);

            if ($response->successful() && $response->json('status') === 'success') {
                $city = $response->json('city');
                $country = $response->json('country');

                if ($city && $country) {
                    return "{$city}, {$country}";
                }
            }
        } catch (Exception $e) {
            Log::warning('Gagal melacak lokasi IP: '.$e->getMessage());
        }

        return 'Tidak diketahui';
    }
}
