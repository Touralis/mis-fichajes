<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Fichajes y Empleados</title>
    <!-- Tailwind CSS CDN -->
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
    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-100 p-6 flex" x-data="{ active: 'fichajes' }">

    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-lg border-r border-gray-200 h-screen fixed top-0 left-0">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Administración</h2>
            <nav class="space-y-3">
                <button @click="active = 'fichajes'"
                    class="w-full flex items-center px-4 py-3 text-left rounded-lg transition-colors"
                    :class="{ 'bg-primary text-white': active === 'fichajes', 'text-gray-700 hover:bg-gray-100': active !== 'fichajes' }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Fichajes
                </button>
                <button @click="active = 'empleados'"
                    class="w-full flex items-center px-4 py-3 text-left rounded-lg transition-colors"
                    :class="{ 'bg-primary text-white': active === 'empleados', 'text-gray-700 hover:bg-gray-100': active !== 'empleados' }">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.982 18.725A20.003 20.003 0 0112 11.138a20.003 20.003 0 01-5.982 1.593M15 13a3 3 0 11-6 0 3 3 0 016 0zm-8 0a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Empleados
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="bg-red-500 text-white font-bold py-2 px-4 rounded-full hover:bg-red-600 transition-colors"
                        style="background-color: #F97316;">
                        Cerrar sesión
                    </button>
                </form>
            </nav>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-grow p-6 ml-64">
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
            <div class="bg-white rounded-2xl shadow p-6">
                <h2 class="text-2xl font-bold mb-4">Fichajes</h2>
                <div class="overflow-x-auto relative">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Usuario</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Tipo</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Entrada</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Salida</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Duración</th>
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
                                    <td class="py-3 px-4 text-gray-900">{{ $fichaje->id }}</td>
                                    <td class="py-3 px-4 text-gray-700">{{ $fichaje->user->name ?? 'Usuario' }}</td>
                                    <td class="py-3 px-4">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">{{ $fichaje->tipo }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-700">
                                        {{ \Carbon\Carbon::parse($fichaje->dia_entrada)->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4 text-gray-700">
                                        {{ $fichaje->dia_salida ? \Carbon\Carbon::parse($fichaje->dia_salida)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-900 font-semibold">{{ $duracion }}</td>
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

        <!-- Empleados section -->
        <section x-show="active === 'empleados'" x-transition x-data="empleadosModal()">
            <div class="bg-white rounded-2xl shadow p-6" x-data="empleadosModal()">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold">Empleados</h2>
                    <button @click="abrirModalNuevo()" class="px-6 py-2 rounded-lg font-semibold transition-colors"
                        style="background-color: #7BC6BF; color: white;">
                        + Nuevo Empleado
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Nombre</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Email</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Puesto</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Horas Diarias</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Teléfono</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($empleados as $empleado)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 text-gray-900">{{ $empleado->id }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-semibold">{{ $empleado->nombre }}
                                        {{ $empleado->apellidos }}</td>
                                    <td class="py-3 px-4 text-gray-700">{{ $empleado->mail }}</td>
                                    <td class="py-3 px-4 text-gray-700">{{ $empleado->puesto_trabajo }}</td>
                                    <td class="py-3 px-4 text-gray-900 font-semibold">{{ $empleado->horas_diarias }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-700">{{ $empleado->telefono }}</td>
                                    <td class="py-3 px-4 text-center space-x-2">
                                        <button @click="copiarContraseña('{{ $empleado->password }}')"
                                            class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                            Copiar contraseña
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
                <!-- Paginación empleados -->
                <div class="mt-4">
                    {{ $empleados->links() }}
                </div>

                <!-- MODAL CREAR/EDITAR EMPLEADO -->
                <div x-show="openModal"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                    @keydown.escape="cerrarModal()" x-transition>
                    <div @click.away="cerrarModal()" class="bg-white rounded-2xl shadow-lg p-8 max-w-md w-full">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold"
                                x-text="modoEdicion ? 'Editar Empleado' : 'Nuevo Empleado'"></h3>
                            <button @click="cerrarModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                                ×
                            </button>
                        </div>
                        <form @submit.prevent="guardarEmpleado()" class="space-y-4">
                            @csrf
                            <input type="hidden" x-model="formulario.id" name="id">

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
                                <input type="text" x-model="formulario.nombre" name="nombre"
                                    placeholder="Nombre del empleado" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Apellidos</label>
                                <input type="text" x-model="formulario.apellidos" name="apellidos"
                                    placeholder="Apellidos" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                                <input type="email" x-model="formulario.email" name="email"
                                    placeholder="email@ejemplo.com" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                                <input type="tel" x-model="formulario.telefono" name="telefono"
                                    placeholder="Teléfono" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">DNI</label>
                                <input type="text" x-model="formulario.dni" name="dni" placeholder="DNI"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Puesto de Trabajo</label>
                                <input type="text" x-model="formulario.puesto_trabajo" name="puesto_trabajo"
                                    placeholder="Puesto de trabajo" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Horas Diarias</label>
                                <input type="number" x-model.number="formulario.horas_diarias" name="horas_diarias"
                                    placeholder="8" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Número de Afiliación
                                    SS</label>
                                <input type="text" x-model="formulario.numero_afiliacion_ss"
                                    name="numero_afiliacion_ss" placeholder="Número de afiliación" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            </div>
                            <div class="flex gap-3 pt-4">
                                <button type="button" @click="cerrarModal()"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="flex-1 px-4 py-2 rounded-lg text-white font-semibold transition-colors"
                                    style="background-color: #7BC6BF;">
                                    <span x-text="modoEdicion ? 'Guardar Cambios' : 'Crear Empleado'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
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
                        // Laravel genera la ruta con un marcador ficticio
                        url = `{{ route('fichajes.admin.updateEmpleado', ['id' => '__ID__']) }}`;
                        // Sustituimos en JS el marcador por el id real
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

        function copiarContraseña(password) {
            navigator.clipboard.writeText(password);
        }
    </script>
</body>

</html>
