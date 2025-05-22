<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('curso_profesor_materia_gestion', function (Blueprint $table) {
            $table->id(); // 👈 Nuevo campo ID autoincremental

            $table->unsignedBigInteger('cursos_id_curso');
            $table->unsignedBigInteger('profesores_id_profesor');
            $table->unsignedBigInteger('materias_id_materia');
            $table->unsignedBigInteger('gestiones_id_gestion');

            // Claves foráneas
            $table->foreign('cursos_id_curso')->references('id_curso')->on('cursos')->onDelete('cascade');
            $table->foreign('profesores_id_profesor')->references('id_profesor')->on('profesores')->onDelete('cascade');
            $table->foreign('materias_id_materia')->references('id_materia')->on('materias')->onDelete('cascade');
            $table->foreign('gestiones_id_gestion')->references('id_gestion')->on('gestiones')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_profesor_materia_gestion');
    }
};
