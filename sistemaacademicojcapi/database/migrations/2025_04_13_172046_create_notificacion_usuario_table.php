<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notificacion_usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notificaciones_id_notificacion');
            $table->unsignedBigInteger('users_id_user');
            $table->boolean('leido')->default(false);
            $table->timestamp('fecha_envio')->useCurrent();
            $table->timestamp('fecha_lectura')->nullable();

            $table->foreign('notificaciones_id_notificacion')
                  ->references('id_notificacion')->on('notificaciones')
                  ->onDelete('cascade');

            $table->foreign('users_id_user')
                  ->references('id_user')->on('users')
                  ->onDelete('cascade');

            $table->unique(['notificaciones_id_notificacion', 'users_id_user'],'noti_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_usuario');
    }
};

