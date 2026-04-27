<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'codigo_cliente',
        'tipo_identificacion',
        'mensualidad',
        'megas',
        'municipio',
        'vereda',
        'finca',
        'fecha_instalacion',
        'metodo_pago',
        'tasa_mora_historica',
        'n_moras_historicas',
        'n_meses_activos',
        'antiguedad_meses',
        'es_moroso',
        'dia_prom_pago_ult12',
        'moras_ult_3_meses',
        'moras_ult_6_meses',
        'racha_limpia_final',
        'probabilidad_ml',
        'nivel_riesgo_ml',
        'prediccion_actualizada_at',
    ];

    protected $casts = [
        'fecha_instalacion'         => 'date',
        'es_moroso'                 => 'boolean',
        'mensualidad'               => 'decimal:2',
        'tasa_mora_historica'       => 'decimal:4',
        'antiguedad_meses'          => 'decimal:1',
        'dia_prom_pago_ult12'       => 'decimal:1',
        'probabilidad_ml'           => 'decimal:2',
        'prediccion_actualizada_at' => 'datetime',
    ];

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'cliente_id');
    }

    public function scopeMorosos($query)
    {
        return $query->where('es_moroso', true);
    }

    public function scopePorMunicipio($query, $municipio)
    {
        return $query->where('municipio', $municipio);
    }
}