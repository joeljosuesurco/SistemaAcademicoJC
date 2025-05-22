<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('administrativos', function (Blueprint $table) {
            $table->id('id_administrativo');
            $table->string('cargo_admi', 50);
            $table->string('estado_admi', 50);

            $table->unsignedBigInteger('persona_rol_id_persona_rol');
            $table->foreign('persona_rol_id_persona_rol')
                ->references('id_persona_rol')
                ->on('persona_rol')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrativos');
    }
};
