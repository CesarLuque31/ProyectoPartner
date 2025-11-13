<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JefeAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verifica si el usuario está logueado y si su rol en la tabla raz_users es 'jefe'
        // Si no está logueado, auth()->user() será nulo, y fallará el acceso.
        if (auth()->check() && auth()->user()->rol === 'jefe') {
            // Si el rol es 'jefe', permite que la solicitud continúe
            return $next($request);
        }

        // Si no es 'jefe', lo redirigimos al dashboard (o puedes usar abort(403))
        return redirect()->route('dashboard')->with('error', 'Acceso denegado: No tienes permisos de Jefe.');
    }
}