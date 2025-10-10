 <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Fichajes</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Configuración opcional de Tailwind -->
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

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <!-- Livewire si lo necesitas -->
    {{-- @livewireStyles y @livewireScripts solo si usas Livewire --}}
</head>
<body class="bg-gray-100 p-6">
  <!-- Botón de Cerrar sesión -->
<form method="POST" action="{{ route('logout') }}" class="absolute top-6 right-6">
    @csrf
    <button
        type="submit"
        class="bg-red-500 text-white font-bold py-2 px-4 rounded-full hover:bg-red-600 transition-colors"
        style="background-color: #F97316;">
        Cerrar sesión
    </button>
</form>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-full">
        <div class="bg-white rounded-2xl shadow p-6 flex flex-col space-y-2">
            <div>
                <h2 class="text-xl font-bold mb-1">Registro de horas</h2>
                <p class="text-gray-600 font-bold mb-4">{{ now()->translatedFormat('j F Y') }}</p>
            </div>
            <div class="mt-4 flex flex-col p-3 bg-gray-100 rounded-lg mb-4">
                <span class="font-semibold">Fecha última salida</span>
                <span class="text-gray-600">
                    {{ $ultimaSalida ? \Carbon\Carbon::parse($ultimaSalida->dia_salida)->translatedFormat('j F Y H:i') : 'Sin registros' }}
                </span>
            </div>
            <div class="mt-4 flex flex-col p-4 rounded-lg items-center justify-center"
                x-data="timerComponent(@js($startTime))"
                x-init="init()"
                @timer-updated.window="updateTimer($event.detail.startTime)">
                <div class="text-center text-3xl font-bold mb-4" id="timer-text" x-text="display">
                </div>
                <form method="POST" action="{{ route('fichajes.click') }}">
        @csrf
        <button
            type="submit"
            id="start-btn"
            class="mt-4 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors"
            style="background-color: #7BC6BF; padding: .8rem 1.5rem; border-radius: 30px;">
            {{ $buttonText }}
        </button>
    </form>
                {{-- <button --}}
                {{--     id="start-btn" --}}
                {{--     class="mt-4 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors" --}}
                {{--     style="background-color: #7BC6BF; padding: .8rem 1.5rem; border-radius: 30px;" --}}
                {{--     wire:click="clickButton"> --}}
                {{-- {{ $buttonText }} --}}
                {{-- </button> --}}
            </div>
        </div>

        {{-- <div class="bg-white rounded-2xl shadow p-6 flex flex-col h-full"> --}}
        {{--     <h2 class="text-xl font-bold mb-4">Estadísticas</h2> --}}
        {{--     <div class="flex flex-col flex-1 justify-between space-y-4"> --}}
        {{--         @foreach ($estadisticas as $stat) --}}
        {{--             <div class="flex flex-col border border-gray-200 rounded-lg p-4"> --}}
        {{--                 <div class="flex items-center justify-between mb-1"> --}}
        {{--                     <span class="text-xs font-semibold">{{ $stat['label'] }}</span> --}}
        {{--                     <span class="text-s font-bold"> --}}
        {{--                         {{ gmdate('H:i:s', $stat['current']) }} / {{ $stat['total'] }} --}}
        {{--                     </span> --}}
        {{--                 </div> --}}
        {{--                 <!-- Barra de progreso --> --}}
        {{--                 <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden mt-2"> --}}
        {{--                     <div class="h-full bg-blue-500 rounded-full transition-all duration-500" --}}
        {{--                          style="width: {{ $percentage }}%; background-color: #7BC6BF;"> --}}
        {{--                     </div> --}}
        {{--                 </div> --}}
        {{--             </div> --}}
        {{--         @endforeach --}}
        {{--     </div> --}}
        {{-- </div> --}}

        <div class="bg-white rounded-2xl shadow p-6 flex flex-col h-full">
    <h2 class="text-xl font-bold mb-4">Estadísticas</h2>
    <div class="flex flex-col flex-1 justify-between space-y-4">
        @foreach ($estadisticas as $stat)
            <div class="flex flex-col border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold">{{ $stat['label'] }}</span>
                    <span class="text-s font-bold">
                        {{ gmdate('H:i:s', $stat['current']) }} / {{ $stat['total'] }}
                    </span>
                </div>
                <!-- Barra de progreso -->
                <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden mt-2">
                    <div class="h-full bg-blue-500 rounded-full transition-all duration-500"
                         style="width: {{ min($stat['percentage'], 100) }}%; background-color: #7BC6BF;">
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

        <div class="bg-white rounded-2xl shadow p-6 h-full"
            style="max-height: 358px; overflow: hidden; overflow-y: scroll;"
            x-data="{}"
            @scroll-to-bottom.window="$el.scrollTop = $el.scrollHeight;">
            <h2 class="text-xl font-bold mb-4">Actividad de Hoy</h2>
            <div class="relative">
                <!-- Línea vertical -->
                <div class="absolute left-2 top-0 bottom-0 w-0.5 bg-orange-300"></div>
                <!-- Lista de actividades -->
                <div class="space-y-6">
                    @if ($fichajesHoy->isEmpty())
                        <div class="relative flex items-start">
                            <div class="w-3 h-3 bg-orange-400 rounded-full border-2 border-white shadow-sm z-10"
                                 style="border-color: #7BC6BF;"></div>
                            <div class="ml-4" style="padding-left: 0.5rem;">
                                <h3 class="font-semibold text-gray-900 text-sm">Sin actividad</h3>
                            </div>
                        </div>
                    @endif
                    @foreach ($fichajesHoy as $fichaje)
                        @if ($fichaje->dia_entrada)
                            <div class="relative flex items-start">
                                <div class="w-3 h-3 bg-orange-400 rounded-full border-2 border-white shadow-sm z-10"
                                     style="border-color: #7BC6BF;"></div>
                                <div class="ml-4" style="padding-left: 0.5rem;">
                                    <h3 class="font-semibold text-gray-900 text-sm">Entrada</h3>
                                    <p class="text-gray-500 text-sm flex items-center mt-1 gap-1">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($fichaje->dia_entrada)->format('H:i:s') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                        @if ($fichaje->dia_salida)
                            <div class="relative flex items-start">
                                <div class="w-3 h-3 bg-orange-400 rounded-full border-2 border-white shadow-sm z-10"
                                     style="border-color: #7BC6BF;"></div>
                                <div class="ml-4" style="padding-left: 0.5rem;">
                                    <h3 class="font-semibold text-gray-900 text-sm">Salida</h3>
                                    <p class="text-gray-500 text-sm flex items-center mt-1 gap-1">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($fichaje->dia_salida)->format('H:i:s') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        function timerComponent(startTime) {
            return {
                startTime,
                timerInterval: null,
                display: '0h 0m 0s',

                init() {
                    if (this.startTime) {
                        this.startTimer(this.startTime);
                    } else {
                        this.stopTimer();
                    }
                },

                updateTimer(newStartTime) {
                    this.startTime = newStartTime;
                    if (this.startTime) {
                        this.startTimer(this.startTime);
                    } else {
                        this.stopTimer();
                    }
                },

                updateTimerDisplay(seconds) {
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.floor((seconds % 3600) / 60);
                    const remainingSeconds = seconds % 60;
                    this.display = `${hours}h ${minutes}m ${remainingSeconds}s`;
                },

                startTimer(startTime) {
                    if (this.timerInterval) clearInterval(this.timerInterval);

                    const initialTimeInSeconds = Math.floor((Date.now() / 1000) - startTime);
                    this.updateTimerDisplay(initialTimeInSeconds);

                    let secondsPassed = initialTimeInSeconds;
                    this.timerInterval = setInterval(() => {
                        secondsPassed++;
                        this.updateTimerDisplay(secondsPassed);
                    }, 1000);
                },

                stopTimer() {
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.updateTimerDisplay(0);
                }
            }
        }
    </script>
</body>
