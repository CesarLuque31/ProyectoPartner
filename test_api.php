<?php
require 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Test directo a la API
echo "=== TEST API EXTERNA ===\n\n";

// 1. Test de autenticación
echo "1. Probando /auth/login...\n";
try {
    $resp = Http::post('http://10.182.18.70:8421/auth/login', [
        'usuario' => 'razzluk',
        'password' => 'CesarPartner*',
    ]);
    
    echo "Status: " . $resp->status() . "\n";
    echo "Body: " . $resp->body() . "\n\n";
    
    if ($resp->successful()) {
        $data = $resp->json();
        $token = $data['token'] ?? null;
        
        if ($token) {
            echo "✓ Token obtenido: " . substr($token, 0, 30) . "...\n\n";
            
            // 2. Test de consulta con token
            echo "2. Probando /consulta con token...\n";
            $consulta = Http::withToken($token)->get('http://10.182.18.70:8421/consulta', [
                'dni' => '73076473'
            ]);
            
            echo "Status: " . $consulta->status() . "\n";
            echo "Body: " . $consulta->body() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
