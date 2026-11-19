<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'telefono')) {
                $table->string('telefono')->nullable();
            }
            if (!Schema::hasColumn('usuarios', 'direccion')) {
                $table->string('direccion')->nullable();
            }
            if (!Schema::hasColumn('usuarios', 'idioma_preferido')) {
                $table->string('idioma_preferido')->default('es');
            }
            if (!Schema::hasColumn('usuarios', 'foto_perfil')) {
                $table->string('foto_perfil')->nullable();
            }
            if (!Schema::hasColumn('usuarios', 'membresia')) {
                $table->string('membresia')->default('Básica');
            }
            if (!Schema::hasColumn('usuarios', 'fecha_renovacion_membresia')) {
                $table->timestamp('fecha_renovacion_membresia')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $columns = [
                'telefono',
                'direccion',
                'idioma_preferido',
                'foto_perfil',
                'membresia',
                'fecha_renovacion_membresia',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('usuarios', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
