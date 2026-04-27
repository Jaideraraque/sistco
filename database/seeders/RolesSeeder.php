<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Rol::create([
            'nombre'      => 'gerente',
            'descripcion' => 'Acceso completo a reportes y módulos de IA',
        ]);

        Rol::create([
            'nombre'      => 'administrativo',
            'descripcion' => 'Gestión de cartera y carga de datos',
        ]);

        Rol::create([
            'nombre'      => 'admin_sistema',
            'descripcion' => 'Administración completa del sistema',
        ]);
    }
}