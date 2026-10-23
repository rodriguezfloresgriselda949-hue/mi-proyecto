<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->increments('id_pago'); $table->integer('id_usuario'); $table->date('fecha_inicio'); $table->date('fecha_fin');
            $table->decimal('total_servicios', 10, 2); $table->decimal('porcentaje', 5, 2)->default(40); $table->decimal('total_adelantos', 10, 2)->default(0); $table->decimal('monto_pagado', 10, 2); $table->date('fecha_pago');
            $table->unique(['id_usuario', 'fecha_inicio', 'fecha_fin']); $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->restrictOnDelete();
        });
        Schema::create('adelantos', function (Blueprint $table) {
            $table->increments('id_adelanto'); $table->integer('id_usuario'); $table->unsignedInteger('id_pago')->nullable(); $table->decimal('monto', 10, 2); $table->string('motivo', 255); $table->date('fecha');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->restrictOnDelete(); $table->foreign('id_pago')->references('id_pago')->on('pagos')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('adelantos'); Schema::dropIfExists('pagos'); }
};
