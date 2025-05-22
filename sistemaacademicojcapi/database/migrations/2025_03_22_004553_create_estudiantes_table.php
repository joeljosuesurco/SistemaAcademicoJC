<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id('id_estudiante');
            //$table->string('estado_esftudiante', 50);
            $table->text('obsev_estudiante')->nullable();
            $table->string('libreta_anterior', 50)->nullable();
            $table->string('rude', 50)->unique();

            $table->unsignedBigInteger('persona_rol_id_persona_rol');
            $table->foreign('persona_rol_id_persona_rol')
                ->references('id_persona_rol')
                ->on('persona_rol')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
