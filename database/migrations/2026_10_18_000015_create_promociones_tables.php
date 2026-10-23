<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promociones', function (Blueprint $table) {
            $table->increments('id_promocion');
            $table->string('nombre', 100);
            $table->string('imagen')->nullable();
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin');
            $table->decimal('precio', 10, 2);
            $table->unsignedInteger('cantidad_clientes');
        });

        Schema::create('promocion_servicio', function (Blueprint $table) {
            $table->unsignedInteger('id_promocion');
            $table->integer('id_servicio');
            $table->primary(['id_promocion', 'id_servicio']);
            $table->foreign('id_promocion')->references('id_promocion')->on('promociones')->cascadeOnDelete();
            $table->foreign('id_servicio')->references('id_servicio')->on('servicios')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion_servicio');
        Schema::dropIfExists('promociones');
    }
};
