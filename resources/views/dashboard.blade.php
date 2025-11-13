<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- BLOQUE DE NOTIFICACIONES (SWEETALERT2) --}}
            @if (session('status') || session('error'))
                <script>
                    let statusKey = "{{ session('status') ?? '' }}";
                    let errorMsg = "{{ session('error') ?? '' }}";
                    let titleText = "";
                    let messageText = "";
                    let iconType = "success";

                    if (errorMsg) {
                        iconType = 'error';
                        titleText = '¡Error!';
                        messageText = errorMsg;
                    } else if (statusKey === 'profile-updated') {
                        titleText = "¡Perfil Actualizado!";
                        messageText = "Tu información personal ha sido guardada con éxito.";
                    } else if (statusKey === 'password-updated') {
                        titleText = "¡Contraseña Cambiada!";
                        messageText = "Tu contraseña ha sido modificada correctamente.";
                    } else {
                        titleText = "Éxito";
                        messageText = statusKey || "Operación completada.";
                    }

                    if (titleText) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: iconType,
                            title: titleText,
                            text: messageText,
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true,
                        });
                    }
                </script>
            @endif

            {{-- CONTENEDOR PRINCIPAL: Aplica a TODOS los usuarios --}}
            <div class="bg-white shadow-xl sm:rounded-lg" x-data="{ currentTab: 'profile' }">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200 flex">

                    {{-- 1. BARRA DE NAVEGACIÓN LATERAL (Menú) --}}
                    <div class="w-1/4 pr-6 border-r border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Menú {{ ucwords(Auth::user()->rol) }}</h3>
                        
                        {{-- BOTÓN 1 (POR DEFECTO): PERFIL (Para todos) --}}
                        <button 
                            @click="currentTab = 'profile'" 
                            :class="{ 'bg-indigo-500 text-white': currentTab === 'profile', 'text-indigo-700 hover:bg-indigo-50': currentTab !== 'profile' }" 
                            class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2"
                        >
                            <i class="fas fa-user-circle mr-2"></i> Perfil
                        </button>
                        
                        {{-- BOTONES ESPECÍFICOS DEL ROL --}}
                        @switch(Auth::user()->rol)
                            @case('jefe')
                                
                                <h4 class="text-sm font-bold text-gray-500 mt-2 mb-2">Administración General</h4>
                                
                                <button @click="currentTab = 'user_management'" :class="{ 'bg-indigo-500 text-white': currentTab === 'user_management', 'text-indigo-700 hover:bg-indigo-50': currentTab !== 'user_management' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2">
                                    <i class="fas fa-user-plus mr-2"></i> Crear Usuario
                                </button>
                                
                                {{-- MÓDULO DE GESTIÓN DE TALENTO (SUBMENÚ DESPLEGABLE) --}}
                                <div x-data="{ talentOpen: false }">
                                    <h4 
                                        @click="talentOpen = ! talentOpen" 
                                        class="flex justify-between items-center text-sm font-bold text-gray-800 hover:text-indigo-600 cursor-pointer mt-4 mb-2 p-2 rounded-lg transition-colors duration-150"
                                    >
                                        <span class="flex items-center">
                                            <i class="fas fa-medal mr-2"></i> Gestión de Talento
                                        </span>
                                        <i class="fas fa-chevron-down text-xs transform transition-transform duration-300" :class="{ 'rotate-180': talentOpen }"></i>
                                    </h4>
                                    
                                    <div x-show="talentOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-y-0" x-transition:enter-end="opacity-100 transform scale-y-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-y-100" x-transition:leave-end="opacity-0 transform scale-y-0" class="origin-top pl-4 pt-1 pb-1 border-l border-indigo-200 ml-2 space-y-1">
                                        
                                        <button @click="currentTab = 'talent_convocatorias'" :class="{ 'bg-indigo-500 text-white': currentTab === 'talent_convocatorias', 'text-indigo-700 hover:bg-indigo-50': currentTab !== 'talent_convocatorias' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold text-sm">
                                            <i class="fas fa-bullhorn mr-2"></i> Convocatorias
                                        </button>
                                        
                                    </div>
                                </div>
                                <hr class="border-gray-200 mt-4">
                                @break
                            
                            @case('analista')
                                <button @click="currentTab = 'reports'" :class="{ 'bg-indigo-500 text-white': currentTab === 'reports', 'text-indigo-700 hover:bg-indigo-50': currentTab !== 'reports' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2">
                                    <i class="fas fa-chart-line mr-2"></i> Reportes
                                </button>
                                @break

                            @case('reclutador')
                                <button @click="currentTab = 'candidates'" :class="{ 'bg-indigo-500 text-white': currentTab === 'candidates', 'text-indigo-700 hover:bg-indigo-50': currentTab !== 'candidates' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2">
                                    <i class="fas fa-users mr-2"></i> Candidatos
                                </button>
                                @break
                            
                            @case('operador')
                                <button @click="currentTab = 'tasks'" :class="{ 'bg-indigo-500 text-white': currentTab === 'tasks', 'text-indigo-700 hover:bg-indigo-50': currentTab !== 'tasks' }" class="w-full text-left py-2 px-3 rounded-lg transition-colors duration-150 font-semibold mb-2">
                                    <i class="fas fa-tasks mr-2"></i> Tareas
                                </button>
                                @break
                        @endswitch
                    </div>

                    {{-- 2. ÁREA DE CONTENIDO DINÁMICO --}}
                    <div class="w-3/4 pl-6">
                        <h1 class="text-3xl font-bold mb-6 text-gray-700">Panel Dinámico</h1>
                        
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
                                                 class="w-24 h-24 rounded-full object-cover border-4 border-indigo-500 shadow-md mx-auto">
                                        @else
                                            <div class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center text-3xl font-extrabold text-indigo-700 mx-auto border-2 border-indigo-500 shadow-md">
                                                {{ substr(Auth::user()->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- DATOS --}}
                                    <div class="space-y-2">
                                        <p class="text-3xl font-extrabold text-gray-900">{{ Auth::user()->name }}</p>
                                        <p class="text-lg text-gray-700 flex items-center justify-center">
                                            <i class="fas fa-id-card mr-2 text-indigo-500"></i>
                                            DNI: <span class="ml-1 font-semibold">{{ Auth::user()->dni ?? 'N/A' }}</span>
                                        </p>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-user-tag mr-2 text-indigo-500"></i>
                                            <span class="px-3 py-1 text-sm font-bold rounded-full bg-indigo-600 text-white shadow-md">
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
                                    <h2 class="text-2xl font-semibold mb-4 text-indigo-600">Bienvenido, Jefe.</h2>
                                    <p class="text-gray-600">Contenido administrativo y KPIs.</p>
                                </div>
                                <div x-show="currentTab === 'user_management'">
                                    @include('admin.users.create')
                                </div>
                                <div x-show="currentTab === 'client_management'">
                                    <h2 class="text-2xl font-semibold mb-4 text-indigo-600">Administración de Clientes</h2>
                                    <p class="text-gray-600">Listado de clientes.</p>
                                </div>
                                
                                {{-- VISTA DE CONVOCATORIAS --}}
                                <div x-show="currentTab === 'talent_convocatorias'">
                                    @include('talent.convocatorias')
                                </div>
                                @break

                            @case('analista')
                                <div x-show="currentTab === 'reports'">
                                    <h2 class="text-2xl font-semibold mb-4 text-indigo-600">Panel de Reportes</h2>
                                    <p class="text-gray-600">Aquí se muestran los gráficos y datos de análisis.</p>
                                </div>
                                @break

                            @case('reclutador')
                                <div x-show="currentTab === 'candidates'">
                                    <h2 class="text-2xl font-semibold mb-4 text-indigo-600">Gestión de Candidatos</h2>
                                    <p class="text-gray-600">Listado y gestión de procesos de selección.</p>
                                </div>
                                @break
                            
                            @case('operador')
                                <div x-show="currentTab === 'tasks'">
                                    <h2 class="text-2xl font-semibold mb-4 text-indigo-600">Mis Tareas Pendientes</h2>
                                    <p class="text-gray-600">El operador solo ve sus tareas diarias.</p>
                                </div>
                                @break
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>