<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CursoEstudianteGestion;
use App\Models\Nota;
use App\Models\DimensionNota;
use App\Models\Seguimiento;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\PersonaRol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivamos temporalmente las claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpiamos las tablas relacionadas para evitar datos duplicados o conflictos
        CursoEstudianteGestion::truncate();
        Nota::truncate();
        DimensionNota::truncate();
        Seguimiento::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ejecución de los seeders en orden lógico
        $this->call([
            RolSeeder::class,
            PersonaSeeder::class,
            PersonaRolSeeder::class,
            UserSeeder::class,
            EstudianteSeeder::class,
            ProfesorSeeder::class,
            PadreSeeder::class,
            AdministrativoSeeder::class,
            PadreEstudianteSeeder::class,
            NivelEducativoSeeder::class,
            CursoSeeder::class,
            MateriaSeeder::class,
            GestionSeeder::class,
            CursoEstudianteGestionSeeder::class,
            CursoProfesorMateriaGestionSeeder::class,
            NotaSeeder::class,
            DimensionNotaSeeder::class,
            SeguimientoSeeder::class,
            HorarioSeeder::class,
            DocumentoSeeder::class,
            NotificacionSeeder::class,
            NotificacionUsuarioSeeder::class,
            Gestion2024Seeder::class,
            CursoEstudianteGestion2024Seeder::class,
            CursoProfesorMateriaGestion2024Seeder::class,
            NotaGestion2024Seeder::class,

        ]);

                $roles = [
                'administrativo' => ['nombre' => 'Administrador', 'username' => 'admin'],
                'profesor'       => ['nombre' => 'Pedro', 'username' => 'profesor'],
                'estudiante'     => ['nombre' => 'Esteban', 'username' => 'estudiante'],
                'padre'          => ['nombre' => 'Pablo', 'username' => 'padre'],
                ];


        echo "\n=== USUARIOS CREADOS ===\n";

        foreach ($roles as $rolNombre => $data) {
            $persona = Persona::create([
                'nombres_persona' => $data['nombre'],
                'apellidos_pat' => ucfirst($rolNombre),
                'apellidos_mat' => '',
                'sexo_persona' => 'Masculino',
                'fecha_nacimiento' => '1990-01-01',
                'direccion_persona' => 'Zona Central',
                'nacionalidad_persona' => 'Boliviana',
                'celular_persona' => '7' . rand(1000000, 9999999),
                'fotografia_persona' => null,
            ]);

            $rol = Rol::where('nombre', $rolNombre)->first();
            if (!$rol) continue;

            $personaRol = PersonaRol::create([
                'roles_id_rol' => $rol->id_rol,
                'personas_id_persona' => $persona->id_persona,
            ]);

            $usuario = User::create([
                'name_user' => $data['username'],
                'password' => Hash::make('1234'),
                'state_user' => 'activo',
                'persona_rol_id_persona_rol' => $personaRol->id_persona_rol,
            ]);

            echo "Usuario: {$usuario->name_user} | Rol: $rolNombre | Contraseña: 1234\n";
        }
        echo "========================\n";
    }

}
