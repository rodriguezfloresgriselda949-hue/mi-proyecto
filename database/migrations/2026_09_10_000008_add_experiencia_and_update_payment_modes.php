<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->unsignedTinyInteger('experiencia')->nullable()->after('pago');
        });
        // Los empleados existentes conservan un porcentaje válido hasta que el administrador lo ajuste.
        DB::table('usuario')->where('rol', 'Empleado')->whereNull('experiencia')->update(['experiencia' => 40]);
        DB::table('usuario')->where('pago', 'Semanal')->update(['pago' => 'Quincenal']);
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table) { $table->dropColumn('experiencia'); });
    }
};
