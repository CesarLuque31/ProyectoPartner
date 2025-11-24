<nav x-data="{ open: false }" class="bg-gradient-to-r from-celeste via-celeste to-celeste shadow-xl rounded-2xl mx-2 mt-2 sticky top-0 z-50 transition duration-300 border-2 border-azul-noche border-opacity-10">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <div class="flex items-center">
                <!-- Logo mejorado -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="bg-azul-noche bg-opacity-10 p-2 rounded-xl group-hover:bg-opacity-20 transition-all duration-300">
                            <svg class="h-7 w-7 text-azul-noche" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6m-6 0h-2a1 1 0 00-1 1v2m8-2v2a1 1 0 001 1h2a1 1 0 001-1v-2" />
                        </svg>
                        </div>
                        <span class="text-xl font-bold text-azul-noche hidden md:block">Partner</span>
                    </a>
                </div>

                <!-- Navigation Links mejorados -->
                <div class="hidden space-x-3 sm:-my-px sm:ms-8 sm:flex items-center">
                    
                    <!-- Enlace a Panel Principal mejorado -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                                class="relative px-5 py-2.5 text-sm font-semibold rounded-xl transition-all duration-300
                                {{ request()->routeIs('dashboard') 
                                    ? 'bg-azul-noche text-white shadow-lg transform scale-105' 
                                    : 'text-azul-noche hover:bg-azul-noche hover:bg-opacity-10 hover:shadow-md' }}">
                        <i class="fas fa-home mr-2"></i>
                        {{ __('Panel Principal') }}
                    </x-nav-link>
                    
                </div>
            </div>

            <!-- Settings Dropdown mejorado -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center space-x-3 bg-white rounded-xl px-4 py-2.5 shadow-md hover:shadow-lg transition-all duration-300 border-2 border-azul-noche border-opacity-10 hover:border-opacity-30 group">
                            
                            <!-- Foto de Perfil mejorada -->
                            <div class="relative">
                                @if (Auth::user()->profile_photo_url)
                                    <img src="{{ Auth::user()->profile_photo_url }}" 
                                         alt="{{ Auth::user()->name }}" 
                                         class="h-10 w-10 rounded-full object-cover ring-2 ring-azul-noche ring-opacity-20 group-hover:ring-opacity-40 transition-all">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-naranja to-naranja flex items-center justify-center text-white font-bold text-sm shadow-md ring-2 ring-azul-noche ring-opacity-20 group-hover:ring-opacity-40 transition-all">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>

                            <!-- Nombre del Usuario -->
                            <div class="hidden lg:block text-left">
                                <div class="font-bold text-azul-noche text-sm">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-azul-noche text-opacity-60">{{ ucwords(Auth::user()->rol) }}</div>
                            </div>

                            <!-- Icono de flecha del dropdown -->
                            <div class="text-azul-noche text-opacity-60 group-hover:text-opacity-100 transition-all">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
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

            <!-- Hamburger (Mobile) mejorado -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2.5 rounded-xl text-azul-noche hover:bg-azul-noche hover:bg-opacity-10 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu mejorado -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t-2 border-azul-noche border-opacity-20 shadow-lg">
        <div class="pt-3 pb-3 space-y-2 px-4">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" 
                class="text-azul-noche hover:bg-celeste rounded-xl px-4 py-3 font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-celeste' : '' }}">
                <i class="fas fa-home mr-2"></i>
                {{ __('Panel Principal') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options mejorado -->
        <div class="pt-4 pb-3 border-t-2 border-celeste">
            <div class="flex items-center px-4 mb-4">
                {{-- Foto de Perfil Responsive --}}
                <div class="shrink-0 me-3">
                    @if (Auth::user()->profile_photo_url)
                        <img src="{{ Auth::user()->profile_photo_url }}" 
                             alt="{{ Auth::user()->name }}" 
                             class="h-12 w-12 rounded-full object-cover ring-2 ring-azul-noche ring-opacity-20">
                    @else
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-naranja to-naranja flex items-center justify-center text-white font-bold text-lg shadow-md ring-2 ring-azul-noche ring-opacity-20">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <div>
                    <div class="font-bold text-base text-azul-noche">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-azul-noche text-opacity-60">{{ Auth::user()->email }}</div>
                    <div class="mt-1">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-verde bg-opacity-20 text-verde">
                            {{ ucwords(Auth::user()->rol) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-2 px-4">
                <x-responsive-nav-link :href="route('dashboard')" 
                    class="text-azul-noche hover:bg-celeste rounded-xl px-4 py-2.5 font-semibold transition-all">
                    <i class="fas fa-home mr-2"></i>
                    {{ __('Panel Principal') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                     this.closest('form').submit();" 
                            class="text-azul-noche hover:bg-celeste rounded-xl px-4 py-2.5 font-semibold transition-all">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>