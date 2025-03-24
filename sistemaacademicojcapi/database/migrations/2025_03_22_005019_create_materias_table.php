<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id('id_materia');

            $table->string('area_materia', 100);
            $table->string('nombre_materia', 100);
            $table->string('sigla_materia', 20);
            $table->string('estado_materia', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
