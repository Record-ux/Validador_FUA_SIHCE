<?php

namespace App\Services;

use App\Models\FuaAtencionDetallado;

class ReglasValidacionService
{
    public function ejecutarValidacion()
    {
        $registros = FuaAtencionDetallado::all();
        $erroresEncontrados = 0;

        foreach ($registros as $fua) {
            $listaErrores = [];
            $listaSoluciones = [];

            // --- REGLA 1: Validar DNI ---
            if ($fua->tipo_doc_paciente == '1' && strlen($fua->num_doc_paciente) != 8) {
                $listaErrores[] = 'DNI Inválido (Longitud incorrecta)';
                $listaSoluciones[] = 'Verificar ficha RENIEC y corregir a 8 dígitos.';
            }

            // --- REGLA 2: Coherencia Sexo / Ginecología ---
            if ($fua->sexo == 'M' && in_array($fua->servicio_descripcion, ['GINECOLOGIA', 'OBSTETRICIA'])) {
                $listaErrores[] = 'Paciente MASCULINO en servicio Materno';
                $listaSoluciones[] = 'Cambiar servicio a UROLOGÍA o MEDICINA GENERAL.';
            }

            // --- REGLA 3: Edad vs Pediatría (Ejemplo) ---
            if ($fua->edad > 18 && str_contains($fua->servicio_descripcion, 'PEDIATRIA')) {
                $listaErrores[] = 'Mayor de edad en PEDIATRIA';
                $listaSoluciones[] = 'Derivar a MEDICINA ADULTO.';
            }

            // --- GUARDADO DE RESULTADOS ---
            if (count($listaErrores) > 0) {
                $fua->estado_validacion = 2; // ROJO
                // Guardamos los arrays convertidos a texto separado por " | "
                $fua->observaciones_reglas = implode(' | ', $listaErrores);
                $fua->soluciones_reglas = implode(' | ', $listaSoluciones);
                $erroresEncontrados++;
            } else {
                $fua->estado_validacion = 1; // VERDE
                $fua->observaciones_reglas = 'Conforme';
                $fua->soluciones_reglas = null;
            }
            
            $fua->save();
        }

        return $erroresEncontrados;
    }
}