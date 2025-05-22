<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Notificacion;
use App\Models\NotificacionUsuario;

class NotificacionUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Solo si no hay datos previos
        if (NotificacionUsuario::count() === 0) {
            $usuarios = User::inRandomOrder()->take(1)->get(); // 1 usuarios aleatorios
            $notificaciones = Notificacion::inRandomOrder()->take(1)->get(); // 1 comunicados aleatorios

            foreach ($notificaciones as $notificacion) {
                foreach ($usuarios as $usuario) {
                    NotificacionUsuario::firstOrCreate([
                        'notificaciones_id_notificacion' => $notificacion->id_notificacion,
                        'users_id_user' => $usuario->id_user,
                    ], [
                        'leido' => false,
                        'fecha_envio' => now(),
                    ]);
                }
            }
        }
    }
}
