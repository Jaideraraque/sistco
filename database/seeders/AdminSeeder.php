<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Natali',
            'email'    => 'natali@uts.edu.co',
            'password' => Hash::make('Natali.1234'),
            'activo'   => 1,
        ]);
    }
}