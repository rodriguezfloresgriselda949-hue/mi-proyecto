<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('especialidades', function (Blueprint $table) {
            $table->increments('id_especialidad');
            $table->integer('id_usuario');
            $table->integer('id_servicio');
            $table->unique(['id_usuario', 'id_servicio']);
            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->onDelete('cascade');
            $table->foreign('id_servicio')->references('id_servicio')->on('servicios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidades');
    }
};
