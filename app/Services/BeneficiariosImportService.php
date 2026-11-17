<?php

namespace App\Services;

use App\Models\BeneficiarioSena;
use App\Models\CargaBeneficiario;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use SimpleXMLElement;
use DOMDocument;
use Exception;

class BeneficiariosImportService
{
    /**
     * Procesa la carga masiva desde un archivo subido por HTTP o desde el filesystem local (CLI/Cron).
     *
     * @param UploadedFile|string $archivo Objeto UploadedFile o ruta absoluta al archivo
     * @param string $ejecutadoPor Nombre del usuario o 'Tarea Programada (Cron)'
     * @return array Resumen del proceso
     */
    public function procesar($archivo, string $ejecutadoPor = 'Sistema'): array
    {
        $nombreOriginal = '';
        $rutaTemporal = '';
        $extension = '';

        if ($archivo instanceof UploadedFile) {
            $nombreOriginal = $archivo->getClientOriginalName();
            $extension = strtolower($archivo->getClientOriginalExtension());
            $rutaTemporal = $archivo->getRealPath();
        } elseif (is_string($archivo) && file_exists($archivo)) {
            $nombreOriginal = basename($archivo);
            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            $rutaTemporal = $archivo;
        } else {
            throw new Exception("El archivo especificado no existe o no es válido.");
        }

        // Validar extensión permitida
        $formatosValidos = ['xlsx', 'ods', 'csv', 'txt'];
        if (!in_array($extension, $formatosValidos)) {
            throw new Exception("Formato no soportado (.{$extension}). Los formatos admitidos son: Excel (.xlsx), OpenDocument (.ods) y texto (.csv).");
        }

        // Guardar copia en almacenamiento privado seguro (no accesible vía web)
        $directorioPrivado = 'private/sena_padron';
        if (!Storage::disk('local')->exists($directorioPrivado)) {
            Storage::disk('local')->makeDirectory($directorioPrivado);
        }
        $nombreGuardado = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $nombreOriginal);
        $rutaPrivada = $directorioPrivado . '/' . $nombreGuardado;
        Storage::disk('local')->put($rutaPrivada, file_get_contents($rutaTemporal));

        // Extraer filas tabulares del archivo según su formato
        $filas = $this->extraerFilas($rutaTemporal, $extension);
        $totalFilasLeidas = count($filas);

        if ($totalFilasLeidas === 0) {
            throw new Exception("El archivo '{$nombreOriginal}' está vacío o no contiene filas tabulares legibles.");
        }

        // Mapear cabeceras y procesar registros
        $resultado = $this->sincronizarPadron($filas, $nombreOriginal, $rutaPrivada, $extension, $ejecutadoPor);

        return $resultado;
    }

    /**
     * Extrae filas tabulares de un archivo según la extensión.
     */
    protected function extraerFilas(string $ruta, string $extension): array
    {
        switch ($extension) {
            case 'xlsx':
                return $this->extraerFilasXlsx($ruta);
            case 'ods':
                return $this->extraerFilasOds($ruta);
            case 'csv':
            case 'txt':
                return $this->extraerFilasCsv($ruta);
            default:
                return [];
        }
    }

    /**
     * Parser nativo OpenXML para hojas de cálculo Excel (.xlsx).
     */
    protected function extraerFilasXlsx(string $ruta): array
    {
        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) {
            throw new Exception("No fue posible abrir el archivo Excel (.xlsx). Puede estar corrupto o protegido.");
        }

        // 1. Cargar cadenas compartidas (sharedStrings.xml)
        $sharedStrings = [];
        $xmlStrings = $zip->getFromName('xl/sharedStrings.xml');
        if ($xmlStrings) {
            $xml = @simplexml_load_string($xmlStrings);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $textoCompleto = '';
                        foreach ($si->r as $r) {
                            $textoCompleto .= (string) $r->t;
                        }
                        $sharedStrings[] = $textoCompleto;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Buscar la primera hoja de cálculo
        $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXmlContent) {
            // Buscar cualquier hoja
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (preg_match('/^xl\/worksheets\/sheet\d+\.xml$/i', $stat['name'])) {
                    $sheetXmlContent = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if (!$sheetXmlContent) {
            throw new Exception("No se encontró ninguna hoja de cálculo válida en el archivo .xlsx.");
        }

        $sheetXml = @simplexml_load_string($sheetXmlContent);
        if (!$sheetXml || !isset($sheetXml->sheetData->row)) {
            return [];
        }

        $filas = [];
        foreach ($sheetXml->sheetData->row as $row) {
            $filaActual = [];
            $ultimoIndiceCol = -1;

            foreach ($row->c as $cell) {
                $ref = (string) $cell['r']; // e.g. "A1", "C2"
                $colIndex = $this->coloniaLetraAIndice($ref);

                // Rellenar celdas vacías intermedias
                while ($ultimoIndiceCol < $colIndex - 1) {
                    $ultimoIndiceCol++;
                    $filaActual[$ultimoIndiceCol] = '';
                }
                $ultimoIndiceCol = $colIndex;

                $tipo = (string) $cell['t'];
                $valor = '';

                if ($tipo === 's') {
                    $idx = (int) $cell->v;
                    $valor = $sharedStrings[$idx] ?? '';
                } elseif ($tipo === 'inlineStr' && isset($cell->is->t)) {
                    $valor = (string) $cell->is->t;
                } else {
                    $valor = isset($cell->v) ? (string) $cell->v : '';
                }

                $filaActual[$colIndex] = trim($valor);
            }

            if (!empty(array_filter($filaActual, fn($v) => $v !== ''))) {
                $filas[] = array_values($filaActual);
            }
        }

        return $filas;
    }

    /**
     * Convierte la referencia de columna de celda (ej: 'C5' -> 2) a índice entero 0-based.
     */
    protected function coloniaLetraAIndice(string $referenciaCelda): int
    {
        $letras = preg_replace('/[0-9]/', '', $referenciaCelda);
        $letras = strtoupper($letras);
        $len = strlen($letras);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($letras[$i]) - ord('A') + 1);
        }
        return max(0, $num - 1);
    }

    /**
     * Parser nativo OASIS OpenDocument Spreadsheet (.ods).
     */
    protected function extraerFilasOds(string $ruta): array
    {
        $zip = new ZipArchive();
        if ($zip->open($ruta) !== true) {
            throw new Exception("No fue posible abrir el archivo OpenDocument (.ods).");
        }

        $contentXml = $zip->getFromName('content.xml');
        $zip->close();

        if (!$contentXml) {
            throw new Exception("El archivo .ods no contiene content.xml.");
        }

        $dom = new DOMDocument();
        @$dom->loadXML($contentXml);

        $tables = $dom->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'table');
        if ($tables->length === 0) {
            return [];
        }

        $filas = [];
        $rows = $tables->item(0)->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'table-row');

        foreach ($rows as $row) {
            $filaActual = [];
            $cells = $row->getElementsByTagNameNS('urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'table-cell');

            foreach ($cells as $cell) {
                $repeat = (int) $cell->getAttributeNS('urn:oasis:names:tc:opendocument:xmlns:table:1.0', 'number-columns-repeated');
                if ($repeat <= 0) {
                    $repeat = 1;
                }
                // Limitar repetición para evitar arrays de millones de celdas vacías al final de la fila
                if ($repeat > 100) {
                    $repeat = 1;
                }

                $texto = trim($cell->textContent);
                for ($r = 0; $r < $repeat; $r++) {
                    $filaActual[] = $texto;
                }
            }

            // Quitar celdas vacías residuales a la derecha
            while (!empty($filaActual) && end($filaActual) === '') {
                array_pop($filaActual);
            }

            if (!empty(array_filter($filaActual, fn($v) => $v !== ''))) {
                $filas[] = $filaActual;
            }
        }

        return $filas;
    }

    /**
     * Parser CSV y TXT delimitado por comas, punto y coma o tabuladores con detección de codificación.
     */
    protected function extraerFilasCsv(string $ruta): array
    {
        $contenido = file_get_contents($ruta);
        // Detectar si requiere conversión de UTF-8
        if (!mb_check_encoding($contenido, 'UTF-8')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1, Windows-1252');
        }

        $lineas = preg_split("/\r\n|\n|\r/", $contenido);
        $filas = [];

        // Detectar delimitador inspeccionando la primera línea no vacía
        $delimitador = ',';
        foreach ($lineas as $linea) {
            $lineaTrim = trim($linea);
            if ($lineaTrim !== '') {
                $comas = substr_count($lineaTrim, ',');
                $puntosComa = substr_count($lineaTrim, ';');
                $tabs = substr_count($lineaTrim, "\t");
                if ($puntosComa > $comas && $puntosComa >= $tabs) {
                    $delimitador = ';';
                } elseif ($tabs > $comas && $tabs > $puntosComa) {
                    $delimitador = "\t";
                }
                break;
            }
        }

        foreach ($lineas as $linea) {
            $lineaTrim = trim($linea);
            if ($lineaTrim === '') {
                continue;
            }
            $cols = str_getcsv($linea, $delimitador);
            $cols = array_map('trim', $cols);
            if (!empty(array_filter($cols, fn($v) => $v !== ''))) {
                $filas[] = $cols;
            }
        }

        return $filas;
    }

    /**
     * Detecta las columnas correspondientes en la fila de cabecera.
     */
    protected function detectarIndicesCabecera(array $cabecera): array
    {
        $indices = [
            'documento' => null,
            'nombre'    => null,
            'email'     => null,
            'ficha'     => null,
            'programa'  => null,
            'centro'    => null,
        ];

        foreach ($cabecera as $idx => $val) {
            $colName = strtolower(trim((string)$val));
            $limpio = preg_replace('/[^a-z0-9_]/', '', str_replace(['á','é','í','ó','ú','ñ',' '], ['a','e','i','o','u','n','_'], $colName));

            if (is_null($indices['documento']) && (
                str_contains($limpio, 'doc') ||
                str_contains($limpio, 'cedula') ||
                str_contains($limpio, 'identificacion') ||
                str_contains($limpio, 'ti') ||
                $limpio === 'cc'
            )) {
                $indices['documento'] = $idx;
            } elseif (is_null($indices['email']) && (
                str_contains($limpio, 'email') ||
                str_contains($limpio, 'correo') ||
                str_contains($limpio, 'mail')
            )) {
                $indices['email'] = $idx;
            } elseif (is_null($indices['nombre']) && (
                str_contains($limpio, 'nombre') ||
                str_contains($limpio, 'aprendiz') ||
                str_contains($limpio, 'estudiante')
            )) {
                $indices['nombre'] = $idx;
            } elseif (is_null($indices['ficha']) && (
                str_contains($limpio, 'ficha') ||
                str_contains($limpio, 'grupo')
            )) {
                $indices['ficha'] = $idx;
            } elseif (is_null($indices['programa']) && str_contains($limpio, 'programa')) {
                $indices['programa'] = $idx;
            } elseif (is_null($indices['centro']) && (str_contains($limpio, 'centro') || str_contains($limpio, 'regional'))) {
                $indices['centro'] = $idx;
            }
        }

        // Si no se detectaron por nombre, asignar posiciones típicas si están libres
        if (is_null($indices['email'])) {
            // Buscar si alguna columna de la primera fila parece email
            foreach ($cabecera as $idx => $val) {
                if (filter_var(trim((string)$val), FILTER_VALIDATE_EMAIL)) {
                    $indices['email'] = $idx;
                    break;
                }
            }
        }

        // Posiciones por defecto en caso de formato estándar sin cabeceras explícitas
        if (is_null($indices['documento']) && count($cabecera) > 0) $indices['documento'] = 0;
        if (is_null($indices['nombre']) && count($cabecera) > 1) $indices['nombre'] = 1;
        if (is_null($indices['email']) && count($cabecera) > 2) $indices['email'] = 2;
        if (is_null($indices['ficha']) && count($cabecera) > 3) $indices['ficha'] = 3;

        return $indices;
    }

    /**
     * Sincroniza el padrón oficial en base de datos de manera transaccional.
     */
    protected function sincronizarPadron(
        array $filas,
        string $nombreOriginal,
        string $rutaPrivada,
        string $formato,
        string $ejecutadoPor
    ): array {
        if (empty($filas)) {
            throw new Exception("No hay registros legibles para procesar.");
        }

        // Determinar si la primera fila es cabecera
        $primeraFila = $filas[0];
        $tieneCabecera = false;
        $primeraCadena = strtolower(implode(' ', $primeraFila));
        if (str_contains($primeraCadena, 'doc') ||
            str_contains($primeraCadena, 'nom') ||
            str_contains($primeraCadena, 'correo') ||
            str_contains($primeraCadena, 'email') ||
            str_contains($primeraCadena, 'ficha')) {
            $tieneCabecera = true;
        }

        $indices = $this->detectarIndicesCabecera($primeraFila);
        $inicio = $tieneCabecera ? 1 : 0;

        $emailsEnNuevoPadron = [];
        $documentosEnNuevoPadron = [];
        $aprendicesAActualizar = [];
        $errores = [];
        $totalFilasDatos = 0;

        for ($i = $inicio; $i < count($filas); $i++) {
            $totalFilasDatos++;
            $fila = $filas[$i];
            $numFilaExcel = $i + 1;

            $docRaw = isset($indices['documento']) && isset($fila[$indices['documento']]) ? trim((string)$fila[$indices['documento']]) : '';
            $nombreRaw = isset($indices['nombre']) && isset($fila[$indices['nombre']]) ? trim((string)$fila[$indices['nombre']]) : '';
            $emailRaw = isset($indices['email']) && isset($fila[$indices['email']]) ? strtolower(trim((string)$fila[$indices['email']])) : '';
            $fichaRaw = isset($indices['ficha']) && isset($fila[$indices['ficha']]) ? trim((string)$fila[$indices['ficha']]) : '3173467';
            $programaRaw = isset($indices['programa']) && isset($fila[$indices['programa']]) ? trim((string)$fila[$indices['programa']]) : 'Tecnología en Análisis y Desarrollo de Software (ADSO)';
            $centroRaw = isset($indices['centro']) && isset($fila[$indices['centro']]) ? trim((string)$fila[$indices['centro']]) : 'CEAI - SENA Regional Valle';

            // Si el correo no es válido pero está en otra columna, buscarlo
            if (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
                foreach ($fila as $col) {
                    $posibleEmail = strtolower(trim((string)$col));
                    if (filter_var($posibleEmail, FILTER_VALIDATE_EMAIL)) {
                        $emailRaw = $posibleEmail;
                        break;
                    }
                }
            }

            // Validar que exista al menos email o documento
            if (!$emailRaw && !$docRaw) {
                $errores[] = [
                    'fila' => $numFilaExcel,
                    'motivo' => 'Fila omitida: no contiene correo electrónico ni número de documento.',
                    'datos' => implode(' | ', array_slice($fila, 0, 4)),
                ];
                continue;
            }

            if ($emailRaw && !filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
                $errores[] = [
                    'fila' => $numFilaExcel,
                    'motivo' => "Correo inválido ('{$emailRaw}').",
                    'datos' => implode(' | ', array_slice($fila, 0, 4)),
                ];
                continue;
            }

            // Normalizar valores
            $doc = preg_replace('/[^0-9A-Za-z-]/', '', $docRaw);
            $nombre = $nombreRaw ?: ($emailRaw ? explode('@', $emailRaw)[0] : 'Aprendiz SENA');
            $ficha = $fichaRaw ?: '3173467';

            if ($emailRaw) {
                $emailsEnNuevoPadron[] = $emailRaw;
            }
            if ($doc) {
                $documentosEnNuevoPadron[] = $doc;
            }

            $aprendicesAActualizar[] = [
                'numero_documento'    => $doc ?: null,
                'nombre_completo'     => $nombre,
                'email'               => $emailRaw ?: ($doc ? "aprendiz_{$doc}@sena.edu.co" : null),
                'activo'              => 1,
                'sincronizado_at'     => now(),
            ];
        }

        // Ejecutar sincronización en transacción
        $activados = 0;
        $inactivados = 0;

        DB::transaction(function () use (
            $aprendicesAActualizar,
            $emailsEnNuevoPadron,
            $documentosEnNuevoPadron,
            &$activados,
            &$inactivados
        ) {
            // 1. Activar o insertar los aprendices que están en el archivo
            foreach ($aprendicesAActualizar as $item) {
                if (empty($item['email']) && empty($item['numero_documento'])) {
                    continue;
                }

                // Buscar beneficiario existente por email o documento
                $query = BeneficiarioSena::query();
                if (!empty($item['email']) && !empty($item['numero_documento'])) {
                    $query->where(function ($q) use ($item) {
                        $q->where('email', $item['email'])
                          ->orWhere('numero_documento', $item['numero_documento']);
                    });
                } elseif (!empty($item['email'])) {
                    $query->where('email', $item['email']);
                } else {
                    $query->where('numero_documento', $item['numero_documento']);
                }

                $beneficiario = $query->first();

                if ($beneficiario) {
                    $beneficiario->update($item);
                } else {
                    BeneficiarioSena::create($item);
                }
                $activados++;

                // Sincronizar usuarios registrados en FoodPass para asegurar estado activo
                if (!empty($item['email'])) {
                    User::where('email', $item['email'])->update(['activo' => 1]);
                }
                if (!empty($item['numero_documento'])) {
                    User::where('documento', $item['numero_documento'])->update(['activo' => 1]);
                }
            }

            // 2. Inactivar a los beneficiarios que ya NO figuran en el listado oficial
            // (SIN borrar su historial en beneficiarios_sena ni canjes previos)
            $beneficiariosAInactivar = BeneficiarioSena::where('activo', 1)
                ->where(function ($q) use ($emailsEnNuevoPadron, $documentosEnNuevoPadron) {
                    if (!empty($emailsEnNuevoPadron)) {
                        $q->whereNotIn('email', $emailsEnNuevoPadron);
                    }
                    if (!empty($documentosEnNuevoPadron)) {
                        $q->whereNotIn('numero_documento', $documentosEnNuevoPadron);
                    }
                })
                ->get();

            foreach ($beneficiariosAInactivar as $b) {
                $b->update([
                    'activo' => 0,
                    'sincronizado_at' => now(),
                ]);
                $inactivados++;
            }
        });

        // 3. Registrar registro de la carga en `cargas_beneficiarios`
        $carga = CargaBeneficiario::create([
            'nombre_archivo_original' => $nombreOriginal,
            'ruta_archivo_privado'    => $rutaPrivada,
            'formato'                 => strtolower($formato),
            'total_filas_leidas'      => $totalFilasDatos,
            'aprendices_activados'    => $activados,
            'aprendices_inactivados'  => $inactivados,
            'errores_count'           => count($errores),
            'errores_detalle'         => $errores,
            'ejecutado_por'           => $ejecutadoPor,
        ]);

        // 4. Registrar evento en auditoría oficial FoodPass
        SrsFoodPassService::registrarAuditoria(
            'PADRON_SINCRONIZADO',
            'Administración',
            "Padrón oficial sincronizado con archivo '{$nombreOriginal}'. Activados: {$activados}, Inactivados: {$inactivados}, Errores: " . count($errores) . " por {$ejecutadoPor}.",
            [
                'carga_id'    => $carga->id,
                'archivo'     => $nombreOriginal,
                'activados'   => $activados,
                'inactivados' => $inactivados,
                'errores'     => count($errores),
            ]
        );

        return [
            'carga_id'               => $carga->id,
            'nombre_archivo'         => $nombreOriginal,
            'total_filas'            => $totalFilasDatos,
            'aprendices_activados'   => $activados,
            'aprendices_inactivados' => $inactivados,
            'errores_count'          => count($errores),
            'errores_detalle'        => $errores,
            'ejecutado_por'          => $ejecutadoPor,
        ];
    }
}
