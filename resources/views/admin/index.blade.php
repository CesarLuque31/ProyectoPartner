@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Panel de Administración (JEFE)') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <h1 class="text-3xl font-bold mb-4 text-indigo-700">Bienvenido, Jefe</h1>
                    
                    <p class="mt-4 text-gray-600">
                        Este es el panel principal de administración. Aquí podrás gestionar usuarios, clientes y reportes.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-4">
                        <!-- Tarjeta de Gestión de Usuarios -->
                        <div class="w-full sm:w-1/2 lg:w-1/3 p-4 bg-gray-50 border border-gray-200 rounded-lg shadow-md hover:shadow-xl transition duration-300">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Gestión de Usuarios</h3>
                            <p class="text-gray-600 mb-4">Crea, edita y asigna roles a los operadores del sistema.</p>
                            <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Ir a Usuarios
                            </a>
                        </div>
                        
                        <!-- Tarjeta de Clientes -->
                        <div class="w-full sm:w-1/2 lg:w-1/3 p-4 bg-gray-50 border border-gray-200 rounded-lg shadow-md hover:shadow-xl transition duration-300">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Administrar Clientes</h3>
                            <p class="text-gray-600 mb-4">Revisa y modifica la información de todos los clientes.</p>
                            <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Ir a Clientes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection