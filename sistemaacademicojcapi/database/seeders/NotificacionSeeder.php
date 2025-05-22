<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notificacion;
use App\Models\User;
use Faker\Factory as Faker;

class NotificacionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $users = User::all();

        foreach ($users as $user) {
            Notificacion::create([
                'titulo_notificacion' => $faker->sentence(6),
                'mensaje_notificacion' => $faker->text(255), // 👈 limitar texto
                'fecha_notificacion' => $faker->date(),
                'estado_notificacion' => $faker->randomElement(['leído', 'pendiente']),
                'tipo_notificacion' => $faker->randomElement(['sistema', 'alerta']),
                'users_id_user' => $user->id_user,
            ]);
        }
    }
}
