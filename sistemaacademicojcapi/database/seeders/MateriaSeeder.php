<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Materia;
use App\Models\NivelEducativo;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        $inicial     = NivelEducativo::where('codigo', 'INICIAL')->first();
        $primaria    = NivelEducativo::where('codigo', 'PRIMARIA')->first();
        $secundaria  = NivelEducativo::where('codigo', 'SECUNDARIA')->first();

        $materias = [
            // INICIAL
            [
                'campo_materia' => 'Desarrollo Integral',
                'area_materia' => 'Psicomotricidad',
                'sigla_materia' => 'PSI',
                'nivel_educativo_id' => $inicial->id,
            ],
            [
                'campo_materia' => 'Comunicación',
                'area_materia' => 'Lenguaje',
                'sigla_materia' => 'LIN',
                'nivel_educativo_id' => $inicial->id,
            ],
            [
                'campo_materia' => 'Comunicación',
                'area_materia' => 'Lengua Originaria',
                'sigla_materia' => 'LOR',
                'nivel_educativo_id' => $inicial->id,
            ],
            [
                'campo_materia' => 'Vida y Medioambiente',
                'area_materia' => 'Exploración del Entorno',
                'sigla_materia' => 'EXE',
                'nivel_educativo_id' => $inicial->id,
            ],

            // PRIMARIA
            [
                'campo_materia' => 'Ciencia, Tecnología y Producción',
                'area_materia' => 'Matemática',
                'sigla_materia' => 'MAT',
                'nivel_educativo_id' => $primaria->id,
            ],
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Lenguaje y Literatura',
                'sigla_materia' => 'LYL',
                'nivel_educativo_id' => $primaria->id,
            ],
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Ciencias Sociales',
                'sigla_materia' => 'CSO',
                'nivel_educativo_id' => $primaria->id,
            ],
            [
                'campo_materia' => 'Ciencia, Tecnología y Producción',
                'area_materia' => 'Ciencias Naturales',
                'sigla_materia' => 'CNA',
                'nivel_educativo_id' => $primaria->id,
            ],
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Lengua Extranjera',
                'sigla_materia' => 'LEN',
                'nivel_educativo_id' => $primaria->id,
            ],
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Educación Física',
                'sigla_materia' => 'EFI',
                'nivel_educativo_id' => $primaria->id,
            ],
            [
                'campo_materia' => 'Ciencia, Tecnología y Producción',
                'area_materia' => 'Educación Artística',
                'sigla_materia' => 'ART',
                'nivel_educativo_id' => $primaria->id,
            ],
            [
                'campo_materia' => 'Cosmos y Pensamiento',
                'area_materia' => 'Valores y Espiritualidad',
                'sigla_materia' => 'VES',
                'nivel_educativo_id' => $primaria->id,
            ],

            // SECUNDARIA (ya visto antes, pero incluido aquí para que quede completo)
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Comunicación y Lenguajes',
                'sigla_materia' => 'LC',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Lengua Extranjera',
                'sigla_materia' => 'LE',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Ciencias Sociales',
                'sigla_materia' => 'CS',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Educación Física y Deportes',
                'sigla_materia' => 'EFD',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Educación Musical',
                'sigla_materia' => 'MUS',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Comunidad y Sociedad',
                'area_materia' => 'Artes Plásticas y Visuales',
                'sigla_materia' => 'APV',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Ciencia, Tecnología y Producción',
                'area_materia' => 'Matemáticas',
                'sigla_materia' => 'MAT',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Ciencia, Tecnología y Producción',
                'area_materia' => 'Técnica Tecnológica',
                'sigla_materia' => 'TT',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Vida, Tierra y Territorio',
                'area_materia' => 'Ciencias Naturales: Biología y Geografía',
                'sigla_materia' => 'BIO-GEO',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Vida, Tierra y Territorio',
                'area_materia' => 'Ciencias Naturales: Física',
                'sigla_materia' => 'FIS',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Vida, Tierra y Territorio',
                'area_materia' => 'Ciencias Naturales: Química',
                'sigla_materia' => 'QUI',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Cosmos y Pensamiento',
                'area_materia' => 'Cosmovisiones, Filosofía y Sociología',
                'sigla_materia' => 'CFS',
                'nivel_educativo_id' => $secundaria->id,
            ],
            [
                'campo_materia' => 'Cosmos y Pensamiento',
                'area_materia' => 'Valores, Espiritualidad y Religiones',
                'sigla_materia' => 'VER',
                'nivel_educativo_id' => $secundaria->id,
            ],
        ];

        foreach ($materias as $materia) {
            Materia::create($materia);
        }
    }
}
