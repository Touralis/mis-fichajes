<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Fichajes y Empleados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#7BC6BF',
                        secondary: '#F97316',
                    }
                }
            }
        }
    </script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        /* Asegura que el body no tenga margen ni scroll horizontal */
        body {
            margin: 0;
            padding-right: 0 !important;
            /* Evita scroll adicional al abrir el sidebar */
        }

        /* Estilo para el backdrop */
        .backdrop-blur {
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        section {
            margin-top: 2rem;
        }
    </style>
</head>

<body class="bg-gray-100 p-0 sm:p-6 flex flex-col min-h-screen" x-data="mainData()"
    @open-laboral.window="abrirRegistroLaboral($event.detail)">
    <!-- Botón de menú hamburguesa para móviles -->
    <div class="sm:hidden bg-white shadow p-4 flex items-center justify-between fixed top-0 left-0 right-0 z-40">
        <button @click="sidebarOpen = true" class="p-2 rounded-lg text-black">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <h1 class="text-lg font-bold text-gray-800">Administración</h1>
    </div>

    <!-- Sidebar Off-Canvas -->
    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg border-r border-gray-200 transform transition-transform duration-300 ease-in-out sm:translate-x-0"
        :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
        <div class="p-6 h-full flex flex-col">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Administración</h2>
            <nav class="space-y-3 flex-1">
                <button @click="active = 'fichajes'; sidebarOpen = false"
                    class="w-full flex items-center px-4 py-3 text-left rounded-lg transition-colors"
                    :class="{ 'bg-primary text-white': active === 'fichajes', 'text-gray-700 hover:bg-gray-100': active !== 'fichajes' }">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    Fichajes
                </button>
                <button @click="active = 'empleados'; sidebarOpen = false"
                    class="w-full flex items-center px-4 py-3 text-left rounded-lg transition-colors"
                    :class="{ 'bg-primary text-white': active === 'empleados', 'text-gray-700 hover:bg-gray-100': active !== 'empleados' }">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    Empleados
                </button>
                <button @click="active = 'configuracion'; sidebarOpen = false"
                    class="w-full flex items-center px-4 py-3 text-left rounded-lg transition-colors"
                    :class="{ 'bg-primary text-white': active === 'configuracion', 'text-gray-700 hover:bg-gray-100': active !== 'configuracion' }">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                    Configuración
                </button>
                <!-- Los enlaces de descarga y logout se mantienen aquí si son relevantes -->
            </nav>
            <!-- Botón de cierre para móviles -->
            <button @click="sidebarOpen = false"
                class="absolute top-4 right-4 sm:hidden text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </aside>

    <!-- Backdrop para móviles -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40 sm:hidden transition-opacity duration-300"
        @click="sidebarOpen = false" x-show="sidebarOpen" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-50"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-50"
        x-transition:leave-end="opacity-0">
    </div>

    <!-- Main content -->
    <main class="flex-grow p-0 sm:p-6 mt-16 sm:mt-0" :class="{ 'sm:ml-64': true }">
        <!-- mt-16 para compensar el header fijo en móvil -->
        <!-- Fichajes section -->
        <section x-show="active === 'fichajes'" x-transition>
            <!-- Filtro -->
            <div class="mb-4 flex flex-col items-end" x-data="{ openFilter: false }">
                <button @click="openFilter = !openFilter"
                    class="px-4 py-2 rounded-lg bg-primary text-white font-semibold hover:bg-cyan-600 transition-colors">
                    Filter
                </button>
                <form x-show="openFilter" x-transition
                    class="absolute top-16 right-6 z-50 bg-white p-4 rounded-lg shadow space-y-2 w-full max-w-md"
                    method="GET" action="{{ route('fichajes.dashboard.admin') }}">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-semibold">Nombre</label>
                            <input type="text" name="nombre" value="{{ request('nombre') }}"
                                class="w-full px-2 py-1 border rounded-lg focus:ring-1 focus:ring-cyan-400">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Tipo</label>
                            <input type="text" name="tipo" value="{{ request('tipo') }}"
                                class="w-full px-2 py-1 border rounded-lg focus:ring-1 focus:ring-cyan-400">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Entrada desde</label>
                            <input type="datetime-local" name="dia_entrada" value="{{ request('dia_entrada') }}"
                                class="w-full px-2 py-1 border rounded-lg focus:ring-1 focus:ring-cyan-400">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold">Salida hasta</label>
                            <input type="datetime-local" name="dia_salida" value="{{ request('dia_salida') }}"
                                class="w-full px-2 py-1 border rounded-lg focus:ring-1 focus:ring-cyan-400">
                        </div>
                    </div>
                    <div class="flex justify-end mt-2">
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-cyan-500 text-white font-semibold hover:bg-cyan-600 transition-colors">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
            <!-- Tabla de fichajes -->
            <div class="bg-white rounded-2xl shadow p-4 sm:p-6"> <!-- Padding más pequeño en móvil -->
                <h2 class="text-xl sm:text-2xl font-bold mb-4">Fichajes</h2>
                <div class="overflow-x-auto relative">
                    <table class="w-full text-xs sm:text-sm"> <!-- Texto más pequeño en móvil -->
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700">Usuario
                                </th>
                                <th class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700">Tipo</th>
                                <th class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700">Entrada
                                </th>
                                <th class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700">Salida</th>
                                <th class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700">Duración
                                </th>
                                <th class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700">Geolocalizacion Entrada
                                </th> <!-- Texto acortado -->
                                <th class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700">Geolocalizacion Salida
                                </th> <!-- Texto acortado -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fichajes as $fichaje)
                                @php
                                    $duracion = 'N/A';
                                    if ($fichaje->dia_salida) {
                                        $entrada = \Carbon\Carbon::parse($fichaje->dia_entrada);
                                        $salida = \Carbon\Carbon::parse($fichaje->dia_salida);
                                        $segundos = $entrada->diffInSeconds($salida);
                                        $horas = intdiv($segundos, 3600);
                                        $minutos = intdiv($segundos % 3600, 60);
                                        $secs = $segundos % 60;
                                        $duracion = sprintf('%dh %dm %ds', $horas, $minutos, $secs);
                                    }
                                @endphp
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-900">{{ $fichaje->id }}</td>
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-700">
                                        {{ $fichaje->user->name ?? 'Usuario' }}</td>
                                    <td class="py-2 px-2 sm:py-3 sm:px-4">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">{{ $fichaje->tipo }}</span>
                                        <!-- Padding más pequeño en móvil -->
                                    </td>
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-700">
                                        {{ \Carbon\Carbon::parse($fichaje->dia_entrada)->format('d/m/Y H:i') }}</td>
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-700">
                                        {{ $fichaje->dia_salida ? \Carbon\Carbon::parse($fichaje->dia_salida)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-900 font-semibold">
                                        {{ $duracion }}</td>
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-700">
                                        @if ($fichaje->geolocalizacion)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $fichaje->geolocalizacion }}"
                                                target="_blank"
                                                class="text-blue-600 hover:text-blue-800 font-semibold transition-colors text-xs sm:text-sm">
                                                <!-- Texto más pequeño en móvil -->
                                                Ver en Google Maps
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs sm:text-sm">Sin ubicación</span>
                                            <!-- Texto más pequeño en móvil -->
                                        @endif
                                    </td>
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-700">
                                        @if ($fichaje->geolocalizacionExit)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $fichaje->geolocalizacionExit }}"
                                                target="_blank"
                                                class="text-blue-600 hover:text-blue-800 font-semibold transition-colors text-xs sm:text-sm">
                                                <!-- Texto más pequeño en móvil -->
                                                Ver en Google Maps
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs sm:text-sm">Sin ubicación</span>
                                            <!-- Texto más pequeño en móvil -->
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $fichajes->links() }}
                </div>
            </div>
        </section>
        <!-- Laboral section -->
        <section x-show="active === 'laboral'" x-transition>
            <button @click="volverAEmpleados()"
                class="mb-4 px-4 py-2 rounded-lg bg-primary text-white font-semibold hover:bg-cyan-600 transition-colors">
                ← Volver a empleados
            </button>
            <button @click="descargarRegistroLaboral()"
                class="mb-4 px-4 py-2 rounded-lg bg-primary text-white font-semibold hover:bg-cyan-600 transition-colors">
                Descargar Registro Laboral
            </button>
            <div class="bg-white rounded-2xl shadow p-4 sm:p-6"> <!-- Padding más pequeño en móvil -->
                <h2 class="text-xl sm:text-2xl font-bold mb-4">Registro Laboral - <span
                        x-text="employerSelected?.nombre + ' ' + (employerSelected?.apellidos || '')"></span></h2>
                <!-- Filtros -->
                <div class="mb-6 flex flex-wrap gap-2 sm:gap-4 items-end"> <!-- Gap más pequeño en móvil -->
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Mes</label>
                        <select x-model="filtros.month" @change="cargarFichajes()"
                            class="px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm">
                            <!-- Tamaño de texto más pequeño en móvil -->
                            <option value="">Todos los meses</option>
                            <option value="1">Ene</option> <!-- Texto acortado -->
                            <option value="2">Feb</option>
                            <option value="3">Mar</option>
                            <option value="4">Abr</option>
                            <option value="5">May</option>
                            <option value="6">Jun</option>
                            <option value="7">Jul</option>
                            <option value="8">Ago</option>
                            <option value="9">Sep</option>
                            <option value="10">Oct</option>
                            <option value="11">Nov</option>
                            <option value="12">Dic</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Año</label>
                        <input type="number" x-model="filtros.year" @change="cargarFichajes()"
                            class="px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm"
                        min="2020" :max="new Date().getFullYear()">
                    </div>
                </div>
                <!-- Tabla de fichajes -->
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm border-collapse border border-gray-300">
                        <!-- Tamaño de texto más pequeño en móvil -->
                        <thead>
                            <tr class="bg-gray-100">
                                <th
                                    class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-left font-semibold text-xs sm:text-sm">
                                    Fecha Entrada</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-left font-semibold text-xs sm:text-sm">
                                    Fecha Salida</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-left font-semibold text-xs sm:text-sm">
                                    Tipo</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-left font-semibold text-xs sm:text-sm">
                                    Duración</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-left font-semibold text-xs sm:text-sm">
                                    Geolocalización</th> <!-- Tamaño de texto más pequeño en móvil -->
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="fichajes.length > 0">
                                <template x-for="fichaje in fichajes" :key="fichaje.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm"
                                            x-text="formatDate(fichaje.dia_entrada)"></td>
                                        <td class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm"
                                            x-text="fichaje.dia_salida ? formatDate(fichaje.dia_salida) : '-'"></td>
                                        <td
                                            class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                            <!-- Tamaño de texto más pequeño en móvil -->
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800"
                                                x-text="fichaje.tipo"></span>
                                        </td>
                                        <td class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 font-semibold text-xs sm:text-sm"
                                            x-text="calcularDuracion(fichaje)"></td>
                                        <td
                                            class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                            <!-- Tamaño de texto más pequeño en móvil -->
                                            <template x-if="fichaje.geolocalizacion">
                                                <a :href="'https://www.google.com/maps/search/?api=1&query=' + fichaje
                                                    .geolocalizacion"
                                                    target="_blank"
                                                    class="text-blue-600 hover:text-blue-800 font-semibold">
                                                    Ver en GM
                                                </a>
                                            </template>
                                            <template x-if="!fichaje.geolocalizacion">
                                                <span class="text-gray-500">-</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                            <template x-if="fichajes.length === 0">
                                <tr>
                                    <td colspan="5"
                                        class="border border-gray-300 px-2 py-2 sm:px-4 sm:py-3 text-center text-gray-500 text-xs sm:text-sm">
                                        <!-- Tamaño de texto más pequeño en móvil -->
                                        No hay registros para los filtros aplicados.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <!-- Empleados section -->
        <section x-show="active === 'empleados'" x-transition x-data="empleadosModal()">
            <div class="bg-white rounded-2xl shadow p-4 sm:p-6"> <!-- Padding más pequeño en móvil -->
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl sm:text-2xl font-bold">Empleados</h2>
                    <button @click="abrirModalNuevo()"
                        class="px-4 py-1 sm:px-6 sm:py-2 rounded-lg font-semibold transition-colors text-sm sm:text-base"
                        style="background-color: #7BC6BF; color: white;">
                        + Nuevo Empleado
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm"> <!-- Tamaño de texto más pequeño en móvil -->
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th
                                    class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700 text-xs sm:text-sm">
                                    ID</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700 text-xs sm:text-sm">
                                    Nombre</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700 text-xs sm:text-sm">
                                    Email</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700 text-xs sm:text-sm">
                                    Puesto</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700 text-xs sm:text-sm">
                                    H. Diarias</th> <!-- Texto acortado -->
                                <th
                                    class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700 text-xs sm:text-sm">
                                    Teléfono</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="text-left py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700 text-xs sm:text-sm">
                                    Contraseña</th> <!-- Tamaño de texto más pequeño en móvil -->
                                <th
                                    class="text-center py-2 px-2 sm:py-3 sm:px-4 font-semibold text-gray-700 text-xs sm:text-sm">
                                    Acciones</th> <!-- Tamaño de texto más pequeño en móvil -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($empleados as $empleado)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-900 text-xs sm:text-sm">
                                        {{ $empleado->id }}</td> <!-- Tamaño de texto más pequeño en móvil -->
                                    <td
                                        class="py-2 px-2 sm:py-3 sm:px-4 text-gray-900 font-semibold text-xs sm:text-sm">
                                        {{ $empleado->nombre }}
                                        {{ $empleado->apellidos }}</td>
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-700 text-xs sm:text-sm">
                                        {{ $empleado->mail }}</td> <!-- Tamaño de texto más pequeño en móvil -->
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-700 text-xs sm:text-sm">
                                        {{ $empleado->puesto_trabajo }}</td>
                                    <!-- Tamaño de texto más pequeño en móvil -->
                                    <td
                                        class="py-2 px-2 sm:py-3 sm:px-4 text-gray-900 font-semibold text-xs sm:text-sm">
                                        {{ $empleado->horas_diarias }}</td>
                                    <!-- Tamaño de texto más pequeño en móvil -->
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-700 text-xs sm:text-sm">
                                        {{ $empleado->telefono }}</td> <!-- Tamaño de texto más pequeño en móvil -->
                                    <td class="py-2 px-2 sm:py-3 sm:px-4 text-gray-700 text-xs sm:text-sm">
                                        <!-- Tamaño de texto más pequeño en móvil -->
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-semibold">{{ $empleado->password }}</span>
                                        <!-- Padding más pequeño en móvil -->
                                    </td>
                                    <td
                                        class="py-2 px-2 sm:py-3 sm:px-4 text-center space-x-1 sm:space-x-2 text-xs sm:text-sm">
                                        <!-- Tamaño de texto y espacio más pequeño en móvil -->
                                        <button
                                            @click="$dispatch('open-laboral', {
id: {{ $empleado->id }},
nombre: '{{ $empleado->nombre }}',
apellidos: '{{ $empleado->apellidos }}',
user_id: {{ $empleado->user_id }}
})"
                                            class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                            Registro Laboral
                                        </button>
                                        <button
                                            @click="abrirModalEditar({
id: {{ $empleado->id }},
nombre: '{{ $empleado->nombre }}',
apellidos: '{{ $empleado->apellidos }}',
email: '{{ $empleado->mail }}',
telefono: '{{ $empleado->telefono }}',
dni: '{{ $empleado->dni }}',
puesto_trabajo: '{{ $empleado->puesto_trabajo }}',
horas_diarias: {{ $empleado->horas_diarias }},
numero_afiliacion_ss: '{{ $empleado->numero_afiliacion_ss }}'
})"
                                            class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                            Editar
                                        </button>
                                        <form method="POST"
                                            action="{{ route('fichajes.admin.destroyEmpleado', $empleado->id) }}"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-800 font-semibold transition-colors"
                                                onclick="return confirm('¿Estás seguro?')">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $empleados->links() }}
                </div>
                <!-- MODAL CREAR/EDITAR EMPLEADO -->
                <div x-show="openModal"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                    @keydown.escape="cerrarModal()" x-transition>
                    <div @click.away="cerrarModal()"
                        class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 max-w-sm sm:max-w-md w-full mx-4">
                        <!-- Padding y tamaño ajustados -->
                        <div class="flex items-center justify-between mb-4 sm:mb-6"> <!-- Margen ajustado -->
                            <h3 class="text-lg sm:text-2xl font-bold"
                                x-text="modoEdicion ? 'Editar Empleado' : 'Nuevo Empleado'"></h3>
                            <button @click="cerrarModal()"
                                class="text-gray-500 hover:text-gray-700 text-xl sm:text-2xl">×</button>
                            <!-- Tamaño de icono ajustado -->
                        </div>
                        <form @submit.prevent="guardarEmpleado()" class="space-y-3 sm:space-y-4">
                            <!-- Espaciado ajustado -->
                            @csrf
                            <input type="hidden" x-model="formulario.id" name="id">
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Nombre</label>
                                <input type="text" x-model="formulario.nombre" name="nombre"
                                    placeholder="Nombre del empleado" required
                                    class="w-full px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm">
                                <!-- Tamaño de texto y padding ajustados -->
                            </div>
                            <div>
                                <label
                                    class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Apellidos</label>
                                <input type="text" x-model="formulario.apellidos" name="apellidos"
                                    placeholder="Apellidos" required
                                    class="w-full px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm">
                                <!-- Tamaño de texto y padding ajustados -->
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Email</label>
                                <input type="email" x-model="formulario.email" name="email"
                                    placeholder="email@ejemplo.com" required
                                    class="w-full px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm">
                                <!-- Tamaño de texto y padding ajustados -->
                            </div>
                            <div>
                                <label
                                    class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                                <input type="tel" x-model="formulario.telefono" name="telefono"
                                    placeholder="Teléfono" required
                                    class="w-full px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm">
                                <!-- Tamaño de texto y padding ajustados -->
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">DNI</label>
                                <input type="text" x-model="formulario.dni" name="dni" placeholder="DNI"
                                    required
                                    class="w-full px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm">
                                <!-- Tamaño de texto y padding ajustados -->
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Puesto de
                                    Trabajo</label>
                                <input type="text" x-model="formulario.puesto_trabajo" name="puesto_trabajo"
                                    placeholder="Puesto de trabajo" required
                                    class="w-full px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm">
                                <!-- Tamaño de texto y padding ajustados -->
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Horas
                                    Diarias</label>
                                <input type="number" x-model.number="formulario.horas_diarias" name="horas_diarias"
                                    placeholder="8" required
                                    class="w-full px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm">
                                <!-- Tamaño de texto y padding ajustados -->
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1">Número de
                                    Afiliación SS</label>
                                <input type="text" x-model="formulario.numero_afiliacion_ss"
                                    name="numero_afiliacion_ss" placeholder="Número de afiliación" required
                                    class="w-full px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400 text-xs sm:text-sm">
                                <!-- Tamaño de texto y padding ajustados -->
                            </div>
                            <div class="flex gap-2 sm:gap-3 pt-3 sm:pt-4"> <!-- Espaciado ajustado -->
                                <button type="button" @click="cerrarModal()"
                                    class="flex-1 px-2 py-1 sm:px-4 sm:py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors text-xs sm:text-sm">
                                    <!-- Tamaño de texto y padding ajustados -->
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="flex-1 px-2 py-1 sm:px-4 sm:py-2 rounded-lg text-white font-semibold transition-colors text-xs sm:text-sm"
                                    style="background-color: #7BC6BF;">
                                    <span x-text="modoEdicion ? 'Guardar Cambios' : 'Crear Empleado'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <section x-show="active === 'configuracion'" x-transition x-data="{
            saving: false,
            successMessage: '',
            errorMessage: ''
        }">
            @php
                $config = App\Models\FichajesConfiguracion::first();
            @endphp
            <div class="bg-white rounded-2xl shadow p-4 sm:p-6"> <!-- Padding más pequeño en móvil -->
                <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Configuración</h2>
                <form
                    @submit.prevent="
saving = true;
successMessage = '';
errorMessage = '';
const formData = new FormData($event.target);
fetch('{{ route('fichajes.admin.configuracion.update') }}', {
method: 'POST',
headers: {
'X-CSRF-TOKEN': '{{ csrf_token() }}'
},
body: formData
})
.then(res => res.json())
.then(data => {
if (data.success) {
successMessage = data.message;
} else {
errorMessage = 'Error al guardar la configuración';
}
})
.catch(() => {
errorMessage = 'Error al guardar la configuración';
})
.finally(() => {
saving = false;
});
"
                    enctype="multipart/form-data" class="space-y-4 sm:space-y-6 max-w-full sm:max-w-xl">
                    <!-- Espaciado ajustado -->
                    <!-- MENSAJE ÉXITO -->
                    <div x-show="successMessage" x-transition
                        class="p-2 sm:p-3 rounded-lg bg-green-100 text-green-800 font-semibold text-xs sm:text-sm">
                        <!-- Tamaño de texto y padding ajustados -->
                        <span x-text="successMessage"></span>
                    </div>
                    <!-- MENSAJE ERROR -->
                    <div x-show="errorMessage" x-transition
                        class="p-2 sm:p-3 rounded-lg bg-red-100 text-red-800 font-semibold text-xs sm:text-sm">
                        <!-- Tamaño de texto y padding ajustados -->
                        <span x-text="errorMessage"></span>
                    </div>
                    <!-- GEOLOCALIZACIÓN -->
                    <div class="flex items-center gap-2 sm:gap-3"> <!-- Espaciado ajustado -->
                        <input type="checkbox" name="geolocalizacion" id="geolocalizacion"
                            class="w-4 h-4 sm:w-5 sm:h-5 text-primary rounded" {{ optional($config)->geolocalizacion ? 'checked' : '' }}>
                        <label for="geolocalizacion" class="font-semibold text-xs sm:text-sm">
                            <!-- Tamaño de texto ajustado -->
                            Activar geolocalización
                        </label>
                    </div>
                    <!-- FIRMA EMPRESA -->
                    <div>
                        <label class="block font-semibold mb-2 text-xs sm:text-sm"> <!-- Tamaño de texto ajustado -->
                            Firma de la empresa
                        </label>
                        @if (optional($config)->firma_empresa)
                            <img src="{{ asset('storage/' . $config->firma_empresa) }}"
                                class="h-16 sm:h-32 mb-2 sm:mb-3 border rounded text-xs sm:text-sm">
                            <!-- Tamaño ajustado -->
                        @endif
                        <input type="file" name="firma_empresa" accept="image/*"
                            class="block w-full text-xs sm:text-sm"> <!-- Tamaño de texto ajustado -->
                    </div>
                    <!-- BOTÓN -->
                    <button type="submit"
                        class="px-4 py-1 sm:px-6 sm:py-2 rounded-lg bg-primary text-white font-semibold flex items-center gap-1 sm:gap-2 text-xs sm:text-sm"
                        :disabled="saving">
                        <span x-show="!saving">Guardar configuración</span>
                        <span x-show="saving">Guardando...</span>
                    </button>
                </form>
            </div>
        </section>
    </main>
    <script>
        function mainData() {
            return {
                active: 'fichajes',
                sidebarOpen: false, // Estado del sidebar para móviles
                employerSelected: null,
                fichajes: [],
                filtros: {
                    month: '',
                    year: new Date().getFullYear()
                },
                abrirRegistroLaboral(event) {
                    const employer = event.detail || event;
                    this.employerSelected = employer;
                    this.active = 'laboral';
                    this.cargarFichajes();
                },
                descargarRegistroLaboral() {
                    const employer = this.employerSelected;
                    if (!employer) return;
                    let url = `{{ route('fichajes.admin.downloadRegistroLaboral', ['employer_id' => '__ID__']) }}`;
                    url = url.replace('__ID__', employer.id);
                    fetch(url)
                        .then(response => response.blob())
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = `RegistroLaboral-${employer.nombre}.pdf`;
                            link.click();
                        })
                        .catch(error => console.error('Error:', error));
                },
                volverAEmpleados() {
                    this.active = 'empleados';
                    this.employerSelected = null;
                    this.fichajes = [];
                },
                cargarFichajes() {
                    if (!this.employerSelected) return;
                    const params = new URLSearchParams();
                    params.append('user_id', this.employerSelected.user_id);
                    if (this.filtros.month) params.append('month', this.filtros.month);
                    if (this.filtros.year) params.append('year', this.filtros.year);
                    fetch(`{{ route('fichajes.admin.getFichajes') }}?${params.toString()}`)
                        .then(response => response.json())
                        .then(data => {
                            this.fichajes = data;
                        })
                        .catch(error => console.error('Error:', error));
                },
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },
                calcularDuracion(fichaje) {
                    if (!fichaje.dia_salida) return 'N/A';
                    const entrada = new Date(fichaje.dia_entrada);
                    const salida = new Date(fichaje.dia_salida);
                    const segundos = Math.floor((salida - entrada) / 1000);
                    const horas = Math.floor(segundos / 3600);
                    const minutos = Math.floor((segundos % 3600) / 60);
                    const secs = segundos % 60;
                    return `${horas}h ${minutos}m ${secs}s`;
                }
            }
        }

        function empleadosModal() {
            return {
                openModal: false,
                modoEdicion: false,
                formulario: {
                    id: null,
                    nombre: '',
                    apellidos: '',
                    email: '',
                    telefono: '',
                    dni: '',
                    puesto_trabajo: '',
                    horas_diarias: 8,
                    numero_afiliacion_ss: ''
                },
                abrirModalNuevo() {
                    this.modoEdicion = false;
                    this.formulario = {
                        id: null,
                        nombre: '',
                        apellidos: '',
                        email: '',
                        telefono: '',
                        dni: '',
                        puesto_trabajo: '',
                        horas_diarias: 8,
                        numero_afiliacion_ss: ''
                    };
                    this.openModal = true;
                },
                abrirModalEditar(empleado) {
                    this.modoEdicion = true;
                    this.formulario = {
                        ...empleado
                    };
                    this.openModal = true;
                },
                cerrarModal() {
                    this.openModal = false;
                    this.modoEdicion = false;
                },
                guardarEmpleado() {
                    let url = '';
                    if (this.modoEdicion) {
                        url = `{{ route('fichajes.admin.updateEmpleado', ['id' => '__ID__']) }}`;
                        url = url.replace('__ID__', this.formulario.id);
                    } else {
                        url = '{{ route('fichajes.admin.storeEmpleado') }}';
                    }
                    const method = this.modoEdicion ? 'PUT' : 'POST';
                    fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value ||
                                    '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.formulario)
                        })
                        .then(response => {
                            if (response.ok) {
                                window.location.reload();
                            } else {
                                alert('Error al guardar el empleado');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error al procesar la solicitud');
                        });
                }
            }
        }
    </script>
</body>

</html>
