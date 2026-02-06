<?php

namespace App\Imports;

use App\Models\FuaSmi;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\FuaAtencionDetallado;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SmiImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        if (!isset($row['fua'])) return null;

        // --- VALIDACIÓN ---
        $esValido = FuaAtencionDetallado::where('fua_id', $row['fua'])
                    ->where('estado_fua', 'OBSERVADO POR EL SIS')
                    ->exists();

        if (!$esValido) return null;

        return new FuaSmi([
            'fua_id'                    => $row['fua'],
            'nro_dx'                    => $row['nro_dx'],
            'cie10'                     => $row['cie10'],
            'diagnostico'               => $row['diagnostico'],
            'cod_smi'                   => $row['cod_smi'],
            'servicio_materno_infantil' => $row['servicio_materno_infantil'],
            'resultado'                 => $row['resultado'],
        ]);
    }

    public function batchSize(): int { return 2000; }
    public function chunkSize(): int { return 2000; }
}