<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdelantoModel extends Model
{
    protected $table = 'adelantos';
    protected $primaryKey = 'id_adelanto';
    public $timestamps = false;

    protected $fillable = ['id_usuario', 'id_pago', 'monto', 'motivo', 'fecha'];

    public function empleado()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario', 'id_usuario');
    }
}
