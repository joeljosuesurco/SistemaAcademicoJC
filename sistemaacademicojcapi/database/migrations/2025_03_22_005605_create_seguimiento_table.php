<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seguimiento', function (Blueprint $table) {
            $table->id('id_seguimiento');
            $table->date('fecha_reg_seg');
            $table->string('asistencia', 50);
            $table->string('participacion', 50);
            $table->string('disciplina', 50);
            $table->string('puntualidad', 50);
            $table->string('respeto', 50);
            $table->string('tolerancia', 50);
            $table->string('estado_animo', 50);
            $table->string('observaciones_seguimiento', 255);

            $table->unsignedBigInteger('estudiantes_id_estudiante');
            $table->unsignedBigInteger('cursos_id_curso');
            $table->unsignedBigInteger('materias_id_materia');
            $table->unsignedBigInteger('gestiones_id_gestion');

            $table->foreign('estudiantes_id_estudiante')->references('id_estudiante')->on('estudiantes')->onDelete('cascade');
            $table->foreign('cursos_id_curso')->references('id_curso')->on('cursos')->onDelete('cascade');
            $table->foreign('materias_id_materia')->references('id_materia')->on('materias')->onDelete('cascade');
            $table->foreign('gestiones_id_gestion')->references('id_gestion')->on('gestiones')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento');
    }
};
