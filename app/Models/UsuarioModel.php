<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioModel extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Configuración de la tabla
    |--------------------------------------------------------------------------
    */

    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;


    /*
    |--------------------------------------------------------------------------
    | Campos permitidos para INSERT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'username',
        'email',
        'telefono',
        'telefono_emergencia',
        'password',
        'rol',
        'pago',
        'experiencia',
        'foto',
        'estado',
    ];


    /*
    |--------------------------------------------------------------------------
    | Ocultar contraseña
    |--------------------------------------------------------------------------
    */

    protected $hidden = [
        'password',
    ];

    public function especialidades()
    {
        return $this->belongsToMany(ServicioModel::class, 'especialidades', 'id_usuario', 'id_servicio');
    }
}
