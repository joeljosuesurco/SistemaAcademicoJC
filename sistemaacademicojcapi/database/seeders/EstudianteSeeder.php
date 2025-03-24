<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estudiante;
use App\Models\PersonaRol;

class EstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $rolesEstudiantes = PersonaRol::whereHas('rol', function ($q) {
            $q->where('nombre', 'estudiante');
        })->get();

        foreach ($rolesEstudiantes as $personaRol) {
            Estudiante::create([
                'estado_estudiante' => 'activo',
                'obsev_estudiante' => null,
                'persona_rol_id_persona_rol' => $personaRol->id_persona_rol,
            ]);
        }
    }
}
