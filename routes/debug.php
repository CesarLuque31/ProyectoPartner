<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

Route::get('/debug/reclutadores', function() {
    try {
        // Mostrar todos los registros en pri.empleados con CampañaID = 33
        $todos = DB::table('pri.empleados')
            ->where('CampañaID', 33)
            ->get();
        
        echo "<h2>Todos los empleados con CampañaID = 33: " . count($todos) . "</h2>";
        echo "<pre>" . json_encode($todos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

        // Mostrar solo los activos
        $activos = DB::table('pri.empleados')
            ->where('CampañaID', 33)
            ->where('EstadoEmpleado', 'Activo')
            ->select('EmpleadoID', 'Nombres', 'ApellidoPaterno', 'ApellidoMaterno', 'EstadoEmpleado', 'CampañaID')
            ->get();
        
        echo "<h2>Empleados ACTIVOS con CampañaID = 33: " . count($activos) . "</h2>";
        echo "<pre>" . json_encode($activos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

        // Mostrar los valores únicos de EstadoEmpleado
        $estados = DB::table('pri.empleados')
            ->where('CampañaID', 33)
            ->distinct()
            ->pluck('EstadoEmpleado');
        
        echo "<h2>Estados únicos en pri.empleados (CampañaID = 33):</h2>";
        echo "<pre>" . json_encode($estados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

    } catch (\Exception $e) {
        echo "<h2>Error:</h2>";
        echo "<pre>" . $e->getMessage() . "</pre>";
    }
});

Route::get('/debug/current-user', function() {
    $user = Auth::user();
    if (!$user) {
        return response()->json(['error' => 'No autenticado'], 401);
    }
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'rol' => $user->rol,
        'created_at' => $user->created_at,
    ]);
});

