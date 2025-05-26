<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profesor;
use App\Models\PersonaRol;
use Faker\Factory as Faker;

class ProfesorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $rolesProfesores = PersonaRol::whereHas('rol', function ($q) {
            $q->where('nombre', 'profesor');
        })->get();

        foreach ($rolesProfesores as $personaRol) {
            Profesor::create([
                'especialidad_profesor' => $faker->randomElement([
                    'Matemáticas', 'Lenguaje', 'Educación Física', 'Música', 'Ciencias Naturales'
                ]),
                'estado_profesor' => 'activo', // 👈 importante
                'titulo_provision_nacional' => $faker->optional()->sentence(3),
                'rda' => $faker->optional()->numerify('RDA-####'),
                'cas' => $faker->optional()->numerify('CAS-####'),
                'persona_rol_id_persona_rol' => $personaRol->id_persona_rol,
            ]);
        }
    }
}

