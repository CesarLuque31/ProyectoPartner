@if(auth()->check() && auth()->user()->rol === 'jefe')
    <div class="bg-white shadow-xl rounded-xl border-2 border-azul-noche border-opacity-20 overflow-hidden">
        <div class="bg-gradient-to-r from-azul-noche to-azul-noche px-6 py-4">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-user-plus mr-2"></i>
                Crear Nuevo Usuario
            </h2>
        </div>
        <div class="p-6">
            <p class="text-azul-noche text-opacity-70 mb-6 flex items-center">
                <i class="fas fa-info-circle mr-2 text-naranja"></i>
                Completa el formulario para dar de alta a un nuevo Jefe, Analista, Operador o Reclutador en el sistema.
            </p>

            {{-- 
                ACCIÓN CRÍTICA:
                1. Se mantiene la acción 'user.store'.
                2. Se añade enctype="multipart/form-data" para permitir la subida de archivos.
            --}}
            <form method="POST" action="{{ route('user.store') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf

                {{-- SECCIÓN: INFORMACIÓN PERSONAL --}}
                <div class="bg-celeste bg-opacity-30 p-6 rounded-lg border border-azul-noche border-opacity-20">
                    <h3 class="text-lg font-bold text-azul-noche mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-naranja"></i>
                        Información Personal
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="name" :value="__('Nombre Completo')" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <x-text-input id="name" class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                            </div>
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Correo Electrónico')" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <x-text-input id="email" class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" type="email" name="email" :value="old('email')" required autocomplete="username" />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="dni" :value="__('DNI')" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-id-card absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <x-text-input id="dni" class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all font-mono" type="text" name="dni" :value="old('dni')" required maxlength="8" />
                            </div>
                            <x-input-error :messages="$errors->get('dni')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="rol" :value="__('Rol del Usuario')" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-user-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <select id="rol" name="rol" required class="block w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all appearance-none bg-white">
                                    <option value="" disabled selected>Selecciona un Rol</option>
                                    <option value="operador" {{ old('rol') == 'operador' ? 'selected' : '' }}>Operador</option>
                                    <option value="analista" {{ old('rol') == 'analista' ? 'selected' : '' }}>Analista</option>
                                    <option value="reclutador" {{ old('rol') == 'reclutador' ? 'selected' : '' }}>Reclutador</option>
                                    <option value="jefe" {{ old('rol') == 'jefe' ? 'selected' : '' }}>Jefe</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-azul-noche pointer-events-none"></i>
                            </div>
                            <x-input-error :messages="$errors->get('rol')" class="mt-2" />
                        </div>
                    </div>
                </div>
                
                {{-- SECCIÓN: FOTO DE PERFIL --}}
                <div class="bg-celeste bg-opacity-30 p-6 rounded-lg border border-azul-noche border-opacity-20">
                    <h3 class="text-lg font-bold text-azul-noche mb-4 flex items-center">
                        <i class="fas fa-image mr-2 text-naranja"></i>
                        Foto de Perfil
                    </h3>
                    <div>
                        <x-input-label for="foto" :value="__('Subir Foto (JPG/PNG)')" class="text-azul-noche font-semibold mb-2" />
                        <div class="relative">
                            <input id="foto" name="foto" type="file" required 
                                class="block w-full text-sm text-azul-noche border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg cursor-pointer bg-white focus:outline-none p-3 transition-all"
                                accept=".jpg, .jpeg, .png" 
                            />
                        </div>
                        <x-input-error :messages="$errors->get('foto')" class="mt-2" />
                        <p class="mt-2 text-sm text-azul-noche text-opacity-70 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-naranja"></i>
                            Dimensiones sugeridas: 500x500px. Máx. 2MB.
                        </p>
                    </div>
                </div>

                {{-- SECCIÓN: CONTRASEÑA --}}
                <div class="bg-celeste bg-opacity-30 p-6 rounded-lg border border-azul-noche border-opacity-20">
                    <h3 class="text-lg font-bold text-azul-noche mb-4 flex items-center">
                        <i class="fas fa-lock mr-2 text-naranja"></i>
                        Credenciales de Acceso
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="password" :value="__('Contraseña')" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <x-text-input id="password" class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" type="password" name="password" required autocomplete="new-password" />
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <x-text-input id="password_confirmation" class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" type="password" name="password_confirmation" required autocomplete="new-password" />
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-start pt-4 border-t border-azul-noche border-opacity-20">
                    <button type="submit" class="bg-gradient-to-r from-verde to-verde hover:from-verde hover:to-verde hover:bg-opacity-90 text-white px-8 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>
                        {{ __('Crear Usuario') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        @if (session('status') && strpos(session('status'), 'creado exitosamente') !== false)
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Usuario Creado!',
                    text: "{{ session('status') }}",
                    showConfirmButton: false,
                    timer: 3000,
                    toast: true,
                    position: 'top-end'
                });
            });
        @endif
    </script>
    @endpush
@endif