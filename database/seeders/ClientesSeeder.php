<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClientesSeeder extends Seeder
{
    public function run(): void
    {
        $municipios = ['Lebrija', 'Girón', 'Bucaramanga', 'Betulia', 'Piedecuesta', 'Floridablanca', 'Rionegro', 'Sabana de Torres'];
        $megas = ['5 Megas', '10 Megas', '20 Megas', '30 Megas', '50 Megas'];
        $metodos = ['Nequi', 'Transferencia bancaria', 'Cajero bancario', 'Efectivo'];

        for ($i = 1; $i <= 20; $i++) {
            Cliente::create([
                'codigo_cliente'       => $i,
                'tipo_identificacion'  => 'Cedula',
                'mensualidad'          => collect([50000, 80000, 100000, 120000, 150000])->random(),
                'megas'                => collect($megas)->random(),
                'municipio'            => collect($municipios)->random(),
                'vereda'               => 'Vereda ' . $i,
                'finca'                => 'Finca ' . $i,
                'fecha_instalacion'    => now()->subMonths(rand(1, 86))->format('Y-m-d'),
                'metodo_pago'          => collect($metodos)->random(),
                'tasa_mora_historica'  => round(rand(0, 15) / 100, 4),
                'n_moras_historicas'   => rand(0, 5),
                'n_meses_activos'      => rand(6, 86),
                'antiguedad_meses'     => rand(1, 86),
                'es_moroso'            => rand(0, 1),
                'dia_prom_pago_ult12'  => rand(1, 28),
                'moras_ult_3_meses'    => rand(0, 3),
                'moras_ult_6_meses'    => rand(0, 6),
                'racha_limpia_final'   => rand(0, 12),
            ]);
        }
    }
}