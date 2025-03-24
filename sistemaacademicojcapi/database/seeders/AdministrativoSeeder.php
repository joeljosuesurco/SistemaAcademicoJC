<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administrativo;
use App\Models\PersonaRol;
use Faker\Factory as Faker;

class AdministrativoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $rolesAdmin = PersonaRol::whereHas('rol', function ($q) {
            $q->where('nombre', 'administrativo');
        })->get();

        foreach ($rolesAdmin as $personaRol) {
            Administrativo::create([
                'cargo_admi' => $faker->randomElement(['Secretaría', 'Director', 'Contador']),
                'estado_admi' => 'activo',
                'persona_rol_id_persona_rol' => $personaRol->id_persona_rol,
            ]);
        }
    }
}
