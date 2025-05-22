<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Padre;
use App\Models\PersonaRol;
use Faker\Factory as Faker;

class PadreSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        $rolesPadres = PersonaRol::whereHas('rol', function ($q) {
            $q->where('nombre', 'padre');
        })->get();

        foreach ($rolesPadres as $personaRol) {
            Padre::create([
                'estado_padre' => 'activo',
                'profesion_padre' => substr($faker->jobTitle, 0, 50),
                'persona_rol_id_persona_rol' => $personaRol->id_persona_rol,
            ]);
        }
    }
}
