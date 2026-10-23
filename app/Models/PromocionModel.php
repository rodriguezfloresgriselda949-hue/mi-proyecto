<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromocionModel extends Model
{
    protected $table = 'promociones';
    protected $primaryKey = 'id_promocion';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'imagen', 'descripcion', 'fecha_hora_inicio', 'fecha_hora_fin', 'precio', 'cantidad_clientes',
    ];

    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
        'fecha_hora_fin' => 'datetime',
        'precio' => 'decimal:2',
        'cantidad_clientes' => 'integer',
    ];

    public function servicios()
    {
        return $this->belongsToMany(ServicioModel::class, 'promocion_servicio', 'id_promocion', 'id_servicio');
    }

    public function reservas()
    {
        return $this->hasMany(ReservaPromocionModel::class, 'id_promocion', 'id_promocion');
    }
}
