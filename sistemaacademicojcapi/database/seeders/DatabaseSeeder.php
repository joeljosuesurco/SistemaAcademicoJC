<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CursoEstudianteGestion;
use App\Models\Nota;
use App\Models\DimensionNota;
use App\Models\Seguimiento;

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
        ]);
    }
}
