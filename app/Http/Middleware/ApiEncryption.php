<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiEncryption
{
    private $method = 'AES-256-CBC';

    public function handle(Request $request, Closure $next)
    {
        $key = env('API_SECRET_KEY');
        $iv = env('API_SECRET_IV');

        // 1. DEKRIPSI REQUEST (Dari Flutter -> Laravel)
        // Cek apakah request methodnya POST/PUT/PATCH dan punya field 'payload'
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH']) && $request->has('payload')) {
            try {
                $encrypted = $request->input('payload');
                
                // Dekripsi AES
                $decrypted = openssl_decrypt($encrypted, $this->method, $key, 0, $iv);

                if ($decrypted) {
                    $data = json_decode($decrypted, true);
                    // Ganti input request enkripsi dengan data asli JSON
                    if (is_array($data)) {
                        $request->replace($data); 
                    }
                }
            } catch (\Exception $e) {
                // Jika gagal dekripsi, biarkan error atau return bad request
            }
        }

        // Lanjut ke Controller...
        $response = $next($request);

        // 2. ENKRIPSI RESPONSE (Dari Laravel -> Flutter)
        if ($response instanceof JsonResponse) {
            $originalData = $response->getData(true); // Ambil data asli
            
            // Hanya enkripsi jika sukses dan data valid
            if (is_array($originalData)) {
                try {
                    $jsonString = json_encode($originalData);
                    
                    // Enkripsi AES
                    $encrypted = openssl_encrypt($jsonString, $this->method, $key, 0, $iv);

                    // Bungkus response jadi { "payload": "..." }
                    $response->setData(['payload' => $encrypted]);
                } catch (\Exception $e) {
                    // Log error jika perlu
                }
            }
        }

        return $response;
    }
}