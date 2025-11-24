<section>
    <header class="mb-6">
        <h2 class="text-xl font-bold text-azul-noche flex items-center mb-2">
            <i class="fas fa-envelope mr-2 text-naranja"></i>
            {{ __('Información de Contacto') }}
        </h2>
        <p class="text-sm text-azul-noche text-opacity-70">
            {{ __('Actualiza la dirección de correo electrónico asociada a tu cuenta.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <!-- ¡CORRECCIÓN CLAVE! Cambiado a la ruta correcta: user.profile.update -->
    <form method="post" action="{{ route('user.profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <!-- CAMPO EMAIL -->
        <div class="bg-celeste bg-opacity-30 p-4 rounded-lg border border-azul-noche border-opacity-20">
            <x-input-label for="profile_email" :value="__('Correo Electrónico')" class="text-azul-noche font-semibold mb-2" />
            
            <div class="relative mt-1">
                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                
                <x-text-input 
                    id="profile_email" 
                    name="email" 
                    type="email" 
                    class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" 
                    :value="old('email', $user->email)" 
                    required="required" 
                    autocomplete="username" 
                />
            </div>
            
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                {{-- ... (Resto de la lógica de verificación de email) ... --}}
            @endif
        </div>

        <div class="flex items-center gap-4 pt-4">
            <button type="submit" class="bg-gradient-to-r from-verde to-verde hover:from-verde hover:to-verde hover:bg-opacity-90 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center">
                <i class="fas fa-save mr-2"></i>
                {{ __('Guardar Cambios') }}
            </button>
        </div>
    </form>
</section>

@push('scripts')
<script>
    @if (session('status') === 'profile-updated')
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: '¡Perfil Actualizado!',
                text: 'Tu información personal ha sido guardada con éxito.',
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end'
            });
        });
    @endif
</script>
@endpush