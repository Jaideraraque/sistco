<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación: un rol tiene muchos usuarios
    public function usuarios()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}