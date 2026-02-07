<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel FUA Electrónico - Control de Calidad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p class="font-bold">¡Operación Exitosa!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('warning'))
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Atención</p>
                    <p>{{ session('warning') }}</p>
                </div>
            @endif

            <div class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-lg shadow gap-4">
                
                <div>
                    <h3 class="text-lg font-bold text-gray-700">Registros Cargados: {{ $fuas->total() }}</h3>
                    <p class="text-xs text-gray-500">Mostrando {{ $fuas->count() }} registros por página</p>
                </div>

                <div class="flex gap-2">
                    <form action="{{ route('fua.validar') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-700 transition">
                            ⚡ Validar Reglas
                        </button>
                    </form>

                    <form action="{{ route('fua.destroyAll') }}" method="POST" onsubmit="return confirm('⚠️ ¡PELIGRO! ⚠️\n\n¿Estás seguro de que deseas ELIMINAR TODOS LOS REGISTROS?\n\nEsta acción dejará el sistema vacío.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 transition">
                            🗑️ Vaciar BD
                        </button>
                    </form>

                    <a href="{{ route('fua.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 transition">
                        📥 Importar Excel
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3">FUA / Fecha</th>
                                <th class="px-6 py-3">Paciente</th>
                                <th class="px-6 py-3">Servicio / Profesional</th>
                                <th class="px-6 py-3">Estado FUA</th>
                                <th class="px-6 py-3 text-center">Validación SIS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fuas as $fua)
                                {{-- Lógica de Colores de Fila según estado --}}
                                @php
                                    $claseBorde = '';
                                    if($fua->estado_validacion == 2) $claseBorde = 'border-l-4 border-red-500 bg-red-50';
                                    elseif($fua->estado_validacion == 1) $claseBorde = 'border-l-4 border-green-500 bg-white';
                                    else $claseBorde = 'border-l-4 border-gray-300 bg-white';
                                @endphp

                                <tr class="border-b hover:bg-gray-50 transition {{ $claseBorde }}">
                                    
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        <div class="text-base font-bold text-blue-600">#{{ $fua->fua_id }}</div>
                                        <div class="text-xs text-gray-500">{{ $fua->fecha_atencion ? $fua->fecha_atencion->format('d/m/Y') : '--' }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-800">
                                            {{ $fua->apellido_paterno_paciente }} {{ $fua->apellido_materno_paciente }},
                                        </div>
                                        <div class="text-gray-600 uppercase">{{ $fua->nombres_paciente }}</div>
                                        <span class="text-xs bg-gray-200 px-1 rounded text-gray-600">
                                            {{ $fua->tipo_doc_paciente == '1' ? 'DNI' : 'DOC' }}: {{ $fua->num_doc_paciente }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-xs font-bold text-gray-500 uppercase">Servicio</div>
                                        <div class="mb-1">{{ $fua->servicio_descripcion }}</div>
                                        <div class="text-xs font-bold text-gray-500 uppercase">Profesional</div>
                                        <div class="text-xs italic">{{ $fua->nombre_profesional }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded border border-blue-400">
                                            {{ $fua->estado_fua }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        @if($fua->estado_validacion == 0)
                                            <span class="inline-flex items-center bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-gray-700 dark:text-gray-300">
                                                <span class="w-2 h-2 mr-1 bg-gray-500 rounded-full"></span>
                                                Pendiente
                                            </span>
                                        @elseif($fua->estado_validacion == 1)
                                            <span class="inline-flex items-center bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                                                <span class="w-2 h-2 mr-1 bg-green-500 rounded-full"></span>
                                                Válido
                                            </span>
                                        @elseif($fua->estado_validacion == 2)
                                            <div class="flex flex-col items-center">
                                                <span class="inline-flex items-center bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300 mb-1">
                                                    <span class="w-2 h-2 mr-1 bg-red-500 rounded-full"></span>
                                                    Errores Detectados
                                                </span>
                                                <p class="text-xs text-red-600 font-semibold mt-1 text-left w-full bg-red-50 p-1 rounded border border-red-200">
                                                    {{ $fua->observaciones_reglas }}
                                                </p>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <p>No hay registros importados aún.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $fuas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>