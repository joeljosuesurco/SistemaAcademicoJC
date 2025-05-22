<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nota;
use Faker\Factory as Faker;

class DimensionNotaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Definimos los porcentajes por dimensión (suma total 100)
        $dimensiones = [
            'ser_docente'      => 5,
            'saber_docente'    => 45,
            'hacer_docente'    => 40,
            'decidir_docente'  => 5,
            'ser_autoeval'     => 5,
            'decidir_autoeval' => 5,
        ];

        // Solo notas que aún no tengan dimensiones
        $notas = Nota::doesntHave('dimensiones')->get();

        foreach ($notas as $nota) {
            $totalValor = 0;

            foreach ($dimensiones as $nombre => $porcentaje) {
                // Generar valor entero entre 0 y 100
                $valor = $faker->numberBetween(0, 100);

                $nota->dimensiones()->create([
                    'nombre_dimension' => $nombre,
                    'porcentaje'       => $porcentaje,
                    'valor_obtenido'   => $valor,
                ]);

                $totalValor += $valor;
            }

            // Actualizar nota_final con la suma entera de todas las dimensiones
            $nota->update(['nota_final' => $totalValor]);
        }
    }
}
