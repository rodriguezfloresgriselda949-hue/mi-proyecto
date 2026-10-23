<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProveedorModel extends Model
{
    use HasFactory;

    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'nombre_empresa',
        'telefono',
        'direccion',
        'email',
    ];

    public function getNombreCompletoAttribute()
    {
        $nombre = trim($this->nombre . ' ' . $this->apellido);
        return $this->nombre_empresa ? $nombre . ' — ' . $this->nombre_empresa : $nombre;
    }

    public function compras()
    {
        return $this->hasMany(CompraModel::class, 'id_proveedor', 'id_proveedor');
    }

    public function productos()
    {
        return $this->hasMany(ProductoModel::class, 'id_proveedor', 'id_proveedor');
    }
}
