<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nivel_educativos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');   // Ej: "Inicial", "Primaria"
            $table->string('codigo');   // Ej: "INICIAL", "PRIMARIA"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nivel_educativos');
    }
};
