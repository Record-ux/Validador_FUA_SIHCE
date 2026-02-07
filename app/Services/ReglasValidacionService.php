<?php

namespace App\Services;

use App\Models\FuaAtencionDetallado;

class ReglasValidacionService
{
    public function ejecutarValidacion()
    {
        // 1. Obtener todos los FUAs cargados
        $registros = FuaAtencionDetallado::all();
        
        $contadorErrores = 0;

        foreach ($registros as $fua) {
            $errores = [];

            // --- REGLA 1: Coherencia Sexo / Servicio (Ejemplo) ---
            // Si es Masculino y el servicio es Ginecología u Obstetricia
            if ($fua->sexo == 'M' && in_array($fua->servicio_descripcion, ['GINECOLOGIA', 'OBSTETRICIA'])) {
                $errores[] = 'Error Normativo: Paciente MASCULINO no puede tener atenciones en GINECOLOGIA.';
            }

            // --- REGLA 2: Edad vs Pediatría ---
            // Si tiene más de 18 años y se atendió en pediatría
            if ($fua->edad > 18 && str_contains($fua->servicio_descripcion, 'PEDIATRIA')) {
                $errores[] = 'Error Normativo: Paciente MAYOR DE EDAD atendido en PEDIATRIA.';
            }

            // --- REGLA 3: Validar DNI (Largo) ---
            if ($fua->tipo_doc_paciente == '1' && strlen($fua->num_doc_paciente) != 8) {
                 $errores[] = 'Error RENIEC: El DNI debe tener 8 dígitos.';
            }

            // --- GUARDAR RESULTADO ---
            if (count($errores) > 0) {
                $fua->estado_validacion = 2; // Con Errores
                $fua->observaciones_reglas = implode(' | ', $errores);
                $contadorErrores++;
            } else {
                $fua->estado_validacion = 1; // Válido
                $fua->observaciones_reglas = null;
            }
            
            $fua->save();
        }

        return $contadorErrores;
    }
}