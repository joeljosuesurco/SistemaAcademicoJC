<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PersonaRol;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $personaRoles = PersonaRol::all();

        foreach ($personaRoles as $personaRol) {
            User::create([
                'name_user' => 'usuario' . $personaRol->id_persona_rol,
                'password' => Hash::make('admin123'), // Contraseña por defecto
                'state_user' => 'activo',
                'persona_rol_id_persona_rol' => $personaRol->id_persona_rol,
            ]);
        }
    }
}
