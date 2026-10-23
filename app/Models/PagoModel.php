<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoModel extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';
    public $timestamps = false;

    protected $fillable = ['id_usuario', 'fecha_inicio', 'fecha_fin', 'total_servicios', 'porcentaje', 'total_adelantos', 'monto_pagado', 'fecha_pago'];

    public function empleado()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario', 'id_usuario');
    }
}
