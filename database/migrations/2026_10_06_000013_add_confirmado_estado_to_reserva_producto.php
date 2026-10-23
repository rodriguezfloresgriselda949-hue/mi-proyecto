<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE reserva_producto MODIFY estado ENUM('reserva', 'confirmado', 'pagado', 'entregado') NOT NULL DEFAULT 'reserva'");
    }

    public function down(): void
    {
        DB::table('reserva_producto')->where('estado', 'confirmado')->update(['estado' => 'reserva']);
        DB::statement("ALTER TABLE reserva_producto MODIFY estado ENUM('reserva', 'pagado', 'entregado') NOT NULL DEFAULT 'reserva'");
    }
};
