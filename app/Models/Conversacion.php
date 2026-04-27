<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversacion extends Model
{
    protected $table = 'conversaciones';

    protected $fillable = [
        'user_id',
        'pregunta',
        'respuesta',
        'fuente',
        'session_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}