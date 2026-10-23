<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtencionModel extends Model
{
    use HasFactory;

    protected $table = 'atencion';
    protected $primaryKey = 'id_atencion';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_servicio',
        'id_usuario',
        'fecha',
        'hora',
        'observaciones',
        'estado',
        'total',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(ClienteModel::class, 'id_cliente', 'id_cliente');
    }

    public function servicio()
    {
        return $this->belongsTo(ServicioModel::class, 'id_servicio', 'id_servicio');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario', 'id_usuario');
    }
}
