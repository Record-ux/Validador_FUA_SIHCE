<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuaAtencionDetallado extends Model
{
    protected $table = 'fua_atencion_detallado'; 
    
    // Deshabilitamos la protección para poder hacer inserciones masivas rápidas
    protected $guarded = [];

    // --- AGREGA ESTO PARA CORREGIR EL ERROR ---
    protected $casts = [
        'fecha_atencion'            => 'datetime',
        'fecha_nacimiento_paciente' => 'date',
        'fecha_registro'            => 'date',
        'fecha_probable_parto'      => 'date',
        'fecha_parto'               => 'date',
        'fecha_ingreso'             => 'datetime',
        'fecha_envio_sis'           => 'datetime',

    ];

    // 1. Relación con Diagnósticos (Tabla Principal/Adicional)
    public function diagnosticos()
    {
        return $this->hasMany(FuaPrincipalAdicional::class, 'nro_formato', 'fua_id');
    }

    // 2. Relación con Consumo (Medicamentos, Insumos, Procedimientos)
    public function consumos()
    {
        return $this->hasMany(FuaConsumo::class, 'fua_id', 'fua_id');
    }

    // 3. Relación con Reporte Estado (Para ver el Código Prestacional real: 056, 906, etc)
    public function reporte()
    {
        return $this->hasOne(FuaReporteEstado::class, 'fua_id', 'fua_id');
    }
}
