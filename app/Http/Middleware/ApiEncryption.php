<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ApiEncryption
{
    public function handle(Request $request, Closure $next)
    {
        $decryptedAesKey = null;
        $decryptedAesIv = null;

        // 1. LOAD RSA PRIVATE KEY
        $keyPath = base_path('storage/app/private/keys/private_key.pem');
        if (file_exists($keyPath)) {
            $privateKey = file_get_contents($keyPath);

            // 2. AMBIL KUNCI SESI DARI HEADER (X-Session-Key)
            $encryptedSession = $request->header('X-Session-Key');

            if ($encryptedSession) {
                try {
                    $encryptedKeyBin = base64_decode($encryptedSession);
                    
                    // Dekripsi RSA
                    if (openssl_private_decrypt($encryptedKeyBin, $decryptedSession, $privateKey)) {
                        $parts = explode('|', $decryptedSession);
                        if (count($parts) === 2) {
                            $decryptedAesKey = $parts[0];
                            $decryptedAesIv = $parts[1];
                            
                            // Kunci Sesi Ditemukan! Sekarang kita bisa mendekripsi body (jika ada)
                            // dan mengenkripsi response nanti.
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('[ApiEncryption] Key Decryption Failed: ' . $e->getMessage());
                }
            }

            // 3. DEKRIPSI BODY (Jika method POST/PUT/PATCH dan ada payload)
            if ($decryptedAesKey && $request->has('payload')) {
                try {
                    $encryptedPayload = base64_decode($request->input('payload'));
                    $decryptedData = openssl_decrypt(
                        $encryptedPayload, 'AES-256-CBC', $decryptedAesKey, OPENSSL_RAW_DATA, $decryptedAesIv
                    );

                    if ($decryptedData) {
                        $json = json_decode($decryptedData, true);
                        if (is_array($json)) {
                            $request->merge($json); // Merge data asli ke request
                            $request->request->remove('payload'); // Bersihkan payload terenkripsi
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('[ApiEncryption] Body Decryption Failed');
                }
            }
        }

        // Lanjut ke Controller...
        $response = $next($request);

        // 4. ENKRIPSI RESPONSE
        // Syarat: Kita punya kunci AES dari header request tadi
        if ($response instanceof JsonResponse && $decryptedAesKey && $decryptedAesIv) {
            $originalData = $response->getData(true);
            
            if (is_array($originalData)) {
                try {
                    $jsonString = json_encode($originalData);
                    
                    $encryptedResponse = openssl_encrypt(
                        $jsonString, 'AES-256-CBC', $decryptedAesKey, 0, $decryptedAesIv
                    );

                    // Ganti respon jadi terenkripsi
                    $response->setData(['payload' => $encryptedResponse]);
                } catch (\Exception $e) {
                    Log::error('[ApiEncryption] Response Encryption Failed');
                }
            }
        }

        return $response;
    }
}