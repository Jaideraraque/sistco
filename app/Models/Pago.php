<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'cliente_id',
        'mes',
        'anio',
        'estado',
        'dia_pago',
        'metodo_pago',
        'referencia',
    ];

    protected $casts = [
        'dia_pago' => 'integer',
        'anio'     => 'integer',
    ];

    // Relación: un pago pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Scope: pagos en mora
    public function scopeEnMora($query)
    {
        return $query->where('estado', 'MORA');
    }

    // Scope: pagos exitosos
    public function scopeExitosos($query)
    {
        return $query->where('estado', 'OK');
    }

    // Scope: pagos por año
    public function scopePorAnio($query, $anio)
    {
        return $query->where('anio', $anio);
    }
}