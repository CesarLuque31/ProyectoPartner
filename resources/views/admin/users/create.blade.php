<div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
    <h2 class="text-2xl font-semibold mb-4 text-indigo-600">Crear Nuevo Usuario</h2>
    <p class="text-gray-600 mb-6">Completa el formulario para dar de alta a un nuevo Jefe, Analista, Operador o Reclutador en el sistema.</p>

    {{-- Mostrar mensaje de éxito si existe (se usa después de crear exitosamente) --}}
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
            {{ session('status') }}
        </div>
    @endif

    {{-- 
        ACCIÓN CRÍTICA:
        1. Se mantiene la acción 'user.store'.
        2. Se añade enctype="multipart/form-data" para permitir la subida de archivos.
    --}}
    <form method="POST" action="{{ route('user.store') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf

        {{-- GRID PARA MEJOR DISPOSICIÓN DE CAMPOS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <x-input-label for="name" :value="__('Nombre')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="dni" :value="__('DNI')" />
                {{-- Usamos tipo 'text' y validación para asegurar solo números y 10 dígitos --}}
                <x-text-input id="dni" class="block mt-1 w-full" type="text" name="dni" :value="old('dni')" required />
                <x-input-error :messages="$errors->get('dni')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="rol" :value="__('Rol del Usuario')" />
                <select id="rol" name="rol" required class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="" disabled selected>Selecciona un Rol</option>
                    <option value="operador" {{ old('rol') == 'operador' ? 'selected' : '' }}>Operador</option>
                    <option value="analista" {{ old('rol') == 'analista' ? 'selected' : '' }}>Analista</option>
                    <option value="reclutador" {{ old('rol') == 'reclutador' ? 'selected' : '' }}>Reclutador</option>
                    <option value="jefe" {{ old('rol') == 'jefe' ? 'selected' : '' }}>Jefe</option>
                </select>
                <x-input-error :messages="$errors->get('rol')" class="mt-2" />
            </div>

        </div>
        
        {{-- Campo de subida de FOTO (Ocupa ancho completo) --}}
        <div>
            <x-input-label for="foto" :value="__('Foto de Perfil (JPG/PNG)')" />
            <input id="foto" name="foto" type="file" required 
                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mt-1 p-2"
                accept=".jpg, .jpeg, .png" 
            />
            <x-input-error :messages="$errors->get('foto')" class="mt-2" />
            <p class="mt-1 text-sm text-gray-500">Dimensiones sugeridas: 500x500px. Máx. 2MB.</p>
        </div>


        {{-- Contraseñas (Fuera del grid) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200">
            <div>
                <x-input-label for="password" :value="__('Contraseña')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>


        <div class="flex items-center justify-start pt-4">
            <x-primary-button>
                {{ __('Crear Usuario') }}
            </x-primary-button>
        </div>
    </form>
</div>