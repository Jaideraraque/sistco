<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargaDatos extends Model
{
    protected $table = 'cargas_datos';

    protected $fillable = [
        'user_id',
        'nombre_archivo',
        'total_clientes',
        'estado',
        'modelos_estado',
        'mensaje',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}