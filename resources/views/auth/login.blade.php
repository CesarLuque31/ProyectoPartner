<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" id="login-form">
        @csrf

        <div class="mb-10 w-full flex justify-center">
            <h2 class="text-4xl font-bold text-gray-700">
                BIENVENIDO
            </h2>
        </div>

        <div class="mb-12">
            {{-- Aquí usas __('Correo') que se traduce correctamente --}}
            <x-input-label for="email" :value="__('Correo')" class="neumo-label" />
            <div class="relative">
                <i class="fas fa-envelope icon-input"></i>
                <x-text-input id="email" class="block mt-2 w-full neumo-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-20"> 
            {{-- Aquí usas __('Contraseña') que se traduce correctamente --}}
            <x-input-label for="password" :value="__('Contraseña')" class="neumo-label" />
            <div class="relative">
                <i class="fas fa-lock icon-input"></i>
                <x-text-input id="password" class="block mt-2 w-full neumo-input pr-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />

                <button type="button" id="togglePassword" class="password-toggle-button neumo-toggle-button">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-6 mb-12">
            <label for="remember_me" class="inline-flex items-center text-gray-600 text-sm">
                <input id="remember_me" type="checkbox" class="neumo-checkbox" name="remember">
                {{-- AJUSTE CLAVE: Usar la clave estándar 'Remember me' --}}
                <span class="ms-2">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-center mt-8"> 
            {{-- AJUSTE CLAVE: Usar la clave estándar 'Log in' (si quieres usar la traducción estándar de Breeze/Auth.php) --}}
            <button type="submit" class="neumo-button">
                {{ __('Ingresar') }} 
                {{-- Si el texto 'Ingresar' ya está hardcodeado en español, déjalo así. Si quieres usar la traducción de auth.php, usa: {{ __('Log in') }} y asegúrate de que 'Log in' => 'Ingresar' esté en resources/lang/es/auth.php --}}
            </button>
        </div>
    </form>
    
    {{-- Scripts de JavaScript --}}
    <script>
    // ... (Tu código JavaScript aquí)
    </script>
</x-guest-layout>