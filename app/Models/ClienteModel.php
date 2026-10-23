<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteModel extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'email'
    ];

    public function reservas()
    {
        return $this->hasMany(ReservasModel::class, 'id_cliente', 'id_cliente');
    }

    public function ventas()
    {
        return $this->hasMany(VentaModel::class, 'id_cliente', 'id_cliente');
    }

    public function reservasProductos()
    {
        return $this->hasMany(ReservaProductoModel::class, 'id_cliente', 'id_cliente');
    }

}
