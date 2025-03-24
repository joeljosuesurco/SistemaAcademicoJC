<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('persona_rol', function (Blueprint $table) {
            $table->id('id_persona_rol');

            $table->unsignedBigInteger('roles_id_rol');
            $table->unsignedBigInteger('personas_id_persona');

            $table->foreign('roles_id_rol')
                ->references('id_rol')
                ->on('roles')
                ->onDelete('cascade');

            $table->foreign('personas_id_persona')
                ->references('id_persona')
                ->on('personas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona_rol');
    }
};
