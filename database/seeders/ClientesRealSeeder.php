<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class ClientesRealSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('clientes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $archivo = database_path('seeders/data/dataset_limpio_SISTCO.csv');

        if (!file_exists($archivo)) {
            $this->command->error('No se encontró el archivo dataset_limpio_SISTCO.csv');
            return;
        }

        $municipios = [
            0 => 'Lebrija', 1 => 'Girón', 2 => 'Bucaramanga',
            3 => 'Betulia', 4 => 'Piedecuesta', 5 => 'Floridablanca',
            6 => 'Rionegro', 7 => 'Sabana de Torres',
        ];

        $megas = [
            0 => '5 Megas', 1 => '10 Megas', 2 => '20 Megas',
            3 => '30 Megas', 4 => '50 Megas', 5 => '100 Megas', 6 => '160 Megas',
        ];

        $metodos = [
            0 => 'Efectivo', 1 => 'Nequi',
            2 => 'Transferencia bancaria', 3 => 'Cajero bancario',
        ];

        $handle = fopen($archivo, 'r');

        // Leer cabecera y limpiar BOM si existe
        $cabecera = fgetcsv($handle);
        $cabecera[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cabecera[0]);
        $cabecera = array_map('trim', $cabecera);

        $count = 0;
        $errores = 0;

        while (($fila = fgetcsv($handle)) !== false) {
            if (count($fila) !== count($cabecera)) continue;

            $datos = array_combine($cabecera, $fila);

            try {
                Cliente::create([
                    'codigo_cliente'      => trim($datos['Codigo Cliente']),
                    'tipo_identificacion' => 'Cedula',
                    'mensualidad'         => floatval($datos['Mensualidad']),
                    'megas'               => $megas[(int)$datos['megas_cod']] ?? '5 Megas',
                    'municipio'           => $municipios[(int)$datos['municipio_cod']] ?? 'Lebrija',
                    'fecha_instalacion'   => now()->subMonths((int)floatval($datos['antiguedad_meses']))->format('Y-m-d'),
                    'metodo_pago'         => $metodos[(int)$datos['metodo_pago_cod']] ?? 'Efectivo',
                    'tasa_mora_historica' => floatval($datos['tasa_mora_historica']),
                    'n_moras_historicas'  => (int)$datos['n_moras_historicas'],
                    'n_meses_activos'     => (int)$datos['n_meses_activos'],
                    'antiguedad_meses'    => floatval($datos['antiguedad_meses']),
                    'es_moroso'           => (bool)(int)$datos['es_moroso'],
                    'dia_prom_pago_ult12' => floatval($datos['dia_prom_pago_ult12']),
                    'moras_ult_3_meses'   => (int)$datos['moras_ult_3_meses'],
                    'moras_ult_6_meses'   => (int)$datos['moras_ult_6_meses'],
                    'racha_limpia_final'  => (int)$datos['racha_limpia_final'],
                ]);
                $count++;
            } catch (\Exception $e) {
                $errores++;
            }
        }

        fclose($handle);
        $this->command->info("✅ $count clientes importados exitosamente.");
        if ($errores > 0) {
            $this->command->warn("⚠️ $errores filas con error omitidas.");
        }
    }
}