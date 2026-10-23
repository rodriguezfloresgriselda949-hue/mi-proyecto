<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservas_promociones', function (Blueprint $table) {
            $table->increments('id_reserva_promocion');
            $table->unsignedInteger('id_promocion');
            $table->integer('id_cliente');
            $table->date('fecha');
            $table->time('hora');
            $table->decimal('precio_promocion', 10, 2);
            $table->decimal('adelanto', 10, 2)->default(0);
            $table->string('estado', 20)->default('Reservado');
            $table->foreign('id_promocion')->references('id_promocion')->on('promociones')->restrictOnDelete();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->restrictOnDelete();
            $table->index(['id_promocion', 'estado']);
            $table->index(['fecha', 'hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas_promociones');
    }
};
