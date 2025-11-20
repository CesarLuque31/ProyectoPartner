<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- CONTENEDOR PRINCIPAL: Aplica a TODOS los usuarios --}}
            <div class="bg-white shadow-xl sm:rounded-lg border-2 border-azul-noche border-opacity-10" x-data="{ currentTab: 'profile' }">
                <div class="p-6 lg:p-8 bg-white border-b border-celeste flex">

                    {{-- 1. BARRA DE NAVEGACIÓN LATERAL (Menú) --}}
                    <div class="w-1/5 pr-6 border-r border-gray-200">
                        <h3 class="text-lg font-bold text-azul-noche mb-4">Menú {{ ucwords(Auth::user()->rol) }}</h3>
                        
                        {{-- BOTÓN 1 (POR DEFECTO): PERFIL (Para todos) --}}
                        <button 
                            @click="currentTab = 'profile'" 
                            :class="{ 'bg-azul-noche text-white': currentTab === 'profile', 'text-azul-noche hover:bg-celeste': currentTab !== 'profile' }" 
                            class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2"
                        >
                            <i class="fas fa-user-circle mr-2"></i> Perfil
                        </button>
                        
                        {{-- BOTONES ESPECÍFICOS DEL ROL --}}
                        @switch(Auth::user()->rol)
                            @case('jefe')
                                
                                <h4 class="text-sm font-bold text-gray-500 mt-2 mb-2">Administración General</h4>
                                
                                <button @click="currentTab = 'user_management'" :class="{ 'bg-azul-noche text-white': currentTab === 'user_management', 'text-azul-noche hover:bg-celeste': currentTab !== 'user_management' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2">
                                    <i class="fas fa-user-plus mr-2"></i> Crear Usuario
                                </button>
                                
                                {{-- MÓDULO DE GESTIÓN DE TALENTO (SUBMENÚ DESPLEGABLE) --}}
                                <div x-data="{ talentOpen: false }">
                                    <h4 
                                        @click="talentOpen = ! talentOpen" 
                                        class="flex justify-between items-center text-sm font-bold text-azul-noche hover:text-naranja cursor-pointer mt-4 mb-2 p-2 rounded-lg transition-colors duration-150"
                                    >
                                        <span class="flex items-center">
                                            <i class="fas fa-medal mr-2"></i> Gestión de Talento
                                        </span>
                                        <i class="fas fa-chevron-down text-xs transform transition-transform duration-300" :class="{ 'rotate-180': talentOpen }"></i>
                                    </h4>
                                    
                                    <div x-show="talentOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-y-0" x-transition:enter-end="opacity-100 transform scale-y-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-y-100" x-transition:leave-end="opacity-0 transform scale-y-0" class="origin-top pl-4 pt-1 pb-1 border-l border-celeste ml-2 space-y-1">
                                        
                                        <button @click="currentTab = 'talent_convocatorias'" :class="{ 'bg-azul-noche text-white': currentTab === 'talent_convocatorias', 'text-azul-noche hover:bg-celeste': currentTab !== 'talent_convocatorias' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold text-sm">
                                            <i class="fas fa-bullhorn mr-2"></i> Crear Convocatorias
                                        </button>

                                        <button @click="currentTab = 'talent_convocatorias_list'" :class="{ 'bg-azul-noche text-white': currentTab === 'talent_convocatorias_list', 'text-azul-noche hover:bg-celeste': currentTab !== 'talent_convocatorias_list' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold text-sm">
                                            <i class="fas fa-list mr-2"></i> Listado Convocatorias
                                        </button>
                                    </div>
                                </div>
                                <hr class="border-gray-200 mt-4">
                                @break
                            
                            @case('analista')
                                <button @click="currentTab = 'reports'" :class="{ 'bg-azul-noche text-white': currentTab === 'reports', 'text-azul-noche hover:bg-celeste': currentTab !== 'reports' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2">
                                    <i class="fas fa-chart-line mr-2"></i> Reportes
                                </button>
                                @break

                            @case('reclutador')
                                <button @click="currentTab = 'candidates'" :class="{ 'bg-azul-noche text-white': currentTab === 'candidates', 'text-azul-noche hover:bg-celeste': currentTab !== 'candidates' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2">
                                    <i class="fas fa-users mr-2"></i> Candidatos
                                </button>
                                @break
                            
                            @case('operador')
                                <button @click="currentTab = 'tasks'" :class="{ 'bg-azul-noche text-white': currentTab === 'tasks', 'text-azul-noche hover:bg-celeste': currentTab !== 'tasks' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2">
                                    <i class="fas fa-tasks mr-2"></i> Tareas
                                </button>
                                @break
                        @endswitch
                    </div>

                    {{-- 2. ÁREA DE CONTENIDO DINÁMICO --}}
                    <div class="w-4/5 pl-6">
                        @php
                            // Pre-cargar datos de convocatorias para las vistas que los necesiten
                            $TalentoControllerList = app(\App\Http\Controllers\TalentoController::class);
                            $data = $TalentoControllerList->getConvocatoriasData();
                            $convocatorias = $data['convocatorias'];
                            $campanias = $data['campanias'];
                            $cargos = $data['cargos'];
                            $horarios = $data['horarios'] ?? [];
                            $reclutadores_disponibles = $data['reclutadores_disponibles'] ?? [];
                        @endphp
                        {{-- VISTA 1 (POR DEFECTO): PERFIL (Para todos) --}}
                        <div x-show="currentTab === 'profile'" class="space-y-8">
                            
                            {{-- BLOQUE DE INFORMACIÓN DE SÓLO LECTURA --}}
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">Datos Personales</h2>
                            
                            {{-- TARJETA DE PERFIL --}}
                            <div class="bg-white p-8 shadow-xl rounded-lg text-center border border-gray-200">
                                <div class="flex flex-col items-center">
                                    {{-- FOTO --}}
                                    <div class="mb-4">
                                        @if(Auth::user()->foto)
                                            <img src="{{ asset('storage/' . Auth::user()->foto) }}" alt="Foto de Perfil" 
                                                 class="w-24 h-24 rounded-full object-cover border-4 border-naranja shadow-md mx-auto">
                                        @else
                                            <div class="w-24 h-24 rounded-full bg-celeste flex items-center justify-center text-3xl font-extrabold text-azul-noche mx-auto border-2 border-naranja shadow-md">
                                                {{ substr(Auth::user()->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- DATOS --}}
                                    <div class="space-y-2">
                                        <p class="text-3xl font-extrabold text-azul-noche">{{ Auth::user()->name }}</p>
                                        <p class="text-lg text-azul-noche flex items-center justify-center">
                                            <i class="fas fa-id-card mr-2 text-naranja"></i>
                                            DNI: <span class="ml-1 font-semibold">{{ Auth::user()->dni ?? 'N/A' }}</span>
                                        </p>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-user-tag mr-2 text-naranja"></i>
                                            <span class="px-3 py-1 text-sm font-bold rounded-full bg-verde text-white shadow-md">
                                                {{ ucwords(Auth::user()->rol) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="border-gray-200">

                            {{-- BLOQUE DE EDICIÓN DE CREDENCIALES --}}
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">Edición de Credenciales</h2>

                            {{-- 2. Edición de Correo --}}
                            <div class="bg-white p-6 shadow-md rounded-lg max-w-xl mx-auto border border-gray-100">
                                @include('profile.partials.update-profile-information-form', ['user' => Auth::user()])
                            </div>

                            {{-- 3. Edición de Contraseña --}}
                            <div class="bg-white p-6 shadow-md rounded-lg max-w-xl mx-auto border border-gray-100">
                                @include('profile.partials.update-password-form')
                            </div>

                            <hr class="border-gray-200">
                        </div>

                        {{-- VISTAS ESPECÍFICAS DEL ROL --}}
                        @switch(Auth::user()->rol)
                            @case('jefe')
                                <div x-show="currentTab === 'overview'">
                                    <h2 class="text-2xl font-semibold mb-4 text-azul-noche">Bienvenido, Jefe.</h2>
                                    <p class="text-azul-noche text-opacity-70">Contenido administrativo y KPIs.</p>
                                </div>
                                <div x-show="currentTab === 'user_management'">
                                    @include('admin.users.create')
                                </div>
                                <div x-show="currentTab === 'client_management'">
                                    <h2 class="text-2xl font-semibold mb-4 text-azul-noche">Administración de Clientes</h2>
                                    <p class="text-azul-noche text-opacity-70">Listado de clientes.</p>
                                </div>
                                
                                {{-- VISTA DE CONVOCATORIAS --}}
                                <div x-show="currentTab === 'talent_convocatorias'">
                                    @include('talent.convocatorias')
                                </div>

                                <div x-show="currentTab === 'talent_convocatorias_list'">
                                    @include('talent.convocatorias_list')
                                </div>
                                @break

                            @case('analista')
                                <div x-show="currentTab === 'reports'">
                                    <h2 class="text-2xl font-semibold mb-4 text-azul-noche">Panel de Reportes</h2>
                                    <p class="text-azul-noche text-opacity-70">Aquí se muestran los gráficos y datos de análisis.</p>
                                </div>
                                @break

                            @case('reclutador')
                                <div x-show="currentTab === 'candidates'">
                                    <h2 class="text-2xl font-semibold mb-4 text-azul-noche">Gestión de Candidatos</h2>
                                    <p class="text-azul-noche text-opacity-70">Listado y gestión de procesos de selección.</p>
                                </div>
                                @break
                            
                            @case('operador')
                                <div x-show="currentTab === 'tasks'">
                                    <h2 class="text-2xl font-semibold mb-4 text-azul-noche">Mis Tareas Pendientes</h2>
                                    <p class="text-azul-noche text-opacity-70">El operador solo ve sus tareas diarias.</p>
                                </div>
                                @break
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Consumir mensajes de sesión después de que profile los haya consumido --}}
    @php
        session()->forget('status');
        session()->forget('error');
    @endphp
</x-app-layout>