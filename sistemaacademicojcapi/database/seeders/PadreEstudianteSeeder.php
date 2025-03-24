<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Padre;
use App\Models\Estudiante;
use App\Models\PadreEstudiante;

class PadreEstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $padres = Padre::all();
        $estudiantes = Estudiante::all();

        foreach ($estudiantes as $estudiante) {
            $padre = $padres->random();

            PadreEstudiante::create([
                'padres_id_padre' => $padre->id_padre,
                'estudiantes_id_estudiante' => $estudiante->id_estudiante,
            ]);
        }
    }
}
