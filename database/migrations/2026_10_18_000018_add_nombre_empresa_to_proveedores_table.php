<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('proveedores', 'nombre_empresa')) {
            Schema::table('proveedores', function (Blueprint $table) {
                $table->string('nombre_empresa', 150)->nullable()->after('apellido');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('proveedores', 'nombre_empresa')) {
            Schema::table('proveedores', function (Blueprint $table) {
                $table->dropColumn('nombre_empresa');
            });
        }
    }
};
