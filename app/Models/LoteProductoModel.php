<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteProductoModel extends Model
{
    use HasFactory;

    protected $table = 'lote_producto';
    protected $primaryKey = 'idlote';
    public $timestamps = false;

    protected $fillable = [
        'idproducto',
        'fecha_caducidad',
        'cantidad_producto_lote',
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'idproducto', 'id_producto');
    }
}
