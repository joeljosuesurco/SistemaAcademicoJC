<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id('id_horario');
            $table->string('dia', 30);
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->unsignedBigInteger('materias_id_materia');
            $table->unsignedBigInteger('cursos_id_curso');
            $table->unsignedBigInteger('gestiones_id_gestion');

            $table->foreign('materias_id_materia')->references('id_materia')->on('materias')->onDelete('cascade');
            $table->foreign('cursos_id_curso')->references('id_curso')->on('cursos')->onDelete('cascade');
            $table->foreign('gestiones_id_gestion')->references('id_gestion')->on('gestiones')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
