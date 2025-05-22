<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id('id_materia');

            $table->string('campo_materia', 100);      // 🆕 Campo de saber
            $table->string('area_materia', 100);
            //$table->string('nombre_materia', 100);
            $table->string('sigla_materia', 20);
            //$table->string('estado_materia', 50);

            $table->unsignedBigInteger('nivel_educativo_id');
            $table->foreign('nivel_educativo_id')
                  ->references('id')->on('nivel_educativos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
