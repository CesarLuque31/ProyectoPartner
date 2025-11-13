<?php

use App\Http\Controllers\ProfileController; 
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TalentoController;
use Illuminate\Support\Facades\Route;

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
    
    // PERFIL (COMENTADO para eliminar la URL /profile)
    /*
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    */

    // NUEVAS RUTAS FUNCIONALES SEPARADAS Y CORRECTAMENTE NOMBRADAS
    Route::patch('/user/profile', [ProfileController::class, 'updateProfileInformation'])->name('user.profile.update');
    Route::patch('/user/password', [ProfileController::class, 'updatePassword'])->name('user.password.update');
    Route::delete('/user/destroy', [ProfileController::class, 'destroy'])->name('user.destroy');


    // RUTAS DE ADMINISTRACIÓN (Accedidas por el Jefe)
    Route::middleware('jefe')->group(function () {
        // Panel principal de administración
        Route::get('/admin', function () {
            // El admin index también necesita el usuario si usa el layout principal
            $user = auth()->user();
            return view('admin.index', ['user' => $user]);
        })->name('admin.index');

        Route::get('/admin/users/create', function () {
            return redirect()->route('dashboard'); 
        })->name('admin.users.create'); 

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');

        // CONVOCATORIAS
Route::post('/convocatorias', [App\Http\Controllers\TalentoController::class, 'storeConvocatoria'])
    ->name('convocatorias.store');
    });
});


// 4. INCLUIR RUTAS DE AUTH
require __DIR__.'/auth.php';
