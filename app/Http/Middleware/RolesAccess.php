<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RolesAccess
{
    /**
     * Permite acceso si el usuario tiene cualquiera de los roles pasados (coma-separados o como argumentos variádicos).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Nota: Se removieron logs de depuración para producción.
        
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No autenticado.'
                ], 401);
            }
            return redirect()->route('login');
        }

        $allowed = array_map('trim', $roles);
        $userRole = Auth::user()->rol ?? null;

        if ($userRole && in_array($userRole, $allowed)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'error' => 'Acceso denegado: No tienes permisos.'
            ], 403);
        }
        return redirect()->route('dashboard')->with('error', 'Acceso denegado: No tienes permisos.');
    }
}
