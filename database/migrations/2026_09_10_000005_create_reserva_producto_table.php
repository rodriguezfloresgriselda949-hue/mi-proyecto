<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reserva_producto', function (Blueprint $table) {
            $table->increments('id_reserva_producto');
            $table->integer('id_cliente');
            $table->integer('id_producto');
            $table->integer('cantidad');
            $table->enum('estado', ['reserva', 'pagado', 'entregado'])->default('reserva');
            $table->date('fecha');
            $table->integer('id_venta')->nullable()->unique();
            $table->foreign('id_cliente')->references('id_cliente')->on('clientes')->restrictOnDelete();
            $table->foreign('id_producto')->references('id_producto')->on('productos')->restrictOnDelete();
            $table->foreign('id_venta')->references('id_venta')->on('ventas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_producto');
    }
};
