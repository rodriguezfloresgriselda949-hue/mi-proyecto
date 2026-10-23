<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompraModel extends Model
{
    use HasFactory;

    protected $table = 'detalle_compra';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'precio',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(CompraModel::class, 'id_compra', 'id_compra');
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'id_producto', 'id_producto');
    }

    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio;
    }
}
