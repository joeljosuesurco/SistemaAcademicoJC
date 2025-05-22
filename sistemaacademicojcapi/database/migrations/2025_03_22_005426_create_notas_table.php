<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotasTable extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id('id_nota');
            $table->string('periodo');
            $table->unsignedTinyInteger('numero_periodo');
            // Cambiado a entero
            $table->unsignedSmallInteger('nota_final')->nullable();
            $table->text('observacion')->nullable();

            $table->unsignedBigInteger('estudiantes_id_estudiante');
            $table->unsignedBigInteger('materias_id_materia');
            $table->unsignedBigInteger('cursos_id_curso');
            $table->unsignedBigInteger('gestiones_id_gestion');

            // Relaciones
            $table->foreign('estudiantes_id_estudiante')->references('id_estudiante')->on('estudiantes')->onDelete('cascade');
            $table->foreign('materias_id_materia')->references('id_materia')->on('materias')->onDelete('cascade');
            $table->foreign('cursos_id_curso')->references('id_curso')->on('cursos')->onDelete('cascade');
            $table->foreign('gestiones_id_gestion')->references('id_gestion')->on('gestiones')->onDelete('cascade');

            $table->unique([
                'estudiantes_id_estudiante',
                'materias_id_materia',
                'cursos_id_curso',
                'gestiones_id_gestion',
                'periodo',
                'numero_periodo'
            ], 'nota_unica_por_periodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
}
