<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservaPromocionModel extends Model
{
    protected $table = 'reservas_promociones';
    protected $primaryKey = 'id_reserva_promocion';
    public $timestamps = false;

    protected $fillable = [
        'id_promocion', 'id_cliente', 'fecha', 'hora', 'precio_promocion', 'adelanto', 'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'precio_promocion' => 'decimal:2',
        'adelanto' => 'decimal:2',
    ];

    public function promocion()
    {
        return $this->belongsTo(PromocionModel::class, 'id_promocion', 'id_promocion');
    }

    public function cliente()
    {
        return $this->belongsTo(ClienteModel::class, 'id_cliente', 'id_cliente');
    }
}
