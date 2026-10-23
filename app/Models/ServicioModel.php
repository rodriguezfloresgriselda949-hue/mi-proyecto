<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioModel extends Model
{
    use HasFactory;
    protected $table = 'servicios';

    protected $primaryKey = 'id_servicio';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'imagen',
    ];

    protected $casts = [
        'precio' => 'decimal:2'
    ];

    public function especialistas()
    {
        return $this->belongsToMany(UsuarioModel::class, 'especialidades', 'id_servicio', 'id_usuario');
    }
}
