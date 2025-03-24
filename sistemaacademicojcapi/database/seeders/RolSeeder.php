<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['estudiante', 'profesor', 'padre', 'administrativo'];

        foreach ($roles as $nombre) {
            Rol::create(['nombre' => $nombre]);
        }
    }
}
