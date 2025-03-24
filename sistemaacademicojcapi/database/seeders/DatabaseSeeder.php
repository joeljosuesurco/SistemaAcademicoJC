<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
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
            CursoSeeder::class,
            MateriaSeeder::class,
            GestionSeeder::class,
            CursoEstudianteGestionSeeder::class,
            CursoProfesorMateriaGestionSeeder::class,
            NotaSeeder::class,
            SeguimientoSeeder::class,
            HorarioSeeder::class,
            DocumentoSeeder::class,
            NotificacionSeeder::class,
         ]);
    }
}
