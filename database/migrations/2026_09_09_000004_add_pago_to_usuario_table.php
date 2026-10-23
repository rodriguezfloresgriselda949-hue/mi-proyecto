<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('usuario', 'pago')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->string('pago', 20)->nullable()->after('rol');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuario', 'pago')) {
            Schema::table('usuario', function (Blueprint $table) {
                $table->dropColumn('pago');
            });
        }
    }
};
