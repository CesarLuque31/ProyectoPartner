<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Actualizar Contraseña') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Asegúrate de que tu cuenta utiliza una contraseña larga y aleatoria para mantener la seguridad.') }}
        </p>
    </header>

    <form method="post" action="{{ route('user.password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- BLOQUE DE DIAGNÓSTICO: Muestra errores de validación generales -->
        @if ($errors->updatePassword->any())
            <div class="font-medium text-red-600 border border-red-500 bg-red-100 p-3 rounded-lg">
                {{ __('¡Error al intentar guardar la contraseña! Por favor, revisa los campos con errores.') }}
            </div>
        @endif

        {{-- CAMPO 1: Contraseña Actual --}}
        <div>
            <x-input-label for="current_password" :value="__('Contraseña Actual')" />
            <div class="relative mt-1">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <x-text-input 
                    id="current_password" 
                    name="current_password" 
                    type="password" 
                    class="block w-full pl-10" 
                    autocomplete="current-password" 
                    required="required" 
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        {{-- CAMPO 2: Nueva Contraseña (Usando ID único) --}}
        <div>
            <x-input-label for="new_password" :value="__('Nueva Contraseña')" />
            <div class="relative mt-1">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <x-text-input 
                    id="new_password" 
                    name="password" 
                    type="password" 
                    class="block w-full pl-10" 
                    autocomplete="new-password" 
                    required="required" 
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        {{-- CAMPO 3: Confirmar Contraseña (Usando ID único) --}}
        <div>
            <x-input-label for="new_password_confirmation" :value="__('Confirmar Contraseña')" />
            <div class="relative mt-1">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <x-text-input 
                    id="new_password_confirmation" 
                    name="password_confirmation" 
                    type="password" 
                    class="block w-full pl-10" 
                    autocomplete="new-password" 
                    required="required" 
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar Contraseña') }}</x-primary-button>
        </div>
    </form>
</section>

@push('scripts')
<script>
    @if (session('status') === 'password-updated')
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: '¡Contraseña Cambiada!',
                text: 'Tu contraseña ha sido modificada correctamente.',
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end'
            });
        });
    @endif
</script>
@endpush