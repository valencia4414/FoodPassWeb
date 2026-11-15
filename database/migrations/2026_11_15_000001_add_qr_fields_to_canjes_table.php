<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega campos de código QR y expiración a la tabla canjes.
     * - codigo_qr: código único para cada QR generado
     * - expira_en: timestamp de expiración (siempre medianoche del día)
     * 
     * También elimina restricciones de unicidad que limitaban un canje por día.
     */
    public function up(): void
    {
        Schema::table('canjes', function (Blueprint $table): void {
            if (!Schema::hasColumn('canjes', 'codigo_qr')) {
                $table->string('codigo_qr')->nullable()->unique()->after('detalle');
            }

            if (!Schema::hasColumn('canjes', 'expira_en')) {
                $table->timestamp('expira_en')->nullable()->after('codigo_qr');
            }
        });
    }

    public function down(): void
    {
        Schema::table('canjes', function (Blueprint $table): void {
            if (Schema::hasColumn('canjes', 'codigo_qr')) {
                $table->dropColumn('codigo_qr');
            }
            if (Schema::hasColumn('canjes', 'expira_en')) {
                $table->dropColumn('expira_en');
            }
        });
    }
};
