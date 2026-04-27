<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use Illuminate\Support\Facades\Http;

class CalcularPredicciones extends Command
{
    protected $signature   = 'ml:calcular-predicciones';
    protected $description = 'Calcula predicciones ML para todos los clientes y las guarda en la BD';

    public function handle()
    {
        $this->info('Calculando predicciones para todos los clientes...');

        $clientes = Cliente::all();
        $total    = $clientes->count();
        $bar      = $this->output->createProgressBar($total);
        $bar->start();

        $exitosos = 0;
        $errores  = 0;

        foreach ($clientes as $cliente) {
            try {
                $response = Http::timeout(5)
                    ->post(env('ML_API_URL') . '/clasificar/cliente', [
                        'mensualidad'         => (float) $cliente->mensualidad,
                        'antiguedad_meses'    => (float) $cliente->antiguedad_meses,
                        'megas_cod'           => (int)   ($cliente->megas_cod ?? 1),
                        'municipio_cod'       => (int)   ($cliente->municipio_cod ?? 1),
                        'metodo_pago_cod'     => (int)   ($cliente->metodo_pago_cod ?? 1),
                        'n_meses_activos'     => (int)   $cliente->n_meses_activos,
                        'n_moras_historicas'  => (int)   $cliente->n_moras_historicas,
                        'tasa_mora_historica' => (float) $cliente->tasa_mora_historica,
                        'moras_ult_3_meses'   => (int)   $cliente->moras_ult_3_meses,
                        'moras_ult_6_meses'   => (int)   $cliente->moras_ult_6_meses,
                        'racha_limpia_final'  => (int)   $cliente->racha_limpia_final,
                        'dia_prom_pago_ult12' => (float) $cliente->dia_prom_pago_ult12,
                    ]);

                if ($response->ok()) {
                    $data = $response->json();
                    $cliente->update([
                        'probabilidad_ml'           => $data['probabilidad'],
                        'nivel_riesgo_ml'            => $data['nivel_riesgo'],
                        'prediccion_actualizada_at'  => now(),
                    ]);
                    $exitosos++;
                }
            } catch (\Exception $e) {
                $errores++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Completado: {$exitosos} predicciones guardadas, {$errores} errores.");

        return Command::SUCCESS;
    }
}