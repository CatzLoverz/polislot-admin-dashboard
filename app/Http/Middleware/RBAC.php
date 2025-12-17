<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RBAC
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Mendapatkan user dengan cara yang kompatibel untuk API dan Web
        $user = $request->user();

        // Jika tidak ada user yang terautentikasi
        if (!$user) {
            // Untuk API, gunakan response JSON sesuai format Controller
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                    'data' => null
                ], 401);
            }
            
            // Untuk Web, redirect ke login
            return redirect()->route('login');
        }

        if (!app()->runningUnitTests()) {
            $connection = match ($user->role) {
                'admin' => 'mariadb',
                'user' => 'mariadb_mobile',
                default => config('database.default'),
            };

            config(['database.default' => $connection]);
            
            if (method_exists($user, 'setConnection')) {
                $user->setConnection($connection);
            }
        }

        // Atur koneksi database berdasarkan role user
        $connection = match ($user->role) {
            'admin' => 'mariadb',
            'user' => 'mariadb_mobile',
            default => config('database.default'),
        };

        config(['database.default' => $connection]);
        
        // Pastikan user menggunakan koneksi yang benar
        if (method_exists($user, 'setConnection')) {
            $user->setConnection($connection);
        }

        // Jika ada parameter role, lakukan pengecekan RBAC
        if (!empty($roles)) {
            // Periksa apakah role user ada dalam daftar roles yang diizinkan
            if (!in_array($user->role, $roles)) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized. Required role(s): ' . implode(', ', $roles),
                        'data' => null
                    ], 403);
                }
                
                // Untuk Web, kembalikan 403
                abort(403, 'Unauthorized. Required role(s): ' . implode(', ', $roles));
            }
        }

        return $next($request);
    }
}