<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Información de Contacto') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Actualiza la dirección de correo electrónico asociada a tu cuenta.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <!-- ¡CORRECCIÓN CLAVE! Cambiado a la ruta correcta: user.profile.update -->
    <form method="post" action="{{ route('user.profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- CAMPO NOMBRE (Si está en uso) -->
        <!-- <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" required="required" /> 
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div> -->

        <!-- CAMPO EMAIL -->
        <div>
            <x-input-label for="profile_email" :value="__('Correo Electrónico')" />
            
            <div class="relative mt-1">
                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                
                <x-text-input 
                    id="profile_email" 
                    name="email" 
                    type="email" 
                    class="block w-full pl-10" 
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

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar Cambios') }}</x-primary-button>
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