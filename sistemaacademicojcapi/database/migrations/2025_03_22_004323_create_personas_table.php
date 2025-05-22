<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id('id_persona'); // ¡IMPORTANTE! Usa 'id_persona' como PK
            $table->string('nombres_persona', 100);
            $table->string('apellidos_pat', 100);
            $table->string('apellidos_mat', 100);
            $table->string('sexo_persona', 100);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('direccion_persona', 100);
            $table->string('nacionalidad_persona', 100);
            $table->string('celular_persona', 100)->nullable();
            $table->string('fotografia_persona', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
