<section>
    <header class="mb-6">
        <h2 class="text-xl font-bold text-azul-noche flex items-center mb-2">
            <i class="fas fa-key mr-2 text-naranja"></i>
            {{ __('Actualizar Contraseña') }}
        </h2>
        <p class="text-sm text-azul-noche text-opacity-70">
            {{ __('Asegúrate de que tu cuenta utiliza una contraseña larga y aleatoria para mantener la seguridad.') }}
        </p>
    </header>

    <form method="post" action="{{ route('user.password.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <!-- BLOQUE DE DIAGNÓSTICO: Muestra errores de validación generales -->
        @if ($errors->updatePassword->any())
            <div class="font-medium text-red-600 border-2 border-red-500 bg-red-100 p-4 rounded-lg flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                {{ __('¡Error al intentar guardar la contraseña! Por favor, revisa los campos con errores.') }}
            </div>
        @endif

        {{-- CAMPO 1: Contraseña Actual --}}
        <div class="bg-celeste bg-opacity-30 p-4 rounded-lg border border-azul-noche border-opacity-20">
            <x-input-label for="current_password" :value="__('Contraseña Actual')" class="text-azul-noche font-semibold mb-2" />
            <div class="relative mt-1">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                <x-text-input 
                    id="current_password" 
                    name="current_password" 
                    type="password" 
                    class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" 
                    autocomplete="current-password" 
                    required="required" 
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        {{-- CAMPO 2: Nueva Contraseña (Usando ID único) --}}
        <div class="bg-celeste bg-opacity-30 p-4 rounded-lg border border-azul-noche border-opacity-20">
            <x-input-label for="new_password" :value="__('Nueva Contraseña')" class="text-azul-noche font-semibold mb-2" />
            <div class="relative mt-1">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                <x-text-input 
                    id="new_password" 
                    name="password" 
                    type="password" 
                    class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" 
                    autocomplete="new-password" 
                    required="required" 
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        {{-- CAMPO 3: Confirmar Contraseña (Usando ID único) --}}
        <div class="bg-celeste bg-opacity-30 p-4 rounded-lg border border-azul-noche border-opacity-20">
            <x-input-label for="new_password_confirmation" :value="__('Confirmar Contraseña')" class="text-azul-noche font-semibold mb-2" />
            <div class="relative mt-1">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                <x-text-input 
                    id="new_password_confirmation" 
                    name="password_confirmation" 
                    type="password" 
                    class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" 
                    autocomplete="new-password" 
                    required="required" 
                />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-4">
            <button type="submit" class="bg-gradient-to-r from-verde to-verde hover:from-verde hover:to-verde hover:bg-opacity-90 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center">
                <i class="fas fa-key mr-2"></i>
                {{ __('Guardar Contraseña') }}
            </button>
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