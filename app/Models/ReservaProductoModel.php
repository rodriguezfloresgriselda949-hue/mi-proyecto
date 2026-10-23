<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaProductoModel extends Model
{
    use HasFactory;

    protected $table = 'reserva_producto';
    protected $primaryKey = 'id_reserva_producto';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_producto',
        'cantidad',
        'metodo_pago',
        'estado',
        'fecha',
        'fecha_recojo',
        'hora_recojo',
        'id_venta',
    ];

    public function cliente()
    {
        return $this->belongsTo(ClienteModel::class, 'id_cliente', 'id_cliente');
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'id_producto', 'id_producto');
    }

    public function venta()
    {
        return $this->belongsTo(VentaModel::class, 'id_venta', 'id_venta');
    }
}
