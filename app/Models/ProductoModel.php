<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoModel extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'destacado',
        'imagen',
        'stock',
        'preciocompra',
        'precioventa',
        'id_categoria',
        'idmarca',
        'id_proveedor',
        'idlote',
    ];

    protected $casts = [
        'stock' => 'integer',
        'preciocompra' => 'decimal:2',
        'precioventa' => 'decimal:2',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaModel::class, 'id_categoria', 'id_categoria');
    }

    public function marca()
    {
        return $this->belongsTo(MarcaModel::class, 'idmarca', 'idmarca');
    }

    public function lote()
    {
        return $this->belongsTo(LoteProductoModel::class, 'idlote', 'idlote');
    }

    public function proveedor()
    {
        return $this->belongsTo(ProveedorModel::class, 'id_proveedor', 'id_proveedor');
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVentaModel::class, 'id_producto', 'id_producto');
    }
}
