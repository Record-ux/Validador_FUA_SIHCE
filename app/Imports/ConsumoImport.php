<?php

namespace App\Imports;

use App\Models\FuaConsumo;
use App\Models\FuaAtencionDetallado;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ConsumoImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        if (!isset($row['fua'])) return null;

        // --- VALIDACIÓN CONTRA LA TABLA MAESTRA ---
        // Buscamos si este FUA existe en la tabla principal Y si su estado es OBSERVADO.
        // Si no existe (o tiene otro estado), ignoramos esta fila de consumo.
        $esValido = FuaAtencionDetallado::where('fua_id', $row['fua'])
                    ->where('estado_fua', 'OBSERVADO POR EL SIS')
                    ->exists();

        if (!$esValido) {
            return null;
        }

        return new FuaConsumo([
            'fua_id'                  => $row['fua'],
            'beneficiario'            => $row['beneficiario'],
            'historia_clinica'        => $row['historia_clinica'],
            'id_servicio'             => $row['servicio'],
            'contrato'                => $row['contrato'],
            'nro_dx'                  => $row['nro_dx'],
            'tipo_dx'                 => $row['tipo_dx'],
            'cie10'                   => $row['cie10'],
            'diagnostico'             => $row['diagnostico'],
            
            // Procedimientos
            'cpms'                    => $row['cpms'] ?? null,
            'descripcion_procedimiento'=> $row['descripcion_procedimiento'] ?? null,
            'proc_cant_indicada'      => $row['proc_cant_indicada'] ?? null,
            'proc_cant_entregada'     => $row['proc_cant_entregada'] ?? null,
            'resultado'               => $row['resultado'] ?? null,
            
            // Medicamentos
            'cod_medicamento'         => $row['cod_medicamento'] ?? null,
            'descripcion_medicamento' => $row['descripcion_medicamento'] ?? null,
            'med_cant_prescrita'      => $row['med_cant_prescrita'] ?? null,
            'med_cant_entregada'      => $row['med_cant_entregada'] ?? null,
            
            // Insumos
            'cod_insumo'              => $row['codigo_insumo'] ?? null, // Verifica si es 'codigo_insumo' o 'cod_insumo'
            'descripcion_insumo'      => $row['descripcion_insumo'] ?? null,
            'ins_cant_prescrita'      => $row['ins_cant_prescrita'] ?? null,
            'ins_cant_entregada'      => $row['ins_cant_entregada'] ?? null,
            
            'gestante'                => $row['gestante'],
            'estado_fua'              => $row['estado_fua'],
        ]);
    }

    public function batchSize(): int { return 1000; }
    public function chunkSize(): int { return 1000; }
}