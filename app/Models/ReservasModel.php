<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservasModel extends Model
{
    use HasFactory;
     protected $table = 'reservas';

    protected $primaryKey = 'id_reserva';

    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_servicio',
        'id_usuario',
        'fecha',
        'hora',
        'subtotal',
        'adelanto',
        'estado'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'adelanto' => 'decimal:2',
    ];


    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(
            ClienteModel::class,
            'id_cliente',
            'id_cliente'
        );
    }


    // Relación con Servicio
    public function servicio()
    {
        return $this->belongsTo(
            ServicioModel::class,
            'id_servicio',
            'id_servicio'
        );
    }

    public function servicios()
    {
        return $this->belongsToMany(
            ServicioModel::class,
            'reserva_servicio',
            'id_reserva',
            'id_servicio',
            'id_reserva',
            'id_servicio'
        );
    }


    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(
            UsuarioModel::class,
            'id_usuario',
            'id_usuario'
        );
    }
}
