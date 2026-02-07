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
}
