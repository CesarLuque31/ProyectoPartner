<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! auth()->check() || auth()->user()->rol !== $role) {
            // Si el rol no coincide, redirigir o abortar
            return abort(403, 'Acceso Denegado. Se requiere el rol: ' . $role);
        }
        return $next($request);
    }
}