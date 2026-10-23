<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVentaModel extends Model
{
    use HasFactory;

    protected $table = 'detalleventa';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precioventa',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precioventa' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(VentaModel::class, 'id_venta', 'id_venta');
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'id_producto', 'id_producto');
    }

    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precioventa;
    }
}
