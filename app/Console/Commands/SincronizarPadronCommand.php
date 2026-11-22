<?php

namespace App\Console\Commands;

use App\Services\BeneficiariosImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SincronizarPadronCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sena:sincronizar-padron
                            {--archivo= : Ruta absoluta o relativa al archivo .xlsx, .ods o .csv}
                            {--directorio= : Directorio donde buscar automáticamente el archivo más reciente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza automáticamente el padrón oficial de beneficiarios SENA desde un archivo Excel (.xlsx), OpenDocument (.ods) o CSV';

    /**
     * Execute the console command.
     */
    public function handle(BeneficiariosImportService $importService): int
    {
        $this->info("=== Sincronización Automática del Padrón de Beneficiarios FoodPass SENA ===");

        $archivoRuta = $this->option('archivo');

        if (!$archivoRuta) {
            $directorio = $this->option('directorio') ?: storage_path('app/private/sena_padron');
            if (File::exists($directorio)) {
                $archivos = File::files($directorio);
                // Ordenar por fecha descendente
                usort($archivos, fn($a, $b) => $b->getMTime() - $a->getMTime());

                foreach ($archivos as $file) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['xlsx', 'ods', 'csv'])) {
                        $archivoRuta = $file->getRealPath();
                        break;
                    }
                }
            }
        }

        if (!$archivoRuta || !file_exists($archivoRuta)) {
            $this->error("No se encontró ningún archivo de padrón para procesar.");
            $this->line("Proporcione uno con: php artisan sena:sincronizar-padron --archivo=ruta/al/archivo.xlsx");
            return 1;
        }

        $this->info("Procesando archivo: {$archivoRuta}");

        try {
            $resultado = $importService->procesar($archivoRuta, 'Tarea Programada (Cron)');

            $this->info("✔ Proceso finalizado exitosamente.");
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Archivo Procesado', $resultado['nombre_archivo']],
                    ['Total Filas Leídas', $resultado['total_filas']],
                    ['Beneficiarios Activados', $resultado['aprendices_activados']],
                    ['Beneficiarios Inactivados', $resultado['aprendices_inactivados']],
                    ['Errores Registrados', $resultado['errores_count']],
                    ['Ejecutado Por', $resultado['ejecutado_por']],
                ]
            );

            if ($resultado['errores_count'] > 0) {
                $this->warn("Advertencia: Se presentaron {$resultado['errores_count']} errores no fatales:");
                foreach ($resultado['errores_detalle'] as $err) {
                    $this->line(" - Fila {$err['fila']}: {$err['motivo']}");
                }
            }

            return 0;
        } catch (\Throwable $e) {
            $this->error("Error crítico durante la sincronización: " . $e->getMessage());
            return 1;
        }
    }
}
