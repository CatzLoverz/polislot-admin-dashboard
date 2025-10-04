<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek jika pengguna sudah login DAN belum pernah mengganti password
        if (Auth::check() && !Auth::user()->pass_change) {
            
            // Periksa apakah request saat ini BUKAN menuju ke halaman profil atau proses logout
            if (!$request->routeIs('profile.edit') && !$request->routeIs('profile.update') && !$request->routeIs('logout')) {
                
                // Jika ya, PAKSA redirect ke halaman edit profil.
                // Tambahkan pesan peringatan untuk ditampilkan di halaman profil.
                return redirect()->route('profile.edit')->with('swal_warning', 'Anda harus mengganti password default Anda terlebih dahulu!');
            }
        }

        // Jika kondisi di atas tidak terpenuhi, izinkan request untuk melanjutkan.
        return $next($request);
    }
}