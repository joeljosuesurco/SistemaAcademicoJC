<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('actividad_sistemas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id'); // Usuario que realizó la acción
            $table->string('accion'); // Tipo de acción: login, consulta, modificación, etc.
            $table->string('modulo')->nullable(); // Módulo afectado: estudiantes, notas, etc.
            $table->text('descripcion')->nullable(); // Detalle adicional
            $table->ipAddress('ip')->nullable(); // IP del usuario
            $table->string('navegador')->nullable(); // Agente de usuario (user agent)
            $table->timestamps();

            $table->foreign('usuario_id')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_sistemas');
    }
};
