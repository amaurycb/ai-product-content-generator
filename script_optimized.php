<?php
/**
 * Script Local para Generación de Contenido IA para Productos
 * Versión independiente sin dependencias externas
 */

// Configuración de ejecución
ini_set('memory_limit', '-1');
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========== CONFIGURACIÓN DE BASE DE DATOS ==========
// MODIFICA ESTOS VALORES CON TUS DATOS DE CONEXIÓN
$db_config = [
    'host' => 'localhost',
    'username' => 'amaurycb',
    'password' => 'rootpass',
    'database' => 'obuma-online-dev',
    'charset' => 'utf8mb4'
];

// ========== CONFIGURACIÓN BATCH PROCESSING ==========
$BATCH_SIZE = isset($_GET['batch_size']) ? (int)$_GET['batch_size'] : 10; // Productos por lote
$BATCH_DELAY = isset($_GET['batch_delay']) ? (int)$_GET['batch_delay'] : 3; // Segundos entre requests
$MAX_RETRIES = 5; // Reintentos por request fallido
$REQUEST_TIMEOUT = 60; // Timeout por request (incrementado para IA)

// ========== PARÁMETROS DEL SCRIPT ==========
$id_empresa = isset($_GET['id_empresa']) ? (int)$_GET['id_empresa'] : 0;
$id_categoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : 0;
$id_subcategoria = isset($_GET['id_subcategoria']) ? (int)$_GET['id_subcategoria'] : 0;
$proccess = isset($_GET['proccess']) ? (int)$_GET['proccess'] : 0;
$update = isset($_GET['update']) ? (int)$_GET['update'] : 0;
$job_id = isset($_GET['job_id']) ? $_GET['job_id'] : null;

// Log para seguimiento
$log_file = __DIR__ . '/logs/ia_batch_' . date('Y-m-d') . '_job-' . ($job_id ?: 'manual') . '.txt';
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// ========== FUNCIONES AUXILIARES ==========

function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    echo $log_entry;
}

function connect_database($config) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        log_message("Error de conexión a BD: " . $e->getMessage());
        die("Error de conexión a la base de datos");
    }
}

function get_categorias_array($pdo, $id_empresa) {
    try {
        $stmt = $pdo->prepare("SELECT producto_categoria_id, producto_categoria_nombre FROM producto_categoria WHERE rel_empresa_id = ? ORDER BY producto_categoria_id ASC");
        $stmt->execute([$id_empresa]);
        $result = [];
        while ($row = $stmt->fetch()) {
            $result[$row['producto_categoria_id']] = $row['producto_categoria_nombre'];
        }
        return $result;
    } catch (PDOException $e) {
        log_message("Error obteniendo categorías: " . $e->getMessage());
        return [];
    }
}

function get_subcategorias_array($pdo, $id_empresa) {
    try {
        $stmt = $pdo->prepare("SELECT producto_subcategoria_id, producto_subcategoria_nombre FROM producto_subcategoria WHERE rel_empresa_id = ? ORDER BY producto_subcategoria_id ASC");
        $stmt->execute([$id_empresa]);
        $result = [];
        while ($row = $stmt->fetch()) {
            $result[$row['producto_subcategoria_id']] = $row['producto_subcategoria_nombre'];
        }
        return $result;
    } catch (PDOException $e) {
        log_message("Error obteniendo subcategorías: " . $e->getMessage());
        return [];
    }
}

function get_fabricantes_array($pdo, $id_empresa) {
    try {
        $stmt = $pdo->prepare("SELECT producto_fabricante_id, producto_fabricante_nombre FROM producto_fabricante WHERE rel_empresa_id = ? ORDER BY producto_fabricante_id ASC");
        $stmt->execute([$id_empresa]);
        $result = [];
        while ($row = $stmt->fetch()) {
            $result[$row['producto_fabricante_id']] = $row['producto_fabricante_nombre'];
        }
        return $result;
    } catch (PDOException $e) {
        log_message("Error obteniendo fabricantes: " . $e->getMessage());
        return [];
    }
}

function get_fabricante_nombre_by_id($pdo, $fabricante_id, $id_empresa) {
    if (empty($fabricante_id)) return "";
    
    try {
        $stmt = $pdo->prepare("SELECT producto_fabricante_nombre FROM producto_fabricante WHERE producto_fabricante_id = ? AND rel_empresa_id = ? LIMIT 1");
        $stmt->execute([$fabricante_id, $id_empresa]);
        $result = $stmt->fetch();
        return $result ? $result['producto_fabricante_nombre'] : "";
    } catch (PDOException $e) {
        log_message("Error obteniendo nombre fabricante: " . $e->getMessage());
        return "";
    }
}

function update_job_status($pdo, $job_id, $status) {
    if (!$job_id) return;
    
    try {
        $stmt = $pdo->prepare("UPDATE a_queue_jobs SET job_status = ?, job_updated_at = ? WHERE job_id = ?");
        $stmt->execute([$status, date('Y-m-d H:i:s'), $job_id]);
    } catch (PDOException $e) {
        log_message("Error actualizando estado del job: " . $e->getMessage());
    }
}

function set_producto_history($pdo, $status, $descripcion, $producto_id, $id_empresa, $usuario_id = 1) {
    try {
        $stmt = $pdo->prepare("INSERT INTO producto_history (ph_status, ph_descripcion, ph_fecha, rel_usuario_id, rel_producto_id, rel_empresa_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$status, $descripcion, date('Y-m-d H:i:s'), $usuario_id, $producto_id, $id_empresa]);
    } catch (PDOException $e) {
        log_message("Error insertando historial: " . $e->getMessage());
    }
}

// ========== FUNCIÓN PRINCIPAL PARA LLAMAR A LA API CON REINTENTOS MEJORADOS ==========
function call_ai_api($producto_data, $api_key, $api_url, $max_retries, $timeout) {
    $new_data = [
        "body" => [
            "user_id" => "elisnordis-batch-" . $producto_data['producto_id'],
            "input_data" => [
                "title" => $producto_data['producto_nombre'],
                "brand" => $producto_data['fabricante_nombre'],
                "short_description" => $producto_data['producto_descripcion'] ?: "",
                "long_description" => $producto_data['producto_descripcion_larga'] ?: "",
                "category" => $producto_data['categoria_nombre'],
                "tone" => "experto",
                "style" => "Educational responses for learning new concepts. Catálogo de repuestos de maquinaria pesada."
            ],
            "prompt_id" => "68MSIK4CHW",
            "prompt_version" => "11"
        ]
    ];

    $json_data = json_encode($new_data);
    $retry_delays = [2, 4, 8, 16, 32]; // Backoff exponencial mejorado
    
    for ($retry = 0; $retry <= $max_retries; $retry++) {
        log_message("Intento " . ($retry + 1) . " de " . ($max_retries + 1) . " para producto {$producto_data['producto_id']}");
        
        $session = curl_init($api_url);
        
        $headers = [
            'x-api-key: ' . $api_key,
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (compatible; ObumaBot/1.0)'
        ];

        curl_setopt_array($session, [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => $json_data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);

        $response = curl_exec($session);
        $http_code = curl_getinfo($session, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($session);
        $curl_info = curl_getinfo($session);
        curl_close($session);

        // Log detallado para debugging
        log_message("HTTP Code: $http_code, Response size: " . strlen($response) . " bytes");
        
        if ($curl_error) {
            log_message("Retry $retry - cURL Error para producto {$producto_data['producto_id']}: $curl_error");
            if ($retry < $max_retries) {
                $delay = $retry_delays[$retry] ?? 32;
                log_message("Esperando {$delay}s antes del siguiente intento...");
                sleep($delay);
                continue;
            }
            return ['success' => false, 'error' => $curl_error, 'retry_count' => $retry + 1];
        }

        if ($http_code == 200) {
            $data_decode = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message("Error JSON decode: " . json_last_error_msg());
                log_message("Response preview: " . substr($response, 0, 500));
                if ($retry < $max_retries) {
                    $delay = $retry_delays[$retry] ?? 32;
                    sleep($delay);
                    continue;
                }
                return ['success' => false, 'error' => 'JSON decode error: ' . json_last_error_msg(), 'retry_count' => $retry + 1];
            }
            
            if ($data_decode && isset($data_decode["body"]["generated_content"])) {
                log_message("✓ Respuesta exitosa para producto {$producto_data['producto_id']} en intento " . ($retry + 1));
                return ['success' => true, 'data' => $data_decode, 'retry_count' => $retry + 1];
            } else {
                log_message("Respuesta válida pero sin contenido generado");
                if ($retry < $max_retries) {
                    $delay = $retry_delays[$retry] ?? 32;
                    sleep($delay);
                    continue;
                }
            }
        } elseif ($http_code == 400) {
            // Caso específico para error 400 - intentar nuevamente
            log_message("Error 400 detectado. Response: " . substr($response, 0, 500));
            if ($retry < $max_retries) {
                $delay = $retry_delays[$retry] ?? 32;
                log_message("Error 400 - Reintentando en {$delay}s...");
                sleep($delay);
                continue;
            }
        } elseif ($http_code == 429) {
            // Rate limit - espera más tiempo
            $delay = ($retry_delays[$retry] ?? 32) * 2;
            log_message("Rate limit alcanzado, esperando {$delay}s...");
            sleep($delay);
            continue;
        } elseif ($http_code >= 500) {
            // Error del servidor - reintentar
            log_message("Error servidor HTTP $http_code, reintentando...");
            if ($retry < $max_retries) {
                $delay = $retry_delays[$retry] ?? 32;
                sleep($delay);
                continue;
            }
        }

        // Para otros códigos HTTP, registrar y salir
        log_message("HTTP $http_code - Response: " . substr($response, 0, 300));
        return ['success' => false, 'error' => "HTTP $http_code", 'response' => substr($response, 0, 1000), 'retry_count' => $retry + 1];
    }
    
    return ['success' => false, 'error' => "Max retries alcanzado después de " . ($max_retries + 1) . " intentos", 'retry_count' => $max_retries + 1];
}

// ========== VALIDACIÓN DE PARÁMETROS ==========
if ($id_empresa <= 0) {
    log_message("ERROR: ID de empresa no válido");
    die("ID de empresa requerido");
}

// ========== CONEXIÓN A BASE DE DATOS ==========
log_message("Conectando a base de datos...");
$pdo = connect_database($db_config);

// ========== INICIO DEL JOB ==========
if ($job_id) {
    update_job_status($pdo, $job_id, '99'); // En proceso
    log_message("------------------------------------------------------------------------");
    log_message("Job iniciado: " . $job_id);
}

log_message("Iniciando procesamiento para empresa: $id_empresa");

// ========== OBTENER ARRAYS DE REFERENCIA ==========
log_message("Cargando datos de referencia...");
$categorias = get_categorias_array($pdo, $id_empresa);
$subcategorias = get_subcategorias_array($pdo, $id_empresa);
$fabricantes = get_fabricantes_array($pdo, $id_empresa);

log_message("Cargados: " . count($categorias) . " categorías, " . count($subcategorias) . " subcategorías, " . count($fabricantes) . " fabricantes");

// ========== CONSTRUIR CONSULTA PRINCIPAL ==========
$where_conditions = ["rel_empresa_id = ?"];
$params = [$id_empresa];

if ($id_categoria > 0) {
    $where_conditions[] = "producto_categoria = ?";
    $params[] = $id_categoria;
}

if ($id_subcategoria > 0) {
    $where_conditions[] = "producto_subcategoria = ?";
    $params[] = $id_subcategoria;
}

$where_clause = implode(" AND ", $where_conditions);

// Contar total de productos
$count_sql = "SELECT COUNT(*) as total FROM producto WHERE $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_productos = $stmt->fetchColumn();

log_message("Total de productos a procesar: $total_productos");

if ($total_productos == 0) {
    log_message("No hay productos para procesar");
    if ($job_id) {
        update_job_status($pdo, $job_id, '1'); // Completado
    }
    exit;
}

// ========== CONFIGURACIÓN API ==========
$api_key = "HAOicUBwv223OXtbXlkXLa3WtrKOxOABDNJJNgZd";
$api_url = "https://kzzovm9z11.execute-api.us-east-1.amazonaws.com/developer";

// ========== CONTADORES GLOBALES ==========
$productos_actualizados = 0;
$productos_error = 0;
$total_reintentos = 0;
$start_time = microtime(true);

// ========== PROCESAMIENTO POR LOTES ==========
$offset = 0;
$total_procesados_global = 0;

do {
    $sql = "SELECT producto_id, producto_codigo_comercial, producto_nombre, producto_descripcion, 
                   producto_descripcion_larga, producto_categoria, producto_subcategoria, 
                   producto_fabricante, producto_imagen_principal 
            FROM producto 
            WHERE $where_clause 
            ORDER BY producto_id ASC 
            LIMIT $BATCH_SIZE OFFSET $offset";

    log_message("Procesando lote - Offset: $offset, Limit: $BATCH_SIZE");
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();

    if (empty($productos)) {
        log_message("No hay más productos para procesar");
        break;
    }

    log_message("Productos en este lote: " . count($productos));

    foreach ($productos as $producto) {
        if ($proccess == 1) {
            // Preparar datos del producto
            $fabricante_nombre = "";
            if (!empty($producto['producto_fabricante'])) {
                // Si producto_fabricante es un ID numérico, buscar el nombre
                if (is_numeric($producto['producto_fabricante'])) {
                    $fabricante_nombre = get_fabricante_nombre_by_id($pdo, $producto['producto_fabricante'], $id_empresa);
                } else {
                    // Si ya es un nombre, usar directamente
                    $fabricante_nombre = $producto['producto_fabricante'];
                }
            }
            
            $producto_data = [
                'producto_id' => $producto['producto_id'],
                'producto_nombre' => $producto['producto_nombre'],
                'producto_descripcion' => $producto['producto_descripcion'],
                'producto_descripcion_larga' => $producto['producto_descripcion_larga'],
                'categoria_nombre' => $categorias[$producto['producto_categoria']] ?? "general",
                'fabricante_nombre' => $fabricante_nombre
            ];

            log_message("Procesando producto ID: {$producto_data['producto_id']} - {$producto_data['producto_nombre']}");

            // Llamar a la API
            $api_result = call_ai_api($producto_data, $api_key, $api_url, $MAX_RETRIES, $REQUEST_TIMEOUT);
            $total_reintentos += $api_result['retry_count'] ?? 1;
            
            if ($api_result['success']) {
                $data_decode = $api_result['data'];
                
                if (isset($data_decode["body"]["generated_content"])) {
                    $generated_content = $data_decode["body"]["generated_content"];
                    
                    $title = $generated_content["title"] ?? "";
                    $short_description = $generated_content["short_description"] ?? "";
                    $long_description = $generated_content["long_description"] ?? "";
                    $keywords = isset($generated_content["keywords"]) ? implode(',', $generated_content["keywords"]) : "";
                    $meta_description = $generated_content["meta_description"] ?? "";

                    // Actualizar en base de datos si se solicita
                    if ($update == 1 && !empty($short_description)) {
                        try {
                            $update_sql = "UPDATE producto SET 
                                          producto_descripcion = ?,
                                          producto_descripcion_larga = ?,
                                          producto_metadescription = ?,
                                          producto_metakeywords = ?,
                                          producto_metatitle = ?
                                          WHERE producto_id = ? AND rel_empresa_id = ?";
                            
                            $update_stmt = $pdo->prepare($update_sql);
                            $update_stmt->execute([
                                $short_description,
                                $long_description,
                                $meta_description,
                                $keywords,
                                $title,
                                $producto['producto_id'],
                                $id_empresa
                            ]);
                            
                            set_producto_history($pdo, 'Edita descripciones', 'Via IA batch processing', $producto['producto_id'], $id_empresa, 1);
                            $productos_actualizados++;
                            log_message("✓ Producto {$producto['producto_id']} actualizado exitosamente");
                        } catch (PDOException $e) {
                            log_message("✗ Error BD para producto {$producto['producto_id']}: " . $e->getMessage());
                            $productos_error++;
                        }
                    } else {
                        log_message("✓ Producto {$producto['producto_id']} procesado (sin actualización)");
                    }
                } else {
                    log_message("✗ Respuesta inesperada de API para producto {$producto['producto_id']}");
                    $productos_error++;
                }
            } else {
                log_message("✗ Error API para producto {$producto['producto_id']}: " . $api_result['error']);
                $productos_error++;
            }
            
            $total_procesados_global++;
            
            // Control de memoria cada 10 productos
            if ($total_procesados_global % 10 == 0) {
                $memory_usage = memory_get_usage(true) / 1024 / 1024;
                log_message("Memoria usada: {$memory_usage}MB - Total procesados: $total_procesados_global");
            }
            
            // Pausa entre requests
            sleep($BATCH_DELAY);
        }
    }

    $offset += $BATCH_SIZE;

    log_message("=== RESUMEN DEL LOTE ===");
    log_message("Productos en lote: " . count($productos));
    log_message("Total procesados: $total_procesados_global de $total_productos");

} while (count($productos) == $BATCH_SIZE);

// ========== RESUMEN FINAL ==========
$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 2);

log_message("=== RESUMEN FINAL COMPLETO ===");
log_message("Total productos procesados: $total_procesados_global");
log_message("Productos actualizados: $productos_actualizados");
log_message("Productos con error: $productos_error");
log_message("Total reintentos realizados: $total_reintentos");
log_message("Tiempo ejecución total: {$execution_time}s");
log_message("Promedio por producto: " . round($execution_time / max($total_procesados_global, 1), 2) . "s");

// Finalizar job
if ($job_id) {
    $final_status = ($productos_error > 0) ? '2' : '1'; // 2 = completado con errores, 1 = completado exitoso
    update_job_status($pdo, $job_id, $final_status);
    log_message("Job finalizado: $job_id (status: $final_status)");
}

log_message("=== FIN DEL PROCESAMIENTO COMPLETO ===");
?>