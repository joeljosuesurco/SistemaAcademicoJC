<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDimensionesNotaTable extends Migration
{
    public function up(): void
    {
        Schema::create('dimensiones_nota', function (Blueprint $table) {
            $table->id('id_dimension_nota');
            $table->string('nombre_dimension');
            // Cambiado a entero
            $table->unsignedTinyInteger('porcentaje');
            $table->unsignedTinyInteger('valor_obtenido')->nullable();
            $table->unsignedBigInteger('notas_id_nota');

            $table->foreign('notas_id_nota')->references('id_nota')->on('notas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dimensiones_nota');
    }
}
