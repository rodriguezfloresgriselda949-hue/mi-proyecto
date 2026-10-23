<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('productos', 'id_proveedor')) {
            Schema::table('productos', function (Blueprint $table) {
                // La tabla histórica usa INT con signo; debe coincidir con proveedores.id_proveedor.
                $table->integer('id_proveedor')->nullable()->after('idmarca');
            });
        } else {
            DB::statement('ALTER TABLE productos MODIFY id_proveedor INT NULL');
        }
        Schema::table('productos', function (Blueprint $table) {
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores')->nullOnDelete();
        });

        // Asigna un proveedor existente a productos históricos cuando sea posible.
        $proveedorInicial = DB::table('proveedores')->min('id_proveedor');
        if ($proveedorInicial) {
            DB::table('productos')->whereNull('id_proveedor')->update(['id_proveedor' => $proveedorInicial]);
        }

        Schema::create('reserva_servicio', function (Blueprint $table) {
            $table->integer('id_reserva');
            $table->integer('id_servicio');
            $table->primary(['id_reserva', 'id_servicio']);
            $table->foreign('id_reserva')->references('id_reserva')->on('reservas')->cascadeOnDelete();
            $table->foreign('id_servicio')->references('id_servicio')->on('servicios')->restrictOnDelete();
        });

        DB::table('reserva_servicio')->insertUsing(
            ['id_reserva', 'id_servicio'],
            DB::table('reservas')->select('id_reserva', 'id_servicio')->whereNotNull('id_servicio')
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_servicio');
        Schema::table('productos', function (Blueprint $table) { $table->dropForeign(['id_proveedor']); $table->dropColumn('id_proveedor'); });
    }
};
