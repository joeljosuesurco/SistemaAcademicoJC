<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('curso_estudiante_gestion', function (Blueprint $table) {
            $table->unsignedBigInteger('cursos_id_curso');
            $table->unsignedBigInteger('gestiones_id_gestion');
            $table->unsignedBigInteger('estudiantes_id_estudiante');

            $table->primary(['cursos_id_curso', 'gestiones_id_gestion', 'estudiantes_id_estudiante']);

            $table->foreign('cursos_id_curso')->references('id_curso')->on('cursos')->onDelete('cascade');
            $table->foreign('gestiones_id_gestion')->references('id_gestion')->on('gestiones')->onDelete('cascade');
            $table->foreign('estudiantes_id_estudiante')->references('id_estudiante')->on('estudiantes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_estudiante_gestion');
    }
};
