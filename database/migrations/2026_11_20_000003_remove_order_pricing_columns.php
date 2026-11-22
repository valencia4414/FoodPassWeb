<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columnsByTable = [
        'platillos' => ['precio'],
        'detalle_pedidos' => ['precio_unitario'],
        'pedidos' => ['total', 'metodo_pago'],
    ];

    public function up(): void
    {
        foreach ($this->columnsByTable as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $existingColumns = array_filter(
                $columns,
                fn (string $column) => Schema::hasColumn($tableName, $column)
            );

            if ($existingColumns) {
                Schema::table($tableName, function (Blueprint $table) use ($existingColumns) {
                    $table->dropColumn($existingColumns);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('platillos') && !Schema::hasColumn('platillos', 'precio')) {
            Schema::table('platillos', fn (Blueprint $table) => $table->decimal('precio', 8, 2)->default(0));
        }

        if (Schema::hasTable('detalle_pedidos') && !Schema::hasColumn('detalle_pedidos', 'precio_unitario')) {
            Schema::table('detalle_pedidos', fn (Blueprint $table) => $table->decimal('precio_unitario', 10, 2)->default(0));
        }

        if (Schema::hasTable('pedidos') && !Schema::hasColumn('pedidos', 'total')) {
            Schema::table('pedidos', fn (Blueprint $table) => $table->decimal('total', 10, 2)->default(0));
        }

        if (Schema::hasTable('pedidos') && !Schema::hasColumn('pedidos', 'metodo_pago')) {
            Schema::table('pedidos', fn (Blueprint $table) => $table->enum('metodo_pago', ['efectivo', 'transferencia', 'nequi', 'tarjeta'])->nullable());
        }
    }
};
