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
        // Cek jika request ke API route
        if (!$this->isApiRequest($request)) {
            return $next($request);
        }
        
        $decryptedAesKey = null;
        $decryptedAesIv = null;
        
        // 1. LOAD RSA PRIVATE KEY
        $privateKey = $this->loadPrivateKey();
        if (!$privateKey) {
            return response()->json([
                'error' => 'server_error',
                'message' => 'Server configuration error'
            ], 500);
        }
        
        // 2. AMBIL DAN VALIDASI SESSION KEY
        $encryptedSession = $request->header('X-Session-Key');
        
        if (!$encryptedSession) {
            return response()->json([
                'error' => 'encryption_required',
                'message' => 'X-Session-Key header is required'
            ], 400);
        }
        
        // 3. DEKRIPSI SESSION KEY DENGAN RSA
        try {
            // Decode base64
            $encryptedKeyBin = base64_decode($encryptedSession);
            if ($encryptedKeyBin === false) {
                return response()->json([
                    'error' => 'invalid_format',
                    'message' => 'Invalid base64 encoding'
                ], 400);
            }
            
            // Dekripsi RSA
            $success = openssl_private_decrypt(
                $encryptedKeyBin,
                $decryptedSession,
                $privateKey,
                OPENSSL_PKCS1_PADDING
            );
            
            if (!$success) {
                $error = openssl_error_string();
                Log::error('[ApiEncryption] RSA Decryption Failed', [
                    'error' => $error,
                    'key_length' => strlen($encryptedSession)
                ]);
                
                return response()->json([
                    'error' => 'decryption_failed',
                    'message' => 'Failed to decrypt session key'
                ], 400);
            }
            
            // Bersihkan dan parse session data
            $decryptedSession = trim($decryptedSession);
            
            $parts = explode('|', $decryptedSession);
            
            if (count($parts) !== 2) {
                Log::error('[ApiEncryption] Invalid session format', [
                    'parts_count' => count($parts),
                    'session_data' => $decryptedSession
                ]);
                
                return response()->json([
                    'error' => 'invalid_session_format'
                ], 400);
            }
            
            $decryptedAesKey = trim($parts[0]);
            $decryptedAesIv = trim($parts[1]);
            
            // Validasi panjang
            if (strlen($decryptedAesKey) !== 32) {
                return response()->json([
                    'error' => 'invalid_key_length',
                    'message' => 'AES key must be 32 characters',
                    'actual_length' => strlen($decryptedAesKey)
                ], 400);
            }
            
            if (strlen($decryptedAesIv) !== 16) {
                return response()->json([
                    'error' => 'invalid_iv_length',
                    'message' => 'AES IV must be 16 characters',
                    'actual_length' => strlen($decryptedAesIv)
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('[ApiEncryption] Session key processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'session_key_error',
                'message' => 'Error processing session key'
            ], 400);
        }
        
        // 4. DEKRIPSI BODY
        if ($request->has('payload')) {
            try {
                $encryptedPayload = base64_decode($request->input('payload'));
                
                if ($encryptedPayload === false) {
                    return response()->json([
                        'error' => 'invalid_payload',
                        'message' => 'Payload is not valid base64'
                    ], 400);
                }
                
                $decryptedData = openssl_decrypt(
                    $encryptedPayload,
                    'AES-256-CBC',
                    $decryptedAesKey,
                    OPENSSL_RAW_DATA,
                    $decryptedAesIv
                );
                
                if ($decryptedData === false) {
                    return response()->json([
                        'error' => 'decryption_failed',
                        'message' => 'Failed to decrypt request body'
                    ], 400);
                }
                
                // Parse JSON
                $json = json_decode($decryptedData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'error' => 'invalid_json',
                        'message' => 'Decrypted data is not valid JSON'
                    ], 400);
                }
                
                if (!is_array($json)) {
                    return response()->json([
                        'error' => 'invalid_data',
                        'message' => 'Decrypted data must be a JSON object'
                    ], 400);
                }
                
                // Ganti request data
                $request->replace($json);
                
            } catch (\Exception $e) {
                Log::error('[ApiEncryption] Body decryption error', [
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'error' => 'body_decryption_error',
                    'message' => 'Failed to decrypt request body'
                ], 400);
            }
        }
        
        // 5. PROSES REQUEST
        $response = $next($request);
        
        // 6. ENKRIPSI RESPONSE
        if ($response instanceof JsonResponse && $decryptedAesKey && $decryptedAesIv) {
            try {
                $originalData = $response->getData(true);
                
                if (!is_array($originalData)) {
                    $originalData = ['data' => $originalData];
                }
                
                // Tambahkan metadata
                
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
                    'error' => $e->getMessage()
                ]);
                
                // Fallback: return error tanpa enkripsi
                return response()->json([
                    'error' => 'response_encryption_error',
                    'message' => 'Failed to encrypt response'
                ], 500);
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
        
        if (!file_exists($keyPath)) {
            Log::critical('[ApiEncryption] Private key not found at: ' . $keyPath);
            return null;
        }
        
        $privateKey = file_get_contents($keyPath);
        
        if (!$privateKey) {
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