<?php

namespace App\Services;

use App\Models\FuaAtencionDetallado;

class ReglasValidacionService
{
    public function ejecutarValidacion()
    {
        // Traemos el FUA con sus relaciones para no hacer miles de consultas (Eager Loading)
        $registros = FuaAtencionDetallado::with(['consumos', 'reporte', 'diagnosticos'])
                        ->whereIn('estado_validacion', [0, 2]) // Pendientes o con errores previos
                        ->get();
        
        $erroresEncontrados = 0;

        foreach ($registros as $fua) {
            $listaErrores = [];
            $listaSoluciones = [];

            // Extraemos datos clave para procesar rápido
            // Colección de todos los códigos de insumos y medicamentos en este FUA
            $insumosFua = $fua->consumos->pluck('cod_insumo')->filter()->toArray();
            $medsFua = $fua->consumos->pluck('cod_medicamento')->filter()->toArray();
            $procsFua = $fua->consumos->pluck('cpms')->filter()->toArray(); // Procedimientos CPMS
            
            // Código Prestacional (del reporte o del detalle si lo tuvieras ahí)
            $codPrestacional = $fua->reporte ? $fua->reporte->cod_prestacional : null;


            // =================================================================================
            // GRUPO 1: REGLAS DE DISPOSITIVOS MÉDICOS (Basado en RC_94.csv)
            // =================================================================================
            
            // Regla: Si existe Kit de Ropa Descartable (54854), debe haber Nutrición Parenteral
            if (in_array('54854', $insumosFua)) {
                // Lista de medicamentos requeridos según tu RC_94
                $medsRequeridos = ['55051', '55046', '54961', '52910', '52802', '50915']; 
                
                // Verificamos si hay intersección (al menos uno coincide)
                if (empty(array_intersect($medsRequeridos, $medsFua))) {
                    $listaErrores[] = 'RC-94: Registra Kit de Ropa (54854) sin medicamento de Nutrición Parenteral asociado.';
                    $listaSoluciones[] = 'Agregar medicamento vinculado (Ej: 55051, 55046...).';
                }
            }


            // =================================================================================
            // GRUPO 2: REGLAS DE TOPES DE PROCEDIMIENTOS (Basado en RC_46.csv)
            // =================================================================================
            
            // Regla: Psicoterapia Individual (90806) - Máximo 1 por atención
            $cantidadPsico = count(array_keys($procsFua, '90806'));
            if ($cantidadPsico > 1) {
                $listaErrores[] = "RC-46: Exceso de Psicoterapia Individual (90806). Registrados: $cantidadPsico (Máx: 1).";
                $listaSoluciones[] = 'Eliminar los procedimientos duplicados 90806.';
            }

            // Regla: Sesión de psicoterapia de grupo (90849) - Máximo 1
            $cantidadPsicoGrupal = count(array_keys($procsFua, '90849'));
            if ($cantidadPsicoGrupal > 1) {
                $listaErrores[] = "RC-46: Exceso de Psicoterapia Grupal (90849). Registrados: $cantidadPsicoGrupal (Máx: 1).";
                $listaSoluciones[] = 'Dejar solo un registro del CPMS 90849.';
            }


            // =================================================================================
            // GRUPO 3: REGLAS DE CÓDIGOS PRESTACIONALES (Basado en RC_87.csv y RC_92)
            // =================================================================================
            
            // Regla: Telemedicina (300)
            if ($codPrestacional == '300') {
                // Verificamos si el tipo de atención indica telemedicina (ajusta según tu data real)
                // A veces se valida que NO sea 'Presencial' o que tenga cierto componente.
                // Aquí un ejemplo genérico:
                if (str_contains(strtoupper($fua->tipo_atencion ?? ''), 'PRESENCIAL')) {
                    $listaErrores[] = 'RC-87: Código 300 (Telemedicina) no compatible con atención Presencial.';
                    $listaSoluciones[] = 'Cambiar tipo de atención a TELECONSULTA o cambiar código prestacional.';
                }
            }
            
            // Regla: Cesárea (055) en Primer Nivel (I-1, I-2...) - (Basado en RC_92)
            // Asumimos que podemos detectar el nivel por el código EESS o si tuviéramos la categoría.
            // Ejemplo simulado: Si el EESS es un puesto de salud (suponiendo lógica de código)
            if ($codPrestacional == '055') {
                 // Aquí iría tu lógica para validar si el EESS del FUA tiene categoría válida.
                 // Como ejemplo:
                 /* if ($this->esPrimerNivel($fua->eess)) {
                     $listaErrores[] = 'RC-92: Cesárea (055) registrada en EESS de Primer Nivel.';
                 } */
            }


            // =================================================================================
            // GRUPO 4: REGLAS DE DIAGNÓSTICOS PREVENTIVOS (Basado en RC_88.csv)
            // =================================================================================
            
            // Regla: CRED (001) requiere diagnóstico Z001
            if ($codPrestacional == '001') {
                $tieneDxCred = false;
                foreach($fua->diagnosticos as $dx) {
                    if ($dx->cie10 == 'Z001') { $tieneDxCred = true; break; }
                }
                
                if (!$tieneDxCred) {
                    $listaErrores[] = 'RC-88: Control CRED (001) requiere diagnóstico Z001.';
                    $listaSoluciones[] = 'Agregar diagnóstico CIE-10 Z001 como principal o adicional.';
                }
            }


            // =================================================================================
            // GUARDADO
            // =================================================================================
            if (count($listaErrores) > 0) {
                $fua->estado_validacion = 2; // ROJO
                $fua->observaciones_reglas = implode(' | ', $listaErrores);
                $fua->soluciones_reglas = implode(' | ', $listaSoluciones);
                $erroresEncontrados++;
            } else {
                $fua->estado_validacion = 1; // VERDE
                $fua->observaciones_reglas = 'Conforme - Validación Avanzada OK';
                $fua->soluciones_reglas = null;
            }
            
            $fua->save();
        }

        return $erroresEncontrados;
    }
}