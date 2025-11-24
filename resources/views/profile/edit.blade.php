<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <i class="fas fa-user-circle mr-3 text-naranja text-2xl"></i>
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Mi Perfil') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-celeste min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- TARJETA DE INFORMACIÓN PERSONAL --}}
            <div class="bg-white shadow-xl rounded-xl border-2 border-azul-noche border-opacity-20 overflow-hidden">
                <div class="bg-gradient-to-r from-azul-noche to-azul-noche px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-id-card mr-2"></i>
                        Información Personal
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-col md:flex-row items-center md:items-start gap-6 mb-6 pb-6 border-b border-celeste">
                        {{-- FOTO DE PERFIL --}}
                        <div class="flex-shrink-0">
                            @if(Auth::user()->foto)
                                <img src="{{ asset('storage/' . Auth::user()->foto) }}" alt="Foto de Perfil" 
                                     class="w-32 h-32 rounded-full object-cover border-4 border-naranja shadow-lg">
                            @else
                                <div class="w-32 h-32 rounded-full bg-celeste flex items-center justify-center text-4xl font-extrabold text-azul-noche border-4 border-naranja shadow-lg">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        {{-- DATOS BÁSICOS --}}
                        <div class="flex-1 text-center md:text-left">
                            <h4 class="text-2xl font-bold text-azul-noche mb-2">{{ Auth::user()->name }}</h4>
                            <div class="space-y-2">
                                <p class="text-azul-noche flex items-center justify-center md:justify-start">
                                    <i class="fas fa-envelope mr-2 text-naranja"></i>
                                    <span>{{ Auth::user()->email }}</span>
                                </p>
                                @if(Auth::user()->dni)
                                <p class="text-azul-noche flex items-center justify-center md:justify-start">
                                    <i class="fas fa-id-card mr-2 text-naranja"></i>
                                    <span>DNI: <strong>{{ Auth::user()->dni }}</strong></span>
                                </p>
                                @endif
                                <div class="flex items-center justify-center md:justify-start">
                                    <i class="fas fa-user-tag mr-2 text-naranja"></i>
                                    <span class="px-4 py-1 text-sm font-bold rounded-full bg-verde text-white shadow-md">
                                        {{ ucwords(Auth::user()->rol) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="max-w-2xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            {{-- TARJETA DE SEGURIDAD --}}
            <div class="bg-white shadow-xl rounded-xl border-2 border-azul-noche border-opacity-20 overflow-hidden">
                <div class="bg-gradient-to-r from-azul-noche to-azul-noche px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Seguridad y Contraseña
                    </h3>
                </div>
                <div class="p-6">
                    <div class="max-w-2xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
