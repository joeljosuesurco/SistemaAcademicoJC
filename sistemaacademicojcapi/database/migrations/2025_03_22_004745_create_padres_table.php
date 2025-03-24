<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('padres', function (Blueprint $table) {
            $table->id('id_padre');
            $table->string('estado_padre', 50);
            $table->string('profesion_padre', 50)->nullable();

            $table->unsignedBigInteger('persona_rol_id_persona_rol');
            $table->foreign('persona_rol_id_persona_rol')
                ->references('id_persona_rol')
                ->on('persona_rol')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('padres');
    }
};
