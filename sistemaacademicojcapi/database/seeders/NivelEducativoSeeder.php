<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NivelEducativo;

class NivelEducativoSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            ['nombre' => 'Inicial',   'codigo' => 'INICIAL'],
            ['nombre' => 'Primaria',  'codigo' => 'PRIMARIA'],
            ['nombre' => 'Secundaria','codigo' => 'SECUNDARIA'],
        ];

        foreach ($niveles as $nivel) {
            NivelEducativo::create($nivel);
        }
    }
}
