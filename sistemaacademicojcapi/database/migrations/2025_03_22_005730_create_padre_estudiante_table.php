<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('padre_estudiante', function (Blueprint $table) {
            $table->id(); // 👈 nuevo ID autoincremental

            $table->unsignedBigInteger('padres_id_padre');
            $table->unsignedBigInteger('estudiantes_id_estudiante');

            // Relaciones
            $table->foreign('padres_id_padre')->references('id_padre')->on('padres')->onDelete('cascade');
            $table->foreign('estudiantes_id_estudiante')->references('id_estudiante')->on('estudiantes')->onDelete('cascade');

            // Índice único para evitar duplicados
            $table->unique(['padres_id_padre', 'estudiantes_id_estudiante'], 'padre_estudiante_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('padre_estudiante');
    }
};
