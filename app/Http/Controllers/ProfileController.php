<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash; 

class ProfileController extends Controller
{
    /**
     * [RUTA user.profile.update] Actualiza la información de perfil (nombre y correo).
     * Usa la clase ProfileUpdateRequest para la validación.
     */
    public function updateProfileInformation(ProfileUpdateRequest $request): RedirectResponse
    {
        // Usa el ProfileUpdateRequest que tú tienes para validar.
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('dashboard')->with('status', 'profile-updated'); 
    }
    
    /**
     * [RUTA user.password.update] Actualiza solo la contraseña del usuario.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        // 1. Validar los campos de contraseña, forzando la bolsa 'updatePassword'
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'string', 'current_password'], 
            'password' => ['required', 'string', Password::defaults(), 'confirmed'], 
        ]);
        
        // 2. ACTUALIZAR LA CONTRASEÑA EN LA BASE DE DATOS
        $request->user()->update([
            'password' => Hash::make($validated['password']),
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
