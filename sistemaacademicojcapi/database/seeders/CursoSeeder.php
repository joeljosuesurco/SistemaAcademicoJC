<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curso;
use App\Models\NivelEducativo;

class CursoSeeder extends Seeder
{
    public function run(): void
    {
        $inicial = NivelEducativo::where('codigo', 'INICIAL')->first();
        $primaria = NivelEducativo::where('codigo', 'PRIMARIA')->first();
        $secundaria = NivelEducativo::where('codigo', 'SECUNDARIA')->first();

        $cursos = [
            [
                'grado_curso' => 'PRIMERO',
                'paralelo_curso' => 'A',
                'nivel_educativo_id' => $inicial->id,
                'aula_curso' => 'Aula 343',
                'turno_curso' => 'Tarde',
                'descripcion' => 'Curso para inicial A',
            ],
            [
                'grado_curso' => 'SEGUNDO',
                'paralelo_curso' => 'B',
                'nivel_educativo_id' => $primaria->id,
                'aula_curso' => 'Aula 102',
                'turno_curso' => 'Mañana',
                'descripcion' => 'Segundo grado paralelo B',
            ],
            [
                'grado_curso' => 'TERCERO',
                'paralelo_curso' => 'C',
                'nivel_educativo_id' => $secundaria->id,
                'aula_curso' => 'Aula 305',
                'turno_curso' => 'Tarde',
                'descripcion' => 'Curso para tercer año de secundaria',
            ],
        ];

        foreach ($cursos as $curso) {
            Curso::create($curso);
        }
    }
}
