<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;       // NECESARIO para transacciones
use Illuminate\Support\Facades\Storage; // NECESARIO para archivos

class RegisteredUserController extends Controller
{
    /**
     * Muestra la vista de registro.
     * Redirige al login para evitar el registro público.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('login');
    }

    /**
     * Maneja la solicitud de registro (usada por el formulario del Jefe).
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. DEFINICIÓN DEL VALIDADOR (Añadimos DNI y FOTO)
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class], 
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            
            // CORRECCIÓN: Los valores 'in' deben coincidir con la base de datos (singular o plural)
            // Asumo que usas singular: 'operador', 'analista'
            'rol' => ['required', 'in:jefe,analista,operador,reclutador'], 
            
            // NUEVAS REGLAS:
            'dni' => ['required', 'string', 'max:10', 'unique:'.User::class.',dni'],
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // Máx 2MB
        ]);

        if ($validator->fails()) {
            Log::warning("Validation failed during user creation: " . json_encode($validator->errors()->all()));
            return back()->withInput()->withErrors($validator->errors());
        }

        // 2. PROCESAMIENTO Y CREACIÓN (Usando Transacción para manejo de archivos)
        $path = null;
        
        try {
            DB::beginTransaction();

            // 2.1. Procesar y guardar la foto en el sistema de archivos
            if ($request->hasFile('foto')) {
                // Guarda en storage/app/public/profile_photos y obtiene la ruta relativa
                $path = $request->file('foto')->store('profile_photos', 'public');
            }
            
            // 2.2. Crear el usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'rol' => $request->rol, 
                'dni' => $request->dni, // Guardar DNI
                'foto' => $path, // Guardar la ruta del archivo
            ]);
            
            event(new Registered($user));
            
            DB::commit(); // Confirmar la transacción
            
            // 3. REDIRECCIÓN DE ÉXITO
            return redirect()->route('dashboard')->with('status', 'Usuario ' . $user->name . ' creado exitosamente con el rol de ' . $user->rol . '.');

        } catch (\Exception $e) {
            // 4. CAPTURAMOS CUALQUIER ERROR (DB, Archivo, etc.)
            DB::rollBack(); // Si falla, revertir los cambios de la base de datos
            
            // Borrar el archivo si se subió antes de que fallara la DB
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            
            Log::error("Error al crear usuario: " . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Ocurrió un error al guardar el usuario: ' . $e->getMessage());
        }
    }
}