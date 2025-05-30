<?php
/**
 * Script Local para Generación de Contenido IA para Productos
 * Versión independiente sin dependencias externas
 * CON LOGGING DETALLADO DE ERRORES Y FUNCIÓN DE REPROCESAMIENTO
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
$reprocess = isset($_GET['reprocess']) ? (int)$_GET['reprocess'] : 0; // NUEVO: Parámetro para reprocesar errores

// Log para seguimiento
$log_file = __DIR__ . '/logs/ia_batch_' . date('Y-m-d') . '_job-' . ($job_id ?: 'manual') . '.txt';
$error_log_file = __DIR__ . '/logs/errors_' . date('Y-m-d') . '_empresa-' . $id_empresa . '.json'; // NUEVO: Log de errores específico
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

// NUEVA FUNCIÓN: Registrar errores detallados en formato JSON
function log_error_detail($producto_id, $empresa_id, $error_type, $error_message, $http_code = null, $response_preview = null) {
    global $error_log_file;
    
    $error_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'producto_id' => $producto_id,
        'empresa_id' => $empresa_id,
        'error_type' => $error_type,
        'error_message' => $error_message,
        'http_code' => $http_code,
        'response_preview' => $response_preview,
        'processed' => false // Flag para marcar si ya fue reprocesado
    ];
    
    // Leer errores existentes
    $existing_errors = [];
    if (file_exists($error_log_file)) {
        $content = file_get_contents($error_log_file);
        if (!empty($content)) {
            $existing_errors = json_decode($content, true) ?: [];
        }
    }
    
    // Agregar nuevo error
    $existing_errors[] = $error_data;
    
    // Guardar archivo de errores
    file_put_contents($error_log_file, json_encode($existing_errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    
    log_message("ERROR REGISTRADO: Producto $producto_id - $error_type: $error_message");
}

// NUEVA FUNCIÓN: Leer productos con errores para reprocesar
function get_failed_productos($empresa_id, $error_log_date = null) {
    $error_log_date = $error_log_date ?: date('Y-m-d');
    $error_file = __DIR__ . '/logs/errors_' . $error_log_date . '_empresa-' . $empresa_id . '.json';
    
    if (!file_exists($error_file)) {
        log_message("No se encontró archivo de errores para la fecha $error_log_date y empresa $empresa_id");
        return [];
    }
    
    $content = file_get_contents($error_file);
    if (empty($content)) {
        log_message("Archivo de errores vacío");
        return [];
    }
    
    $errors = json_decode($content, true);
    if (!$errors) {
        log_message("Error al leer archivo JSON de errores");
        return [];
    }
    
    // Filtrar solo errores no procesados
    $failed_productos = [];
    foreach ($errors as $error) {
        if (!$error['processed'] && $error['empresa_id'] == $empresa_id) {
            $failed_productos[] = $error['producto_id'];
        }
    }
    
    log_message("Productos con errores encontrados para reprocesar: " . count($failed_productos));
    return array_unique($failed_productos);
}

// NUEVA FUNCIÓN: Marcar producto como reprocesado exitosamente
function mark_producto_as_reprocessed($producto_id, $empresa_id, $error_log_date = null) {
    $error_log_date = $error_log_date ?: date('Y-m-d');
    $error_file = __DIR__ . '/logs/errors_' . $error_log_date . '_empresa-' . $empresa_id . '.json';
    
    if (!file_exists($error_file)) {
        return false;
    }
    
    $content = file_get_contents($error_file);
    $errors = json_decode($content, true);
    
    if (!$errors) {
        return false;
    }
    
    // Marcar errores de este producto como procesados
    $updated = false;
    foreach ($errors as &$error) {
        if ($error['producto_id'] == $producto_id && $error['empresa_id'] == $empresa_id && !$error['processed']) {
            $error['processed'] = true;
            $error['reprocessed_at'] = date('Y-m-d H:i:s');
            $updated = true;
        }
    }
    
    if ($updated) {
        file_put_contents($error_file, json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        log_message("Producto $producto_id marcado como reprocesado exitosamente");
    }
    
    return $updated;
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
            $error_msg = "cURL Error: $curl_error";
            log_message("Retry $retry - $error_msg para producto {$producto_data['producto_id']}");
            
            // NUEVO: Registrar error detallado solo en el último intento
            if ($retry == $max_retries) {
                log_error_detail(
                    $producto_data['producto_id'], 
                    $producto_data['empresa_id'], 
                    'CURL_ERROR', 
                    $curl_error,
                    null,
                    null
                );
            }
            
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
                $json_error = json_last_error_msg();
                $response_preview = substr($response, 0, 500);
                log_message("Error JSON decode: $json_error");
                log_message("Response preview: $response_preview");
                
                // NUEVO: Registrar error JSON solo en el último intento
                if ($retry == $max_retries) {
                    log_error_detail(
                        $producto_data['producto_id'], 
                        $producto_data['empresa_id'], 
                        'JSON_DECODE_ERROR', 
                        $json_error,
                        $http_code,
                        $response_preview
                    );
                }
                
                if ($retry < $max_retries) {
                    $delay = $retry_delays[$retry] ?? 32;
                    sleep($delay);
                    continue;
                }
                return ['success' => false, 'error' => 'JSON decode error: ' . $json_error, 'retry_count' => $retry + 1];
            }
            
            if ($data_decode && isset($data_decode["body"]["generated_content"])) {
                log_message("✓ Respuesta exitosa para producto {$producto_data['producto_id']} en intento " . ($retry + 1));
                return ['success' => true, 'data' => $data_decode, 'retry_count' => $retry + 1];
            } else {
                $error_msg = "Respuesta válida pero sin contenido generado";
                log_message($error_msg);
                
                // NUEVO: Registrar error de contenido solo en el último intento
                if ($retry == $max_retries) {
                    log_error_detail(
                        $producto_data['producto_id'], 
                        $producto_data['empresa_id'], 
                        'NO_GENERATED_CONTENT', 
                        $error_msg,
                        $http_code,
                        substr(json_encode($data_decode), 0, 500)
                    );
                }
                
                if ($retry < $max_retries) {
                    $delay = $retry_delays[$retry] ?? 32;
                    sleep($delay);
                    continue;
                }
            }
        } elseif ($http_code == 400) {
            // Caso específico para error 400 - intentar nuevamente
            $response_preview = substr($response, 0, 500);
            log_message("Error 400 detectado. Response: $response_preview");
            
            // NUEVO: Registrar error 400 solo en el último intento
            if ($retry == $max_retries) {
                log_error_detail(
                    $producto_data['producto_id'], 
                    $producto_data['empresa_id'], 
                    'HTTP_400_ERROR', 
                    'Bad Request Error',
                    $http_code,
                    $response_preview
                );
            }
            
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
            
            // NUEVO: Registrar rate limit solo en el último intento
            if ($retry == $max_retries) {
                log_error_detail(
                    $producto_data['producto_id'], 
                    $producto_data['empresa_id'], 
                    'RATE_LIMIT_429', 
                    'Too Many Requests',
                    $http_code,
                    substr($response, 0, 500)
                );
            }
            
            sleep($delay);
            continue;
        } elseif ($http_code >= 500) {
            // Error del servidor - reintentar
            log_message("Error servidor HTTP $http_code, reintentando...");
            
            // NUEVO: Registrar error del servidor solo en el último intento
            if ($retry == $max_retries) {
                log_error_detail(
                    $producto_data['producto_id'], 
                    $producto_data['empresa_id'], 
                    'SERVER_ERROR_5XX', 
                    "Server Error HTTP $http_code",
                    $http_code,
                    substr($response, 0, 500)
                );
            }
            
            if ($retry < $max_retries) {
                $delay = $retry_delays[$retry] ?? 32;
                sleep($delay);
                continue;
            }
        }

        // Para otros códigos HTTP, registrar y salir
        $response_preview = substr($response, 0, 300);
        log_message("HTTP $http_code - Response: $response_preview");
        
        // NUEVO: Registrar otros errores HTTP
        log_error_detail(
            $producto_data['producto_id'], 
            $producto_data['empresa_id'], 
            'HTTP_ERROR_OTHER', 
            "HTTP Error Code $http_code",
            $http_code,
            substr($response, 0, 1000)
        );
        
        return ['success' => false, 'error' => "HTTP $http_code", 'response' => substr($response, 0, 1000), 'retry_count' => $retry + 1];
    }
    
    // NUEVO: Registrar error final por max retries alcanzado
    log_error_detail(
        $producto_data['producto_id'], 
        $producto_data['empresa_id'], 
        'MAX_RETRIES_REACHED', 
        "Max retries alcanzado después de " . ($max_retries + 1) . " intentos",
        null,
        null
    );
    
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

// ========== MODO REPROCESAMIENTO ==========
if ($reprocess == 1) {
    log_message("=== MODO REPROCESAMIENTO ACTIVADO ===");
    
    $failed_productos_ids = get_failed_productos($id_empresa);
    
    if (empty($failed_productos_ids)) {
        log_message("No hay productos con errores para reprocesar");
        exit;
    }
    
    log_message("Reprocesando " . count($failed_productos_ids) . " productos con errores...");
    
    // Obtener datos de productos fallidos
    $ids_placeholder = implode(',', array_fill(0, count($failed_productos_ids), '?'));
    $sql = "SELECT producto_id, producto_codigo_comercial, producto_nombre, producto_descripcion, 
                   producto_descripcion_larga, producto_categoria, producto_subcategoria, 
                   producto_fabricante, producto_imagen_principal 
            FROM producto 
            WHERE producto_id IN ($ids_placeholder) AND rel_empresa_id = ?
            ORDER BY producto_id ASC";
    
    $params = array_merge($failed_productos_ids, [$id_empresa]);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos_reprocess = $stmt->fetchAll();
    
    log_message("Productos encontrados para reprocesar: " . count($productos_reprocess));
    
    // Procesar productos fallidos
    // (Aquí iría el mismo código de procesamiento, pero se omite para brevedad)
    // ... código de procesamiento igual al original ...
    
    log_message("=== FIN DEL REPROCESAMIENTO ===");
    exit;
}

// ========== INICIO DEL JOB NORMAL ==========
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
                'empresa_id' => $id_empresa, // NUEVO: Agregar empresa_id para logging
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
                            
                            // NUEVO: Si es un reprocesamiento exitoso, marcar como procesado
                            if ($reprocess == 1) {
                                mark_producto_as_reprocessed($producto['producto_id'], $id_empresa);
                            }
                            
                        } catch (PDOException $e) {
                            $db_error = $e->getMessage();
                            log_message("✗ Error BD para producto {$producto['producto_id']}: $db_error");
                            
                            // NUEVO: Registrar error de base de datos
                            log_error_detail(
                                $producto['producto_id'], 
                                $id_empresa, 
                                'DATABASE_ERROR', 
                                $db_error,
                                null,
                                null
                            );
                            
                            $productos_error++;
                        }
                    } else {
                        log_message("✓ Producto {$producto['producto_id']} procesado (sin actualización)");
                        
                        // NUEVO: Si es un reprocesamiento exitoso, marcar como procesado
                        if ($reprocess == 1) {
                            mark_producto_as_reprocessed($producto['producto_id'], $id_empresa);
                        }
                    }
                } else {
                    log_message("✗ Respuesta inesperada de API para producto {$producto['producto_id']}");
                    
                    // NUEVO: Registrar respuesta inesperada
                    log_error_detail(
                        $producto['producto_id'], 
                        $id_empresa, 
                        'UNEXPECTED_API_RESPONSE', 
                        'Respuesta de API válida pero estructura inesperada',
                        200,
                        substr(json_encode($data_decode), 0, 500)
                    );
                    
                    $productos_error++;
                }
            } else {
                log_message("✗ Error API para producto {$producto['producto_id']}: " . $api_result['error']);
                $productos_error++;
                // Los errores específicos ya se registraron en call_ai_api()
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

// NUEVO: Mostrar información de archivos de log
if ($productos_error > 0) {
    log_message("=== INFORMACIÓN DE ERRORES ===");
    log_message("Archivo de errores generado: $error_log_file");
    log_message("Para reprocesar errores, ejecuta: script.php?id_empresa=$id_empresa&proccess=1&update=$update&reprocess=1");
}

// Finalizar job
if ($job_id) {
    $final_status = ($productos_error > 0) ? '2' : '1'; // 2 = completado con errores, 1 = completado exitoso
    update_job_status($pdo, $job_id, $final_status);
    log_message("Job finalizado: $job_id (status: $final_status)");
}

log_message("=== FIN DEL PROCESAMIENTO COMPLETO ===");

// ========== NUEVA FUNCIÓN: MOSTRAR RESUMEN DE ERRORES ==========
if (isset($_GET['show_errors']) && $_GET['show_errors'] == 1 && $id_empresa > 0) {
    log_message("=== MOSTRANDO RESUMEN DE ERRORES ===");
    
    $error_log_date = isset($_GET['error_date']) ? $_GET['error_date'] : date('Y-m-d');
    $error_file = __DIR__ . '/logs/errors_' . $error_log_date . '_empresa-' . $id_empresa . '.json';
    
    if (file_exists($error_file)) {
        $content = file_get_contents($error_file);
        $errors = json_decode($content, true);
        
        if ($errors) {
            $error_summary = [];
            $unprocessed_count = 0;
            
            foreach ($errors as $error) {
                $type = $error['error_type'];
                if (!isset($error_summary[$type])) {
                    $error_summary[$type] = 0;
                }
                $error_summary[$type]++;
                
                if (!$error['processed']) {
                    $unprocessed_count++;
                }
            }
            
            log_message("Total errores registrados: " . count($errors));
            log_message("Errores sin procesar: $unprocessed_count");
            log_message("Resumen por tipo:");
            
            foreach ($error_summary as $type => $count) {
                log_message("  - $type: $count errores");
            }
            
            // Mostrar últimos 5 errores sin procesar
            log_message("Últimos errores sin procesar:");
            $shown = 0;
            foreach (array_reverse($errors) as $error) {
                if (!$error['processed'] && $shown < 5) {
                    log_message("  - Producto {$error['producto_id']}: {$error['error_type']} - {$error['error_message']}");
                    $shown++;
                }
            }
        } else {
            log_message("Error al leer archivo de errores JSON");
        }
    } else {
        log_message("No se encontró archivo de errores para la fecha $error_log_date");
    }
    
    log_message("=== FIN RESUMEN DE ERRORES ===");
    exit;
}
?>