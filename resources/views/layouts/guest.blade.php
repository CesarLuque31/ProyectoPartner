<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJd/rYhMxxhTIfXQo/A0Z0l8oVwE4W4u7P8jJ5P2d/l8QzL9C8DkQ/1G7Z4vA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Estilos personalizados para Neumorfismo -->
        <style>
            .neumo-container {
                background-color: #f0f0f3; 
            }
/* Separación entre los grupos de input */
.neumo-group {
    margin-bottom: 1.5rem; /* Ajusta este valor según tu gusto */
    position: relative; /* Necesario para posicionar iconos absolutos */
}

            /* Tarjeta de formulario */
            .neumo-card {
                background-color: #f0f0f3;
                border-radius: 1.5rem; 
                padding: 2.5rem;
                /* Sombra suave de neumorfismo */
                box-shadow: 10px 10px 20px #bebebe, 
                            -10px -10px 20px #ffffff;
            }

            /* Etiquetas de input */
            .neumo-label {
                color: #4a5568; 
                font-weight: 500;
            }

            /* Inputs */
            .neumo-input {
                background-color: #f0f0f3;
                border: none;
                box-shadow: inset 5px 5px 10px #bebebe, 
                            inset -5px -5px 10px #ffffff;
                border-radius: 0.75rem;
                transition: all 0.2s ease-in-out;
                padding-left: 3rem;
            }

            .neumo-input:focus {
                outline: none;
                box-shadow: inset 2px 2px 5px #bebebe, 
                            inset -2px -2px 5px #ffffff,
                            0 0 0 3px rgba(66, 153, 225, 0.5);
            }

            /* Iconos dentro del input */
            .icon-input {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: #a0aec0;
                pointer-events: none;
                z-index: 10;
            }
            
            .password-toggle-button {
                position: absolute;
                right: 0.5rem;
                top: 50%;
                transform: translateY(-50%);
                border: none;
                background: none;
                cursor: pointer;
                color: #a0aec0;
                padding: 0.5rem;
                border-radius: 50%;
                transition: all 0.2s ease-in-out;
            }

            .password-toggle-button:hover {
                color: #4a5568;
            }

            /* Botón principal (Log in) */
            .neumo-button {
                background-color: #4299e1; 
                color: white;
                font-weight: bold;
                padding: 0.75rem 1.5rem;
                border-radius: 0.75rem;
                border: none;
                max-width: 50%; 
                margin-left: auto; 
                margin-right: auto; 
                transition: all 0.2s ease-in-out;
            }

            .neumo-button:hover {
                background-color: #3182ce;
                box-shadow: inset 3px 3px 6px #2b6cb0, 
                            inset -3px -3px 6px #4c9ee7;
            }
            
            /* Checkbox */
            .neumo-checkbox {
                appearance: none;
                width: 1.25rem;
                height: 1.25rem;
                border-radius: 0.375rem;
                background-color: #f0f0f3;
                box-shadow: 3px 3px 6px #bebebe, -3px -3px 6px #ffffff;
                transition: all 0.2s ease-in-out;
                cursor: pointer;
                border: none;
            }
            
            .neumo-checkbox:checked {
                background-color: #4299e1;
                box-shadow: inset 3px 3px 6px #2b6cb0, inset -3px -3px 6px #4c9ee7;
            }

            .neumo-checkbox:checked:after {
                content: '\f00c'; 
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                color: white;
                font-size: 0.75rem;
                position: relative;
                top: -0.1rem;
                left: 0.15rem;
            }

            .password-toggle-button.neumo-toggle-button:hover {
                background-color: #e0e0e3;
            }

        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased neumo-container">
        <!-- SE CAMBIÓ pt-40 por pt-6 y se mantiene el centrado vertical -->
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">

            <div class="w-full sm:max-w-md mt-6 neumo-card">
                <!-- SE ELIMINÓ la clase text-center que podría causar problemas de layout -->
                <div> 
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>