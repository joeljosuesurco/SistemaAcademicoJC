<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id('id_curso');

            $table->string('grado_curso', 50);        // Ej: "Primero", "Segundo"
            $table->string('paralelo_curso', 10);      // Ej: "A", "B"

            // Reemplazo de nivel_curso
            $table->unsignedBigInteger('nivel_educativo_id');
            $table->foreign('nivel_educativo_id')
                  ->references('id')->on('nivel_educativos')
                  ->onDelete('cascade');

            $table->string('aula_curso', 50);          // Ej: "Aula 101"
            $table->string('turno_curso', 20);         // Ej: "Mañana", "Tarde", "Noche"
            $table->text('descripcion')->nullable();   // Opcional
            $table->string('estado')->default('activo'); // Aquí mismo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
