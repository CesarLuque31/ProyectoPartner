<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JefeAccess
{
    public function handle(Request $request, Closure $next, string $role): Response // <-- AÑADE STRING $ROLE
    {
        // 1. Verifica si el usuario está logueado y si su rol coincide con el parámetro $role
        if (auth()->check() && auth()->user()->rol === $role) {
            return $next($request);
        }

        // Si no cumple el rol requerido, redirigir.
        return redirect()->route('dashboard')->with('error', 'Acceso denegado: No tienes permisos de ' . $role . '.');
    }
}