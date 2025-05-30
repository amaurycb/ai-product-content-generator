# 🚀 Script de Generación de Contenido IA para Productos - VERSIÓN TURBO

Script PHP ultra-optimizado para generar automáticamente descripciones, meta tags y contenido SEO para productos usando Inteligencia Artificial con **máxima velocidad de procesamiento**.

## 📋 Descripción

Este script procesa productos de una base de datos y utiliza una API de IA para generar contenido optimizado con **velocidades de hasta 100+ productos por minuto**:

- ✅ **Descripciones cortas y largas** mejoradas por IA
- ✅ **Meta títulos SEO** optimizados para buscadores
- ✅ **Meta descripciones** atractivas y relevantes
- ✅ **Keywords automáticas** para mejor posicionamiento
- ✅ **Historial de cambios** detallado y trazable
- ✅ **Sistema de reprocesamiento** para errores
- ✅ **Logging avanzado de errores** en formato JSON
- ✅ **Gestión inteligente de reintentos** con backoff exponencial

## 🎯 Características Principales

- ⚡ **Procesamiento TURBO** - Hasta 100+ productos por minuto
- 🔄 **Reintentos inteligentes** con backoff exponencial optimizado
- 📊 **Logging selectivo** para máximo rendimiento
- 🛡️ **Manejo automático de rate limits** y errores 400/500
- 🧠 **Optimización de memoria** automática
- 📈 **Monitoreo en tiempo real** de velocidad y progreso
- 🏢 **Multi-empresa** con procesamiento independiente
- ⚡ **Optimizado para PHP 8.3** (compatible desde 7.4+)
- 🔄 **Sistema de reprocesamiento** de productos fallidos
- 📋 **Logging detallado de errores** con análisis por tipo
- 🎯 **Gestión automática de timeouts** y conexiones
- 📊 **Resúmenes estadísticos** completos de ejecución

## 🆕 Nuevas Funcionalidades del Script

### 🔄 **Sistema de Reprocesamiento de Errores**

El script incluye un sistema completo para manejar y reprocesar productos que fallaron:

- **Registro automático de errores** en archivos JSON estructurados
- **Identificación de productos fallidos** por empresa y fecha
- **Modo reprocesamiento** para reintentar solo productos con errores
- **Marcado automático** de productos reprocesados exitosamente

### 📊 **Logging Avanzado de Errores**

Sistema de logging mejorado que incluye:

- **Archivos de errores en JSON** con estructura detallada
- **Clasificación por tipo de error** (CURL, HTTP, JSON, etc.)
- **Información de contexto** (códigos HTTP, respuestas de API)
- **Timestamps y metadatos** para debugging avanzado
- **Estado de procesamiento** para tracking de reprocesos

### 🎯 **Gestión Inteligente de API**

Manejo robusto de la API de IA con:

- **Backoff exponencial mejorado** (2s, 4s, 8s, 16s, 32s)
- **Manejo específico de códigos HTTP** (400, 429, 5xx)
- **Timeouts configurables** por tipo de operación
- **User-Agent personalizado** para mejor identificación
- **Validación de respuestas JSON** con logging de errores

## 🗄️ Tablas de Base de Datos

### **Tablas Principales (Obligatorias)**

| Tabla | Propósito | Campos Clave |
|-------|-----------|--------------|
| `producto` | Productos principales | `producto_id`, `producto_nombre`, `producto_descripcion`, `rel_empresa_id` |
| `producto_categoria` | Categorías de productos | `producto_categoria_id`, `producto_categoria_nombre` |
| `producto_subcategoria` | Subcategorías | `producto_subcategoria_id`, `producto_subcategoria_nombre` |
| `producto_fabricante` | Fabricantes/Marcas | `producto_fabricante_id`, `producto_fabricante_nombre`, `producto_fabricante_imagen` |
| `producto_history` | Historial de cambios | `ph_id`, `ph_status`, `ph_descripcion`, `rel_producto_id` |

### **Tablas Opcionales**

| Tabla | Propósito | Requerida Para |
|-------|-----------|----------------|
| `a_queue_jobs` | Sistema de colas | Parámetro `job_id` |

### **Campos Actualizados por el Script**

```sql
-- En tabla 'producto' - Campos modificados por IA
producto_descripcion       -- Descripción corta optimizada
producto_descripcion_larga -- Descripción detallada generada
producto_metatitle         -- Título SEO optimizado
producto_metadescription   -- Meta descripción para buscadores
producto_metakeywords      -- Keywords relevantes (separadas por comas)
```

## ⚙️ Variables de Configuración

### **🔧 Configuración de Base de Datos**
```php
$db_config = [
    'host' => 'localhost',           // Servidor MySQL
    'username' => 'tu_usuario',      // Usuario BD
    'password' => 'tu_password',     // Contraseña BD
    'database' => 'tu_base_datos',   // Nombre BD
    'charset' => 'utf8mb4'           // Charset (recomendado)
];
```

### **⚡ Parámetros de Velocidad TURBO**

| Variable | Valor Optimizado | Descripción | Rango Recomendado |
|----------|------------------|-------------|-------------------|
| `batch_size` | `30` | Productos por lote | `20-50` (hasta 100 en modo extremo) |
| `batch_delay` | `0` | Pausa entre requests (segundos) | `0-2` |
| `max_retries` | `5` | Reintentos por error | `3-5` |
| `request_timeout` | `60` | Timeout por request | `30-60` |

### **📦 ¿Qué son los LOTES (BATCH_SIZE)?**

El `batch_size` controla **cuántos productos se procesan juntos** en cada "ronda" del script. Es fundamental para optimizar memoria, velocidad y estabilidad.

#### **🔄 Funcionamiento del Procesamiento por Lotes**

```sql
-- En lugar de cargar TODOS los productos (❌ Problemático):
SELECT * FROM producto WHERE rel_empresa_id = 1302; -- 2028 productos en memoria

-- Se procesan en lotes pequeños (✅ Optimizado):
SELECT * FROM producto LIMIT 30 OFFSET 0;   -- Lote 1: productos 1-30
SELECT * FROM producto LIMIT 30 OFFSET 30;  -- Lote 2: productos 31-60
SELECT * FROM producto LIMIT 30 OFFSET 60;  -- Lote 3: productos 61-90
-- ... continúa hasta procesar todos
```

#### **📊 Ejemplo Visual (2028 productos con batch_size=30)**
```
🗄️ Base de Datos: 2028 productos total

📦 Lote 1: Productos 1-30    (OFFSET 0)
📦 Lote 2: Productos 31-60   (OFFSET 30)  
📦 Lote 3: Productos 61-90   (OFFSET 60)
📦 Lote 4: Productos 91-120  (OFFSET 90)
...
📦 Lote 68: Productos 2011-2028 (últimos 18)

Total: 68 lotes de máximo 30 productos cada uno
```

#### **🎯 Ventajas del Procesamiento por Lotes**

| Beneficio | Sin Lotes | Con Lotes (30) |
|-----------|-----------|----------------|
| **Memoria** | ~10MB (todos en RAM) | ~150KB (solo 30 en RAM) |
| **Consultas BD** | 1 consulta lenta | 68 consultas rápidas |
| **Tolerancia a fallos** | Si falla, pierdes todo | Si falla, solo pierdes 30 |
| **Monitoreo** | No hay progreso visible | Progreso lote por lote |

#### **⚙️ Impacto del BATCH_SIZE en Rendimiento**

| BATCH_SIZE | Consultas BD | Memoria Aprox | Velocidad | Estabilidad | Recomendado Para |
|------------|--------------|---------------|-----------|-------------|------------------|
| 5 | 406 | ~25KB | 🐌 Muy Lenta | ⭐⭐⭐⭐⭐ | Servidores muy limitados |
| 10 | 203 | ~50KB | 🐌 Lenta | ⭐⭐⭐⭐ | Pruebas iniciales |
| 30 | 68 | ~150KB | ⚡ Rápida | ⭐⭐⭐ | **Uso general** |
| 50 | 41 | ~250KB | 🚀 Muy Rápida | ⭐⭐ | APIs robustas |
| 100 | 21 | ~500KB | 💥 Extrema | ⭐ | Modo extremo |

### **🎛️ Parámetros URL (GET) - TURBO**

| Parámetro | Obligatorio | Descripción | Ejemplo | Efecto en Velocidad |
|-----------|-------------|-------------|---------|---------------------|
| `id_empresa` | ✅ | ID de la empresa a procesar | `1302` | - |
| `proccess` | ✅ | Activar procesamiento (1=sí) | `1` | - |
| `update` | ⚠️ | Actualizar BD (1=sí, 0=simular) | `1` | - |
| `batch_size` | ❌ | Productos por lote | `40` | ⬆️ Mayor = más rápido |
| `batch_delay` | ❌ | Pausa entre requests | `0` | ⬇️ Menor = más rápido |
| `extreme` | ❌ | Modo extremo (1=activar) | `1` | 🚀 Velocidad máxima |
| `verbose` | ❌ | Logging detallado (1=activar) | `1` | ⬇️ Reduce velocidad |
| `max_retries` | ❌ | Número de reintentos | `5` | ⬇️ Menos = más rápido |
| `timeout` | ❌ | Timeout en segundos | `60` | ⬇️ Menor = más rápido |
| `id_categoria` | ❌ | Filtrar por categoría | `25` | - |
| `id_subcategoria` | ❌ | Filtrar por subcategoría | `108` | - |
| `job_id` | ❌ | ID de job para colas | `job_123` | - |
| **`reprocess`** | ❌ | **Reprocesar errores (1=activar)** | `1` | - |
| **`show_errors`** | ❌ | **Mostrar resumen de errores** | `1` | - |
| **`error_date`** | ❌ | **Fecha de errores a analizar** | `2025-05-30` | - |

## 🚀 Instalación y Configuración

### **1. Requisitos del Sistema**
```bash
# PHP 8.3 (Recomendado) - Compatible desde 7.4+
php --version

# Extensiones requeridas
php -m | grep -E "(pdo|curl|json|mbstring)"
```

### **2. Instalar Extensiones (Ubuntu/Debian)**
```bash
sudo apt update
sudo apt install php-curl php-mysql php-mbstring php-json

# Para máximo rendimiento, instalar también:
sudo apt install php-opcache
```

### **3. Configuración PHP para Velocidad TURBO**
```bash
# Editar php.ini para máximo rendimiento
sudo nano /etc/php/8.3/cli/php.ini

# Agregar estas configuraciones:
memory_limit = -1
max_execution_time = 0
default_socket_timeout = 60
opcache.enable = 1
opcache.memory_consumption = 256
opcache.jit_buffer_size = 256M
opcache.jit = 1235
```

### **4. Configurar el Script**
```php
// Editar configuración de BD en el script:
$db_config = [
    'host' => 'tu_servidor',
    'username' => 'tu_usuario', 
    'password' => 'tu_contraseña',
    'database' => 'tu_bd'
];
```

### **5. Crear Estructura de Archivos**
```bash
mkdir logs
chmod 755 logs
touch logs/.gitkeep
```

### **6. Verificar Configuración**
```bash
# Ejecutar diagnóstico
php test_extensions.php
```

## 📖 Uso del Script TURBO

### **⚡ Configuraciones por Velocidad**

#### **🟢 RÁPIDA (Recomendada - ~40-50 prod/min)**
```bash
php script_optimized.php?id_empresa=1302&batch_size=25&batch_delay=1&proccess=1&update=1
```

#### **🟡 AGRESIVA (~60-80 prod/min)**
```bash
php script_optimized.php?id_empresa=1302&batch_size=35&batch_delay=0&proccess=1&update=1
```

#### **🔴 EXTREMA (~100+ prod/min)**
```bash
php script_optimized.php?id_empresa=1302&batch_size=50&batch_delay=0&extreme=1&proccess=1&update=1
```

#### **🎛️ PERSONALIZADA**
```bash
# Configuración manual de todos los parámetros
php script_optimized.php?id_empresa=1302&batch_size=40&batch_delay=0&max_retries=3&timeout=60&proccess=1&update=1
```

### **🔄 Nuevas Funcionalidades de Uso**

#### **🛠️ Sistema de Reprocesamiento**
```bash
# Reprocesar productos que fallaron en ejecuciones anteriores
php script_optimized.php?id_empresa=1302&reprocess=1&proccess=1&update=1

# Reprocesar con configuración específica
php script_optimized.php?id_empresa=1302&reprocess=1&batch_size=20&batch_delay=2&proccess=1&update=1
```

#### **📊 Análisis de Errores**
```bash
# Mostrar resumen de errores del día actual
php script_optimized.php?id_empresa=1302&show_errors=1

# Mostrar errores de una fecha específica
php script_optimized.php?id_empresa=1302&show_errors=1&error_date=2025-05-29

# Ver estadísticas por tipo de error
php script_optimized.php?id_empresa=1302&show_errors=1&error_date=2025-05-30
```

### **🎯 Filtros y Opciones Avanzadas**

```bash
# Procesar solo una categoría específica
php script_optimized.php?id_empresa=1302&id_categoria=25&batch_size=30&proccess=1&update=1

# Solo simular (no actualizar BD) - Para pruebas
php script_optimized.php?id_empresa=1302&batch_size=20&proccess=1&update=0

# Con logging verbose para debugging
php script_optimized.php?id_empresa=1302&batch_size=15&verbose=1&proccess=1&update=1

# Procesar subcategoría específica
php script_optimized.php?id_empresa=1302&id_subcategoria=108&batch_size=30&proccess=1&update=1
```

### **🌐 Uso con Servidor Web**
```bash
# Iniciar servidor PHP local
php -S localhost:8000

# Ejecutar vía HTTP
curl "http://localhost:8000/script_optimized.php?id_empresa=1302&batch_size=30&proccess=1&update=1"
```

### **⏰ Uso con Cron (Programado)**
```bash
# Editar crontab
crontab -e

# Ejecutar diariamente a las 2:00 AM (configuración rápida)
0 2 * * * /usr/bin/php /ruta/script_optimized.php?id_empresa=1302&batch_size=25&batch_delay=1&proccess=1&update=1 >> /var/log/ia_turbo.log 2>&1

# Ejecutar semanalmente en modo extremo
0 3 * * 0 /usr/bin/php /ruta/script_optimized.php?id_empresa=1302&batch_size=50&extreme=1&proccess=1&update=1 >> /var/log/ia_extremo.log 2>&1

# Reprocesar errores automáticamente cada día a las 4:00 AM
0 4 * * * /usr/bin/php /ruta/script_optimized.php?id_empresa=1302&reprocess=1&proccess=1&update=1 >> /var/log/ia_reprocess.log 2>&1
```

## 📊 Monitoreo y Logs TURBO

### **📁 Archivos de Log Optimizados**
```
logs/
├── ia_batch_2025-05-29_job-manual_empresa-1302.txt     # Ejecución manual empresa 1302
├── ia_batch_2025-05-29_job-turbo123_empresa-1450.txt   # Job específico empresa 1450
├── ia_batch_2025-05-30_job-extremo_empresa-1302.txt    # Modo extremo empresa 1302
├── errors_2025-05-29_empresa-1302.json                 # Errores empresa 1302
├── errors_2025-05-30_empresa-1450.json                 # Errores empresa 1450
└── errors_2025-05-30_empresa-1302.json                 # Errores actuales empresa 1302
```

### **📋 Información TURBO en Logs**
- ⚡ **Velocidad en tiempo real** (productos/minuto)
- 📊 **Progreso dinámico** (procesados/total)
- ⏱️ **ETA automático** (tiempo estimado restante)
- 🧠 **Monitoreo de memoria** cada 10 productos
- 🔄 **Estadísticas de reintentos** detalladas
- 📦 **Progreso por lotes** con offset y tamaño
- 📈 **Resumen final** con métricas de rendimiento
- 🛠️ **Información de reprocesamiento** y errores marcados

### **📊 Ejemplo de Log TURBO en Tiempo Real**
```
[14:23:45] ⚡ Iniciando modo TURBO para empresa: 1302
[14:23:45] Configuración: batch_size=30, delay=0, timeout=60, max_retries=5
[14:23:46] 🎯 Total a procesar: 2028 productos
[14:23:46] 🚀 INICIANDO PROCESAMIENTO TURBO
[14:23:47] 📦 Lote: 30 productos (offset: 0)
[14:23:52] 🔄 Procesando: 438325 - PRESOSTATO MECANICO...
[14:24:15] 📦 Lote: 30 productos (offset: 30)
[14:24:45] ⚡ Velocidad: 67.3 prod/min | Procesados: 60/2028 | ETA: 29.1 min
[14:24:45] 🧠 Memoria: 245.7MB
[14:25:12] 📊 Progreso: 150/2028 | Actualizados: 147 | Errores: 3
[14:25:30] 📦 Lote: 30 productos (offset: 150)
[14:26:15] ERROR REGISTRADO: Producto 438890 - HTTP_400_ERROR: Bad Request Error
[14:27:02] ✓ Producto 439125 actualizado exitosamente
[14:27:45] Intento 3 de 6 para producto 439234
...
[14:52:15] 🏁 === RESUMEN FINAL TURBO ===
[14:52:15] Total procesados: 2028
[14:52:15] Actualizados: 2015
[14:52:15] Errores: 13
[14:52:15] Total reintentos realizados: 87
[14:52:15] ⚡ Velocidad promedio: 71.2 productos/minuto
[14:52:15] 📊 Tasa de éxito: 99.4%
[14:52:15] === INFORMACIÓN DE ERRORES ===
[14:52:15] Archivo de errores generado: logs/errors_2025-05-30_empresa-1302.json
[14:52:15] Para reprocesar errores, ejecuta: script.php?id_empresa=1302&proccess=1&update=1&reprocess=1
```

### **🔍 Comandos de Monitoreo TURBO**
```bash
# Ver logs en tiempo real con información de lotes
tail -f logs/ia_batch_*.txt

# Monitorear solo estadísticas de velocidad y lotes
tail -f logs/ia_batch_*.txt | grep -E "(⚡|📊|📦|ETA)"

# Seguir progreso de lotes específicamente
tail -f logs/ia_batch_*.txt | grep "📦 Lote\|Progreso"

# Buscar errores críticos
grep -i "error\|✗\|fail" logs/ia_batch_*.txt

# Verificar rate limits y problemas de API
grep -i "429\|400\|rate limit\|timeout" logs/ia_batch_*.txt

# Contar productos procesados exitosamente
grep "Actualizados:" logs/ia_batch_*.txt | tail -1

# Ver estadísticas finales del último procesamiento
grep -A 15 "RESUMEN FINAL TURBO" logs/ia_batch_*.txt | tail -20

# Analizar rendimiento por lotes
grep "📦 Lote" logs/ia_batch_*.txt | wc -l  # Contar lotes procesados

# NUEVOS: Monitoreo de errores y reprocesamiento
grep "ERROR REGISTRADO" logs/ia_batch_*.txt | tail -10
grep "reprocesamiento" logs/ia_batch_*.txt
```

### **📈 Interpretación de Métricas TURBO**
```bash
# Ejemplo de salida del log:
[14:23:45] 📦 Lote: 30 productos (offset: 150)
[14:23:52] ⚡ Velocidad: 67.3 prod/min | Procesados: 180/2028 | ETA: 27.1 min
[14:23:52] 🧠 Memoria: 245.7MB
[14:24:15] 📊 Progreso: 210/2028 | Actualizados: 205 | Errores: 5
[14:24:30] ERROR REGISTRADO: Producto 439125 - CURL_ERROR: Connection timeout
[14:24:45] ✓ Producto 439150 reprocesado exitosamente

# Interpretación:
- 📦 Procesando lote de 30 productos (productos 151-180)
- ⚡ Velocidad actual: 67.3 productos por minuto
- 📊 Van 180 procesados de 2028 total
- ⏱️ Tiempo estimado restante: 27.1 minutos
- 🧠 Uso de memoria: 245.7MB
- ✅ 205 productos actualizados exitosamente
- ❌ 5 productos con errores (registrados en JSON)
- 🔄 Sistema de reprocesamiento funcionando
```

## 🆕 Gestión Avanzada de Errores

### **📋 Tipos de Errores Registrados**

El script registra automáticamente diferentes tipos de errores en archivos JSON estructurados:

| Tipo de Error | Descripción | Ejemplo |
|---------------|-------------|---------|
| `CURL_ERROR` | Errores de conexión cURL | Connection timeout, DNS resolution |
| `HTTP_400_ERROR` | Errores de solicitud incorrecta | Bad Request de la API |
| `HTTP_429_ERROR` | Rate limit alcanzado | Too Many Requests |
| `SERVER_ERROR_5XX` | Errores del servidor API | Internal Server Error |
| `JSON_DECODE_ERROR` | Error al decodificar JSON | Respuesta malformada |
| `NO_GENERATED_CONTENT` | Respuesta sin contenido | API respondió pero sin datos |
| `DATABASE_ERROR` | Error de base de datos | Fallo al actualizar producto |
| `MAX_RETRIES_REACHED` | Máximo de reintentos alcanzado | Producto no procesable |

### **📊 Estructura del Archivo de Errores JSON**
```json
[
  {
    "timestamp": "2025-05-30 14:23:45",
    "producto_id": 438890,
    "empresa_id": 1302,
    "error_type": "HTTP_400_ERROR",
    "error_message": "Bad Request Error",
    "http_code": 400,
    "response_preview": "{\"error\":\"Invalid request format\"}",
    "processed": false
  },
  {
    "timestamp": "2025-05-30 14:25:12",
    "producto_id": 439125,
    "empresa_id": 1302,
    "error_type": "CURL_ERROR",
    "error_message": "Connection timeout after 60 seconds",
    "http_code": null,
    "response_preview": null,
    "processed": true,
    "reprocessed_at": "2025-05-30 16:30:45"
  }
]
```

### **🔄 Flujo de Reprocesamiento**

1. **Detección de Errores**: El script registra automáticamente todos los errores en `errors_FECHA_empresa-ID.json`
2. **Análisis de Errores**: Usar `show_errors=1` para ver resumen por tipo de error
3. **Reprocesamiento**: Ejecutar con `reprocess=1` para reintentar solo productos fallidos
4. **Marcado de Éxito**: Los productos reprocesados exitosamente se marcan como `processed: true`
5. **Seguimiento**: Monitor de progreso y estadísticas de reprocesamiento

### **🛠️ Comandos de Gestión de Errores**

```bash
# Ver resumen de errores del día actual
php script_optimized.php?id_empresa=1302&show_errors=1

# Analizar errores de fecha específica
php script_optimized.php?id_empresa=1302&show_errors=1&error_date=2025-05-29

# Reprocesar todos los errores pendientes
php script_optimized.php?id_empresa=1302&reprocess=1&proccess=1&update=1

# Reprocesar con configuración conservadora
php script_optimized.php?id_empresa=1302&reprocess=1&batch_size=10&batch_delay=3&proccess=1&update=1

# Ver estadísticas de archivos de error
ls -la logs/errors_*.json
wc -l logs/errors_*.json
```

### **📈 Análisis de Errores Avanzado**

```bash
# Contar errores por tipo usando jq (si está disponible)
cat logs/errors_2025-05-30_empresa-1302.json | jq -r '.[].error_type' | sort | uniq -c

# Ver errores no procesados
cat logs/errors_2025-05-30_empresa-1302.json | jq '.[] | select(.processed == false)'

# Extraer IDs de productos con errores
cat logs/errors_2025-05-30_empresa-1302.json | jq -r '.[].producto_id' | sort -n

# Ver errores de un tipo específico
cat logs/errors_2025-05-30_empresa-1302.json | jq '.[] | select(.error_type == "HTTP_400_ERROR")'
```

## 🔄 Ejecución Concurrente TURBO

### **✅ Empresas Diferentes (Recomendado)**
```bash
# Terminal 1 - Empresa A (Configuración rápida)
php script_optimized.php?id_empresa=1302&batch_size=20&batch_delay=1&proccess=1&update=1

# Terminal 2 - Empresa B (Configuración rápida)
php script_optimized.php?id_empresa=1450&batch_size=20&batch_delay=1&proccess=1&update=1

# Terminal 3 - Reprocesamiento empresa A
php script_optimized.php?id_empresa=1302&reprocess=1&batch_size=15&proccess=1&update=1
```

### **⚠️ Misma Empresa por Categorías**
```bash
# Terminal 1 - Categorías 1-50
php script_optimized.php?id_empresa=1302&id_categoria=10&batch_size=15&batch_delay=2&proccess=1&update=1

# Terminal 2 - Categorías 51-100  
php script_optimized.php?id_empresa=1302&id_categoria=25&batch_size=15&batch_delay=2&proccess=1&update=1
```

### **📊 Monitoreo de Procesos Concurrentes**
```bash
# Ver procesos TURBO activos
ps aux | grep "script_optimized.php" | grep -v grep

# Monitoreo continuo con uso de recursos
watch -n 10 'ps aux | grep "script_optimized.php" | grep -v grep | awk "{print \$2, \$3, \$4, \$11}"'

# Verificar que no hay conflictos de logs
ls -la logs/ | grep $(date +%Y