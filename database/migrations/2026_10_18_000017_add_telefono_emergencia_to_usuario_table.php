<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('usuario', 'telefono_emergencia')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->string('telefono_emergencia', 20)->nullable()->after('telefono');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuario', 'telefono_emergencia')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('telefono_emergencia');
            });
        }
    }
};
