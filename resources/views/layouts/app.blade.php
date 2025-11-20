<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="route-consulta" content="{{ route('postulantes.consulta') }}">
        <meta name="route-store" content="{{ route('postulantes.store') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
        
        <!-- Select2 CSS (Necesario para el multiselect) -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-celeste" id="page-top"> 
        @include('layouts.navigation')

        @if(isset($header) || View::hasSection('header'))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @isset($header)
                        {{ $header }}
                    @else
                        @yield('header')
                    @endisset
                </div>
            </header>
        @endif

        <main>
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </main>
    </div>
    
    <!-- SCRIPTS DE LIBRERÍAS AL FINAL DEL BODY (ORDEN CRÍTICO) -->
    
    <!-- 1. JQuery (Dependencia de Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- 2. Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- 3. @STACK('SCRIPTS') - AQUÍ SE INYECTA EL CÓDIGO DE INICIALIZACIÓN CON $ -->
    @stack('scripts') 

    <script>
        // Mantenemos la función window.onload, si es estrictamente necesaria.
        window.onload = function() {
            if (window.scrollY !== 0) {
                window.scrollTo(0, 0);
            }
        };
    </script>
</body>
</html>