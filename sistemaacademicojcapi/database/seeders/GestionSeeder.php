<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gestion;
use Carbon\Carbon;

class GestionSeeder extends Seeder
{
    public function run(): void
    {
        Gestion::create([
            'nombre_gestion' => 'Gestión 2025',
            'gestion' => '2025',
            'inicio_gestion' => Carbon::create(2025, 2, 1),
            'fin_gestion' => Carbon::create(2025, 11, 30),
            'estado_gestion' => 'activa',
        ]);
    }
}
