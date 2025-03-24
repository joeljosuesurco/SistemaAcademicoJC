<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gestiones', function (Blueprint $table) {
            $table->id('id_gestion');
            $table->string('nombre_gestion', 50);
            $table->string('gestion', 50); // Antes era tipo fecha, ahora usamos texto
            $table->date('inicio_gestion');
            $table->date('fin_gestion');
            $table->string('estado_gestion', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gestiones');
    }
};
