<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->string('titulo_notificacion', 255);
            $table->string('mensaje_notificacion', 255);
            $table->date('fecha_notificacion');
            $table->string('estado_notificacion', 50);
            $table->string('tipo_notificacion', 50);

            $table->unsignedBigInteger('users_id_user');
            $table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
