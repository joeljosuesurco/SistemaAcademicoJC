<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id('id_curso');

            $table->string('grado_curso', 50);
            $table->string('paralelo_curso', 10);
            $table->string('nivel_curso', 50);
            $table->string('aula_curso', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
