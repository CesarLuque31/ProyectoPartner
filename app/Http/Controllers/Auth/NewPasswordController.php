<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash; // AÑADIR: Necesario para verificar contraseñas

class ProfileController extends Controller
{
    // ... (updateProfileInformation queda igual) ...
    
    /**
     * [RUTA user.password.update] Actualiza solo la contraseña del usuario.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        // 1. Validar los campos de contraseña
        $validated = $request->validateWithBag('updatePassword', [
            // Corregimos la validación para usar 'current_password' y 'confirmed'
            'current_password' => ['required', 'string', 'current_password'], 
            'password' => ['required', 'string', Password::defaults(), 'confirmed'], 
        ]);
        
        // El ProfileUpdateRequest ya hace la validación, pero si usamos Request $request, la hacemos aquí.
        
        // 2. ACTUALIZAR LA CONTRASEÑA EN LA BASE DE DATOS
        $request->user()->update([
            'password' => Hash::make($validated['password']), // Usamos Hash::make para mayor claridad y seguridad
        ]);

        // 3. Redirigir y mostrar mensaje de éxito
        return Redirect::route('dashboard')->with('status', 'password-updated');
    }

    /**
     * [RUTA user.destroy] Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}