<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;
use Faker\Factory as Faker;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 20; $i++) {
            Persona::create([
                'nombres_persona' => $faker->firstName,
                'apellidos_pat' => $faker->lastName,
                'apellidos_mat' => $faker->lastName,
                'sexo_persona' => $faker->randomElement(['Masculino', 'Femenino']),
                'direccion_persona' => $faker->address,
                'nacionalidad_persona' => $faker->country,
                'celular_persona' => $faker->phoneNumber,
                'fotografia_persona' => $faker->imageUrl(200, 200, 'people', true),
            ]);
        }
    }
}
