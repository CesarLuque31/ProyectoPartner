<?php

use App\Http\Controllers\ProfileController; 
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TalentoController;
use App\Http\Middleware\RolesAccess;
use Illuminate\Support\Facades\Route;

// DEBUG: Test API externa (solo en local)
if (env('APP_DEBUG')) {
    Route::get('/debug/test-api', function () {
        try {
            $resp = \Illuminate\Support\Facades\Http::post('http://10.182.18.70:8421/auth/login', [
                'usuario' => env('API_EXTERNAL_USER'),
                'password' => env('API_EXTERNAL_PASS'),
            ]);
            
            return [
                'auth_status' => $resp->status(),
                'auth_response' => $resp->json(),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    });
}

// 1. RUTA RAÍZ: Redirige automáticamente al Login 
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// 2. DASHBOARD: CORRECCIÓN CLAVE - PASAR EL USUARIO A LA VISTA
Route::get('/dashboard', function () {
    // Definimos la variable $user aquí
    $user = auth()->user(); 
    
    return view('dashboard', [
        'user' => $user // Pasamos la variable $user a la vista
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. RUTAS PROTEGIDAS POR AUTH
Route::middleware('auth')->group(function () {
    
    // PERFIL 
    Route::patch('/user/profile', [ProfileController::class, 'updateProfileInformation'])->name('user.profile.update');
    Route::patch('/user/password', [ProfileController::class, 'updatePassword'])->name('user.password.update');
    Route::delete('/user/destroy', [ProfileController::class, 'destroy'])->name('user.destroy');

    // RUTAS DE ADMINISTRACIÓN (Accedidas por el Jefe)
    Route::middleware([RolesAccess::class . ':jefe'])->group(function () {
        // Panel principal de administración
        Route::get('/admin', function () {
            $user = auth()->user();
            return view('admin.index', ['user' => $user]);
        })->name('admin.index');

        Route::get('/admin/users/create', function () {
            return redirect()->route('dashboard'); 
        })->name('admin.users.create'); 

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');

        // CONVOCATORIAS
        Route::post('/convocatorias', [TalentoController::class, 'storeConvocatoria'])
            ->name('convocatorias.store');

        // LISTAR CONVOCATORIAS (vista de listado)
        Route::get('/convocatorias/list', [TalentoController::class, 'listConvocatorias'])
            ->name('convocatorias.index');

        // ELIMINAR CONVOCATORIA (DELETE)
        Route::delete('/convocatorias/{id}', [TalentoController::class, 'destroyConvocatoria'])
            ->name('convocatorias.destroy');

        // ASIGNAR RECLUTADORES (POST)
        Route::post('/convocatorias/{id}/assign-reclutadores', [TalentoController::class, 'assignReclutadores'])
            ->name('convocatorias.assign-reclutadores');

        // FILTRAR CONVOCATORIAS (AJAX)
        Route::post('/convocatorias/filtrar', [TalentoController::class, 'filtrarConvocatorias'])
            ->name('convocatorias.filtrar');
    });

    // RUTA GET: Si alguien intenta ir a /convocatorias, lo enviamos al dashboard
    Route::get('/convocatorias', function () {
        return redirect()->route('dashboard');
    });

    // Rutas para insertar postulantes (solo jefe y reclutador)
    Route::middleware([RolesAccess::class . ':jefe,reclutador'])->group(function () {
        Route::get('/postulantes/insertar', [\App\Http\Controllers\PostulanteController::class, 'create'])
            ->name('postulantes.create');

        Route::post('/postulantes/check-dni', [\App\Http\Controllers\PostulanteController::class, 'checkDNI'])
            ->name('postulantes.checkDNI');

        Route::post('/postulantes/consulta', [\App\Http\Controllers\PostulanteController::class, 'consulta'])
            ->name('postulantes.consulta');

        Route::post('/postulantes', [\App\Http\Controllers\PostulanteController::class, 'store'])
            ->name('postulantes.store');
    });

    // Endpoint auxiliar para obtener horarios base (usado por la vista)
    Route::get('/api/horarios-base', [\App\Http\Controllers\TalentoController::class, 'getHorariosBase']);
    
    // Endpoint auxiliar para obtener tipos de contrato (usado por la vista)
    Route::get('/api/tipos-contrato', [\App\Http\Controllers\TalentoController::class, 'getTiposContrato']);
});

// DEBUG ROUTES (SOLO PARA DEVELOPMENT)
if (app()->environment('local')) {
    require __DIR__.'/debug.php';
}

// 4. INCLUIR RUTAS DE AUTH
require __DIR__.'/auth.php';