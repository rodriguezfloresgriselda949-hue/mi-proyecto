<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('productos', 'color')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('color', 50)->nullable()->after('descripcion');
            });
        }

        if (!Schema::hasColumn('productos', 'imagen')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('imagen')->nullable()->after('color');
            });
        }
    }
    public function down(): void {
        $columnas = array_filter(['color', 'imagen'], fn ($columna) => Schema::hasColumn('productos', $columna));

        if ($columnas) {
            Schema::table('productos', function (Blueprint $table) use ($columnas) {
                $table->dropColumn($columnas);
            });
        }
    }
};
