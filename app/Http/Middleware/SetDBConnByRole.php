<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetDBConnByRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()){
            $user = Auth::user();
            $connection = match ($user->role) {
                'admin' => 'mariadb',
                'user' => 'mariadb_mobile'
            };

            config(['database.default' => $connection]);
            $user->setConnection($connection);
        }
        return $next($request);
    }
}
