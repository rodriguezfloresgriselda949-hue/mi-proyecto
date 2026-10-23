<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarcaModel extends Model
{
    use HasFactory;

    protected $table = 'marca';
    protected $primaryKey = 'idmarca';
    public $timestamps = false;

    protected $fillable = [
        'nombre'
    ];

    public function productos()
    {
        return $this->hasMany(ProductoModel::class, 'idmarca', 'idmarca');
    }
}
