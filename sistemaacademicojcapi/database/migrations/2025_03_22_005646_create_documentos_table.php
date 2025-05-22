<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id('id_documento');
            $table->string('carnet_identidad', 50);
            $table->string('certificado_nacimiento', 50);
            $table->unsignedBigInteger('personas_id_persona');
            $table->foreign('personas_id_persona')->references('id_persona')->on('personas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
