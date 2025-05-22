<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonaRol;
use App\Models\Rol;
use App\Models\Persona;

class PersonaRolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Rol::all();
        $personas = Persona::all();

        foreach ($personas as $persona) {
            PersonaRol::create([
                'roles_id_rol' => $roles->random()->id_rol,
                'personas_id_persona' => $persona->id_persona,
            ]);
        }
    }
}
