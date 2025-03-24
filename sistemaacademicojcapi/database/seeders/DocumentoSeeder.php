<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Documento;
use App\Models\Persona;
use Faker\Factory as Faker;

class DocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $personas = Persona::all();

        foreach ($personas as $persona) {
            Documento::create([
                'carnet_identidad' => $faker->numerify('#######'),
                'certificado_nacimiento' => $faker->uuid,
                'libreta_anterior' => $faker->uuid,
                'titulo_academico' => $faker->randomElement(['Licenciatura', 'Técnico', null]),
                'personas_id_persona' => $persona->id_persona,
            ]);
        }
    }
}
