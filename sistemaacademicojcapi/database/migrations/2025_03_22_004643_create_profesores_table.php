<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profesores', function (Blueprint $table) {
            $table->id('id_profesor');
            $table->string('especialidad_profesor', 50);
            $table->string('estado_profesor', 50);
            $table->string('titulo_provision_nacional', 100)->nullable();
            $table->string('rda', 50)->nullable();
            $table->string('cas', 50)->nullable();

            $table->unsignedBigInteger('persona_rol_id_persona_rol');
            $table->foreign('persona_rol_id_persona_rol')
                ->references('id_persona_rol')
                ->on('persona_rol')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesores');
    }
};

