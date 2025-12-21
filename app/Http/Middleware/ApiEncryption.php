<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiEncryption
{
    public function handle(Request $request, Closure $next)
    {
        // Cek jika request ke API route
        if (! $this->isApiRequest($request)) {
            return $next($request);
        }

        $decryptedAesKey = null;
        $decryptedAesIv = null;

        // 1. LOAD RSA PRIVATE KEY
        $privateKey = $this->loadPrivateKey();
        if (! $privateKey) {
            // Generic Server Error
            return response()->json(['message' => 'Server Error'], 500);
        }

        // 2. CHECK & LOAD KEYS (Obscured)
        try {
            // A. VALIDASI KEY HEADER
            $encryptedSession = $request->header('X-Session-Key');
            if (! $encryptedSession) {
                // Silent fail or generic bad request
                return response()->json(['message' => 'Invalid Request'], 400);
            }

            // B. DEKRIPSI RSA (Session Key)
            $encryptedKeyBin = base64_decode($encryptedSession);
            if ($encryptedKeyBin === false) {
                throw new \Exception('Invalid Base64');
            }

            $success = openssl_private_decrypt(
                $encryptedKeyBin,
                $decryptedSession,
                $privateKey,
                OPENSSL_PKCS1_PADDING
            );

            if (! $success) {
                throw new \Exception('RSA Decrypt Failed');
            }

            // C. PARSE SESSION DATA
            $decryptedSession = trim($decryptedSession);
            $parts = explode('|', $decryptedSession);

            if (count($parts) !== 2) {
                throw new \Exception('Invalid Session Format');
            }

            $decryptedAesKey = trim($parts[0]);
            $decryptedAesIv = trim($parts[1]);

            // Validasi panjang key (AES-256 = 32 bytes, IV = 16 bytes)
            if (strlen($decryptedAesKey) !== 32 || strlen($decryptedAesIv) !== 16) {
                throw new \Exception('Invalid Key/IV Length');
            }

        } catch (\Exception $e) {
            // Log detail untuk admin, tapi return generic ke client
            Log::error('[ApiEncryption] Handshake Failed: '.$e->getMessage());

            return response()->json(['message' => 'Invalid Request Signature'], 400);
        }

        // 🚨 SECURITY HARDENING: Security through Obscurity
        // Logic cleanup: Stop giving hints to attackers.

        // 1. Cek Anomaly: Raw Auth Header tanpa X-Auth-Token
        // Indikasi percobaan bypass / replay attack.
        // Return 404 Not Found seolah-olah endpoint tidak ada/salah.
        if ($request->hasHeader('Authorization') && ! $request->hasHeader('X-Auth-Token')) {
            Log::warning('[ApiEncryption] Suspicious request: Raw Authorization without X-Auth-Token');

            return response()->json(['message' => 'Not Found'], 404);
        }

        // Hapus header Authorization bawaan untuk memastikan bersih
        // Kita hanya akan menset ulang jika X-Auth-Token valid berhasil didekripsi.
        $request->headers->remove('Authorization');
        $request->server->remove('HTTP_AUTHORIZATION');

        // 🚨 DEKRIPSI AUTH TOKEN (Jika ada)
        // Mencegah token terlihat di MITM
        if ($request->hasHeader('X-Auth-Token')) {
            try {
                $encryptedToken = base64_decode($request->header('X-Auth-Token'));

                if ($encryptedToken) {
                    $decryptedToken = openssl_decrypt(
                        $encryptedToken,
                        'AES-256-CBC',
                        $decryptedAesKey,
                        OPENSSL_RAW_DATA,
                        $decryptedAesIv
                    );

                    if ($decryptedToken !== false) {
                        // CLEANUP: Potential padding issues
                        $decryptedToken = trim($decryptedToken);

                        // Restore ke Authorization header standard
                        $request->headers->set('Authorization', 'Bearer '.$decryptedToken);
                        // Failsafe: Set SERVER var juga (untuk library yang baca $_SERVER langsung)
                        $request->server->set('HTTP_AUTHORIZATION', 'Bearer '.$decryptedToken);
                    } else {
                        Log::warning('[ApiEncryption] Auth Token Decryption Failed');

                        // Obscure: Generic 400
                        return response()->json(['message' => 'Invalid Request Signature'], 400);
                    }
                }
            } catch (\Exception $e) {
                Log::error('[ApiEncryption] Auth Token processing error', [
                    'error' => $e->getMessage(),
                ]);

                return response()->json(['message' => 'Invalid Request Signature'], 400);
            }
        }

        // 3. DECRYPT PAYLOAD (Obscured)
        if ($request->has('payload')) {
            try {
                $encryptedPayload = base64_decode($request->input('payload'));

                if ($encryptedPayload === false) {
                    throw new \Exception('Invalid Payload Base64');
                }

                $decryptedData = openssl_decrypt(
                    $encryptedPayload,
                    'AES-256-CBC',
                    $decryptedAesKey,
                    OPENSSL_RAW_DATA,
                    $decryptedAesIv
                );

                if ($decryptedData === false) {
                    throw new \Exception('Body Decryption Failed');
                }

                // Parse JSON
                $json = json_decode($decryptedData, true);

                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($json)) {
                    throw new \Exception('Invalid JSON in Payload');
                }

                // Ganti request data
                $request->replace($json);

            } catch (\Exception $e) {
                Log::error('[ApiEncryption] Payload Processing Failed: '.$e->getMessage());

                return response()->json(['message' => 'Invalid Request Data'], 400);
            }
        }

        // 5. PROSES REQUEST
        $response = $next($request);

        // 6. ENKRIPSI RESPONSE
        if ($response instanceof JsonResponse && $decryptedAesKey && $decryptedAesIv) {
            try {
                $originalData = $response->getData(true);

                if (! is_array($originalData)) {
                    $originalData = ['data' => $originalData];
                }

                $jsonString = json_encode($originalData);

                $encryptedResponse = openssl_encrypt(
                    $jsonString,
                    'AES-256-CBC',
                    $decryptedAesKey,
                    OPENSSL_RAW_DATA,
                    $decryptedAesIv
                );

                if ($encryptedResponse === false) {
                    throw new \Exception('openssl_encrypt failed');
                }

                $response->setData([
                    'payload' => base64_encode($encryptedResponse),
                ]);

            } catch (\Exception $e) {
                Log::error('[ApiEncryption] Response encryption failed', [
                    'error' => $e->getMessage(),
                ]);

                // Fallback: return Generic Error tanpa enkripsi
                return response()->json(['message' => 'Server Error'], 500);
            }
        }

        return $response;
    }

    /**
     * Load private key from storage
     */
    private function loadPrivateKey()
    {
        $keyPath = storage_path('app/private/keys/private_key.pem');

        if (! file_exists($keyPath)) {
            Log::critical('[ApiEncryption] Private key not found at: '.$keyPath);

            return null;
        }

        $privateKey = file_get_contents($keyPath);

        if (! $privateKey) {
            Log::critical('[ApiEncryption] Failed to read private key');

            return null;
        }

        return $privateKey;
    }

    /**
     * Check if request is for API
     */
    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($request->path(), 'api/') ||
               $request->expectsJson() ||
               $request->hasHeader('X-Session-Key');
    }

    /**
     * Check if request method should have body
     */
    private function shouldHaveBody(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']);
    }
}
