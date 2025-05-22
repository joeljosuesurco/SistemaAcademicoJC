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
                'especialidad_profesor' => $faker->randomElement(['Matemáticas', 'Lenguaje', 'Historia']),
                'estado_profesor' => 'activo',
                'titulo_provision_nacional' => $faker->randomElement(['Lic. en Educación', 'MSc. en Física', 'Técnico en Computación']),
                'rda' => strtoupper($faker->bothify('RDA-###??')),
                'cas' => strtoupper($faker->bothify('CAS-####')),
                'persona_rol_id_persona_rol' => $personaRol->id_persona_rol,
            ]);
        }
    }
}
