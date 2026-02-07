<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel FUA Electrónico - Control de Calidad') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 shadow-sm">
                    <p class="font-bold">¡Operación Exitosa!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('warning'))
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 shadow-sm">
                    <p class="font-bold">Resultados de Validación</p>
                    <p>{{ session('warning') }}</p>
                </div>
            @endif

            <div class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-lg shadow gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-700">Total Registros: {{ $fuas->total() }}</h3>
                    <p class="text-xs text-gray-500">Página {{ $fuas->currentPage() }} de {{ $fuas->lastPage() }}</p>
                </div>

                <div class="flex gap-2">
                    <form action="{{ route('fua.validar') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-700 transition shadow">
                            ⚡ Ejecutar Reglas SIS
                        </button>
                    </form>

                    <form action="{{ route('fua.destroyAll') }}" method="POST" onsubmit="return confirm('⚠️ ¿ESTÁS SEGURO?\n\nEsto eliminará TODA la información cargada.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 transition shadow">
                            🗑️ Vaciar BD
                        </button>
                    </form>

                    <a href="{{ route('fua.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 transition shadow">
                        📥 Importar Excel
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                            <tr>
                                <th class="px-6 py-3">FUA / Fecha</th>
                                <th class="px-6 py-3">Paciente</th>
                                <th class="px-6 py-3">Servicio</th>
                                <th class="px-6 py-3 text-center w-2/5">Reporte de Reglas (Problema/Solución)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fuas as $fua)
                                @php
                                    // Definimos el color de la fila según el estado
                                    $claseFila = match($fua->estado_validacion) {
                                        2 => 'bg-red-50 border-l-4 border-red-500', // Con Errores
                                        1 => 'bg-white border-l-4 border-green-500', // Conforme
                                        default => 'bg-white border-l-4 border-gray-300' // Pendiente
                                    };
                                @endphp

                                <tr class="border-b hover:bg-gray-50 transition {{ $claseFila }}">
                                    
                                    <td class="px-6 py-4">
                                        <div class="text-base font-bold text-blue-600">#{{ $fua->fua_id }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{-- Usamos parse() por seguridad si no has actualizado el Modelo --}}
                                            {{ $fua->fecha_atencion ? \Carbon\Carbon::parse($fua->fecha_atencion)->format('d/m/Y') : '--' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">
                                            {{ $fua->apellido_paterno_paciente }} {{ $fua->apellido_materno_paciente }}
                                        </div>
                                        <div class="text-xs text-gray-600">{{ $fua->nombres_paciente }}</div>
                                        <span class="text-[10px] bg-gray-200 px-1 rounded mt-1 inline-block">
                                            Doc: {{ $fua->num_doc_paciente }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-xs font-bold uppercase">{{ $fua->servicio_descripcion }}</div>
                                        <div class="text-[10px] text-gray-500 italic">{{ $fua->nombre_profesional }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        @if($fua->estado_validacion == 1)
                                            <div class="text-center">
                                                <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full border border-green-200">
                                                    ✅ {{ $fua->observaciones_reglas ?? 'Conforme' }}
                                                </span>
                                            </div>

                                        @elseif($fua->estado_validacion == 2)
                                            <div class="flex flex-col gap-2 text-left">
                                                
                                                <div class="bg-red-100 p-2 rounded border border-red-200">
                                                    <strong class="block text-[10px] text-red-800 uppercase mb-1">⛔ Observación:</strong>
                                                    <span class="text-xs text-red-700 font-semibold">
                                                        {{ $fua->observaciones_reglas }}
                                                    </span>
                                                </div>

                                                @if($fua->soluciones_reglas)
                                                <div class="bg-blue-50 p-2 rounded border border-blue-200">
                                                    <strong class="block text-[10px] text-blue-800 uppercase mb-1">💡 Solución Sugerida:</strong>
                                                    <span class="text-xs text-blue-700">
                                                        {{ $fua->soluciones_reglas }}
                                                    </span>
                                                </div>
                                                @endif
                                            </div>

                                        @else
                                            <div class="text-center">
                                                <span class="bg-gray-100 text-gray-600 text-xs font-medium px-3 py-1 rounded-full border border-gray-300">
                                                    ⚪ Pendiente de Validación
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 bg-gray-50">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-lg font-medium">No hay registros cargados</p>
                                        <p class="text-sm">Importa un archivo Excel para comenzar.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-200">
                    {{ $fuas->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>