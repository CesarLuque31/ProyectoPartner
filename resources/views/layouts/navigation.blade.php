<nav x-data="{ open: false }" class="bg-white shadow-lg rounded-xl mx-2 mt-2 sticky top-0 z-50 transition duration-300">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <div class="flex items-center">
                <!-- Logo: Quitamos el título principal para hacerla más limpia -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-2xl font-extrabold text-indigo-700 hover:text-indigo-500 transition duration-150">
                        {{-- DEJAMOS ESTE ESPACIO PARA UN ICONO LIGERO O NOMBRE CORTO DE LA APP --}}
                        <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6m-6 0h-2a1 1 0 00-1 1v2m8-2v2a1 1 0 001 1h2a1 1 0 001-1v-2" />
                        </svg>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 sm:-my-px sm:ms-6 sm:flex items-center">
                    
                    <!-- Enlace a Panel Principal (estilizado como un badge/chip) -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                                class="text-indigo-700 hover:bg-indigo-100 transition duration-150 rounded-full py-2.5 px-4 text-sm font-semibold 
                                {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-800 shadow-md' : 'font-medium' }}">
                        {{ __('Panel Principal') }}
                    </x-nav-link>
                    
                    {{-- Puedes añadir más enlaces aquí con el mismo estilo rounded-full --}}
                    
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-sm font-medium text-gray-700 bg-gray-100 rounded-full p-1.5 hover:bg-gray-200 transition ease-in-out duration-150 shadow-md">
                            
                            <!-- Foto de Perfil (más limpia) -->
                            <div class="me-2">
                                {{-- Si usas Laravel Jetstream o tienes la URL de la foto --}}
                                @if (Auth::user()->profile_photo_url)
                                    <img src="{{ Auth::user()->profile_photo_url }}" 
                                         alt="{{ Auth::user()->name }}" 
                                         class="h-8 w-8 rounded-full object-cover">
                                @else
                                    {{-- Placeholder si no hay foto --}}
                                    <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-sm">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <!-- Nombre del Usuario -->
                            <div class="pe-2 hidden md:block font-semibold">
                                {{ Auth::user()->name }}
                            </div>

                            <!-- Icono de flecha del dropdown -->
                            <div class="me-1">
                                <svg class="fill-current h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- El contenido del dropdown --}}
                        <x-dropdown-link :href="route('dashboard')">
                            {{ __('Panel Principal') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                             this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-gray-50 border-t border-gray-200">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-indigo-700 hover:bg-indigo-100">
                {{ __('Panel Principal') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                {{-- Foto de Perfil Responsive --}}
                <div class="shrink-0 me-3">
                    @if (Auth::user()->profile_photo_url)
                        <img src="{{ Auth::user()->profile_photo_url }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="h-10 w-10 rounded-full object-cover">
                    @else
                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-xl">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- CORRECCIÓN FINAL: Cambiamos 'profile.edit' por 'dashboard' -->
                <x-responsive-nav-link :href="route('dashboard')" class="text-gray-700 hover:bg-indigo-100">
                    {{ __('Panel Principal') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                     this.closest('form').submit();" class="text-gray-700 hover:bg-indigo-100">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>