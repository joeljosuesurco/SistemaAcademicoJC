<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gestion;
use Carbon\Carbon;

class Gestion2024Seeder extends Seeder
{
    public function run(): void
    {
        Gestion::firstOrCreate(
            ['gestion' => '2024'],
            [
                'nombre_gestion' => 'Gestión 2024',
                'inicio_gestion' => Carbon::create(2024, 2, 1),
                'fin_gestion' => Carbon::create(2024, 11, 30),
                'estado_gestion' => 'inactiva', // puedes cambiar a 'activa' si deseas
            ]
        );

        $this->command->info('✅ Gestión 2024 creada correctamente (si no existía).');
    }
}
