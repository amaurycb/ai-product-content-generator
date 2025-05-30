# 🚀 Script de Generación de Contenido IA para Productos - VERSIÓN TURBO

Script PHP ultra-optimizado para generar automáticamente descripciones, meta tags y contenido SEO para productos usando Inteligencia Artificial con **máxima velocidad de procesamiento**.

## 📋 Descripción

Este script procesa productos de una base de datos y utiliza una API de IA para generar contenido optimizado con **velocidades de hasta 100+ productos por minuto**:

- ✅ **Descripciones cortas y largas** mejoradas por IA
- ✅ **Meta títulos SEO** optimizados para buscadores
- ✅ **Meta descripciones** atractivas y relevantes
- ✅ **Keywords automáticas** para mejor posicionamiento
- ✅ **Historial de cambios** detallado y trazable

## 🎯 Características Principales

- ⚡ **Procesamiento TURBO** - Hasta 100+ productos por minuto
- 🔄 **Reintentos inteligentes** con backoff exponencial optimizado
- 📊 **Logging selectivo** para máximo rendimiento
- 🛡️ **Manejo automático de rate limits** y errores 400/500
- 🧠 **Optimización de memoria** automática
- 📈 **Monitoreo en tiempo real** de velocidad y progreso
- 🏢 **Multi-empresa** con procesamiento independiente
- ⚡ **Optimizado para PHP 8.3** (compatible desde 7.4+)

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
| `max_retries` | `2` | Reintentos por error | `1-3` |
| `request_timeout` | `25` | Timeout por request | `20-30` |

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

#### **🔧 Señales para Ajustar BATCH_SIZE**

**🔻 Reducir batch_size si observas:**
- ❌ Errores de memoria (Out of memory)
- ❌ Consultas BD lentas (>5 segundos)
- ❌ Muchos timeouts de API
- ❌ El servidor se ralentiza

**🔺 Aumentar batch_size si observas:**
- ✅ Memoria muy baja (<100MB usado)
- ✅ Consultas BD rápidas (<0.1 segundos)
- ✅ API responde sin errores
- ✅ Quieres más velocidad

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
| `max_retries` | ❌ | Número de reintentos | `2` | ⬇️ Menos = más rápido |
| `timeout` | ❌ | Timeout en segundos | `20` | ⬇️ Menor = más rápido |
| `id_categoria` | ❌ | Filtrar por categoría | `25` | - |
| `id_subcategoria` | ❌ | Filtrar por subcategoría | `108` | - |
| `job_id` | ❌ | ID de job para colas | `job_123` | - |

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
memory_limit = 2G
max_execution_time = 7200
default_socket_timeout = 30
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
php script.php?id_empresa=1302&batch_size=25&batch_delay=1&proccess=1&update=1
```

#### **🟡 AGRESIVA (~60-80 prod/min)**
```bash
php script.php?id_empresa=1302&batch_size=35&batch_delay=0&proccess=1&update=1
```

#### **🔴 EXTREMA (~100+ prod/min)**
```bash
php script.php?id_empresa=1302&batch_size=50&batch_delay=0&extreme=1&proccess=1&update=1
```

#### **🎛️ PERSONALIZADA**
```bash
# Configuración manual de todos los parámetros
php script.php?id_empresa=1302&batch_size=40&batch_delay=0&max_retries=1&timeout=20&proccess=1&update=1
```

### **🎯 Filtros y Opciones Avanzadas**

```bash
# Procesar solo una categoría específica
php script.php?id_empresa=1302&id_categoria=25&batch_size=30&proccess=1&update=1

# Solo simular (no actualizar BD) - Para pruebas
php script.php?id_empresa=1302&batch_size=20&proccess=1&update=0

# Con logging verbose para debugging
php script.php?id_empresa=1302&batch_size=15&verbose=1&proccess=1&update=1

# Procesar subcategoría específica
php script.php?id_empresa=1302&id_subcategoria=108&batch_size=30&proccess=1&update=1
```

### **🌐 Uso con Servidor Web**
```bash
# Iniciar servidor PHP local
php -S localhost:8000

# Ejecutar vía HTTP
curl "http://localhost:8000/script.php?id_empresa=1302&batch_size=30&proccess=1&update=1"
```

### **⏰ Uso con Cron (Programado)**
```bash
# Editar crontab
crontab -e

# Ejecutar diariamente a las 2:00 AM (configuración rápida)
0 2 * * * /usr/bin/php /ruta/script.php?id_empresa=1302&batch_size=25&batch_delay=1&proccess=1&update=1 >> /var/log/ia_turbo.log 2>&1

# Ejecutar semanalmente en modo extremo
0 3 * * 0 /usr/bin/php /ruta/script.php?id_empresa=1302&batch_size=50&extreme=1&proccess=1&update=1 >> /var/log/ia_extremo.log 2>&1
```

## 📊 Monitoreo y Logs TURBO

### **📁 Archivos de Log Optimizados**
```
logs/
├── ia_batch_2025-05-29_job-manual.txt      # Ejecución manual
├── ia_batch_2025-05-29_job-turbo123.txt    # Job específico
└── ia_batch_2025-05-30_job-extremo.txt     # Modo extremo
```

### **📋 Información TURBO en Logs**
- ⚡ **Velocidad en tiempo real** (productos/minuto)
- 📊 **Progreso dinámico** (procesados/total)
- ⏱️ **ETA automático** (tiempo estimado restante)
- 🧠 **Monitoreo de memoria** cada 50 productos
- 🔄 **Estadísticas de reintentos** detalladas
- 📦 **Progreso por lotes** con offset y tamaño
- 📈 **Resumen final** con métricas de rendimiento

### **📊 Ejemplo de Log TURBO en Tiempo Real**
```
[14:23:45] ⚡ Iniciando modo TURBO para empresa: 1302
[14:23:45] Configuración: batch_size=30, delay=0, timeout=25
[14:23:46] 🎯 Total a procesar: 2028 productos
[14:23:46] 🚀 INICIANDO PROCESAMIENTO TURBO
[14:23:47] 📦 Lote: 30 productos (offset: 0)
[14:23:52] 🔄 Procesando: 438325 - PRESOSTATO MECANICO...
[14:24:15] 📦 Lote: 30 productos (offset: 30)
[14:24:45] ⚡ Velocidad: 67.3 prod/min | Procesados: 60/2028 | ETA: 29.1 min
[14:24:45] 🧠 Memoria: 245.7MB
[14:25:12] 📊 Progreso: 150/2028 | Actualizados: 147 | Errores: 3
[14:25:30] 📦 Lote: 30 productos (offset: 150)
...
[14:52:15] 🏁 === RESUMEN FINAL TURBO ===
[14:52:15] Total procesados: 2028
[14:52:15] Actualizados: 2015
[14:52:15] Errores: 13
[14:52:15] ⚡ Velocidad promedio: 71.2 productos/minuto
[14:52:15] 📊 Tasa de éxito: 99.4%
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
grep -A 10 "RESUMEN FINAL TURBO" logs/ia_batch_*.txt | tail -15

# Analizar rendimiento por lotes
grep "📦 Lote" logs/ia_batch_*.txt | wc -l  # Contar lotes procesados
```

### **📈 Interpretación de Métricas TURBO**
```bash
# Ejemplo de salida del log:
[14:23:45] 📦 Lote: 30 productos (offset: 150)
[14:23:52] ⚡ Velocidad: 67.3 prod/min | Procesados: 180/2028 | ETA: 27.1 min
[14:23:52] 🧠 Memoria: 245.7MB
[14:24:15] 📊 Progreso: 210/2028 | Actualizados: 205 | Errores: 5

# Interpretación:
- 📦 Procesando lote de 30 productos (productos 151-180)
- ⚡ Velocidad actual: 67.3 productos por minuto
- 📊 Van 180 procesados de 2028 total
- ⏱️ Tiempo estimado restante: 27.1 minutos
- 🧠 Uso de memoria: 245.7MB
- ✅ 205 productos actualizados exitosamente
- ❌ 5 productos con errores
```

## 🔄 Ejecución Concurrente TURBO

### **✅ Empresas Diferentes (Recomendado)**
```bash
# Terminal 1 - Empresa A (Configuración rápida)
php script.php?id_empresa=1302&batch_size=20&batch_delay=1&proccess=1&update=1

# Terminal 2 - Empresa B (Configuración rápida)
php script.php?id_empresa=1450&batch_size=20&batch_delay=1&proccess=1&update=1
```

### **⚠️ Misma Empresa por Categorías**
```bash
# Terminal 1 - Categorías 1-50
php script.php?id_empresa=1302&id_categoria=10&batch_size=15&batch_delay=2&proccess=1&update=1

# Terminal 2 - Categorías 51-100  
php script.php?id_empresa=1302&id_categoria=25&batch_size=15&batch_delay=2&proccess=1&update=1
```

### **📊 Monitoreo de Procesos Concurrentes**
```bash
# Ver procesos TURBO activos
ps aux | grep "script.php" | grep -v grep

# Monitoreo continuo con uso de recursos
watch -n 10 'ps aux | grep "script.php" | grep -v grep | awk "{print \$2, \$3, \$4, \$11}"'

# Verificar que no hay conflictos de logs
ls -la logs/ | grep $(date +%Y-%m-%d)
```

## 🛠️ Solución de Problemas TURBO

### **❌ Errores Comunes y Soluciones**

#### **Error: curl_init() undefined**
```bash
sudo apt install php8.3-curl
sudo systemctl restart apache2
php -m | grep curl  # Verificar instalación
```

#### **Error: PDO not found**
```bash
sudo apt install php8.3-mysql
sudo systemctl restart php8.3-fpm  # Si usas FPM
```

#### **⚠️ Muchos Errores 400 de la API**
```bash
# Reducir agresividad
php script.php?id_empresa=1302&batch_size=20&batch_delay=2&max_retries=3&proccess=1&update=1
```

#### **⚠️ Rate Limit (429) Frecuente**
```bash
# Configuración más conservadora
php script.php?id_empresa=1302&batch_size=15&batch_delay=3&proccess=1&update=1
```

#### **🐌 Velocidad Más Lenta de lo Esperado**
```bash
# 1. Verificar configuración PHP
php -i | grep -E "(memory_limit|max_execution_time|opcache)"

# 2. Optimizar batch_size - Probar tamaños diferentes
php script.php?id_empresa=1302&batch_size=20&proccess=1&update=1  # Reducir si BD lenta
php script.php?id_empresa=1302&batch_size=40&proccess=1&update=1  # Aumentar si BD rápida

# 3. Optimizar MySQL temporalmente
mysql -u root -p -e "SET GLOBAL query_cache_size = 268435456;"

# 4. Verificar que no hay muchos lotes pequeños innecesarios
grep "📦 Lote" logs/ia_batch_*.txt | head -5  # Ver tamaño de lotes actuales
```

#### **🧠 Problemas de Memoria**
```bash
# 1. Aumentar memoria en php.ini
echo "memory_limit = 4G" >> /etc/php/8.3/cli/php.ini

# 2. Reducir batch_size para usar menos memoria
php script.php?id_empresa=1302&batch_size=10&proccess=1&update=1

# 3. Verificar uso actual de memoria en logs
grep "🧠 Memoria" logs/ia_batch_*.txt | tail -5

# 4. Monitorear memoria del sistema en tiempo real
watch -n 5 "free -h && echo '---' && ps aux | grep script.php | grep -v grep"
```

#### **📦 Problemas con Lotes (Batch Processing)**
```bash
# Si los lotes son muy grandes y causan problemas:
php script.php?id_empresa=1302&batch_size=15&proccess=1&update=1

# Si los lotes son muy pequeños y es muy lento:
php script.php?id_empresa=1302&batch_size=45&proccess=1&update=1

# Verificar cuántos lotes se están generando:
echo "Con batch_size=30: $((2028 / 30 + 1)) lotes aproximados"
echo "Con batch_size=50: $((2028 / 50 + 1)) lotes aproximados"

# Monitorear progreso de lotes en tiempo real:
tail -f logs/ia_batch_*.txt | grep "📦 Lote\|📊 Progreso"
```

## 📈 Optimización Avanzada TURBO

### **🚀 Para Máximo Rendimiento**
```bash
# Configuración extrema (solo si la API aguanta)
php script.php?id_empresa=1302&batch_size=60&batch_delay=0&extreme=1&max_retries=1&timeout=15&proccess=1&update=1
```

### **🛡️ Para Máxima Estabilidad**
```bash
# Configuración equilibrada y confiable
php script.php?id_empresa=1302&batch_size=20&batch_delay=1&max_retries=3&timeout=30&proccess=1&update=1
```

### **⚖️ Configuración Balanceada (Recomendada)**
```bash
# Mejor relación velocidad/estabilidad
php script.php?id_empresa=1302&batch_size=30&batch_delay=0&max_retries=2&timeout=25&proccess=1&update=1
```

### **💾 Optimizaciones del Sistema**
```bash
# Configuración MySQL para velocidad
mysql -u root -p -e "
SET GLOBAL innodb_buffer_pool_size = 1073741824;
SET GLOBAL query_cache_size = 268435456;
SET GLOBAL query_cache_type = ON;
"

# Verificar configuración PHP
php --ini
cat /etc/php/8.3/cli/conf.d/99-turbo.ini
```

## 📊 Métricas de Rendimiento Esperadas

### **🎯 Velocidades por Configuración (Estimadas)**

| Configuración | Productos/Minuto | Tiempo (2000 productos) | Uso API | Estabilidad |
|---------------|------------------|--------------------------|---------|-------------|
| **Conservadora** | 20-30 | 67-100 min | Bajo | ⭐⭐⭐⭐⭐ |
| **Rápida** | 40-50 | 40-50 min | Medio | ⭐⭐⭐⭐ |
| **Agresiva** | 60-80 | 25-33 min | Alto | ⭐⭐⭐ |
| **Extrema** | 100+ | <20 min | Muy Alto | ⭐⭐ |

### **📈 Factores que Afectan la Velocidad**
- 🌐 **Latencia de red** a la API
- 💾 **Velocidad de la base de datos**
- 🧠 **Memoria disponible** en el servidor
- ⚡ **Capacidad de la API** de IA
- 📝 **Complejidad de los productos** (nombres largos, etc.)

## 🔐 Seguridad y Mejores Prácticas

### **🛡️ Seguridad**
- 🔒 **API Key hardcodeada** (cambiar en producción)
- 🛡️ **Validación de empresa** antes de procesar
- 📝 **Logs no contienen datos sensibles**
- 🔄 **Transacciones independientes** por producto

### **📋 Mejores Prácticas**
- 🔄 **Backup de BD** antes de procesamiento masivo
- 📊 **Monitoreo de logs** durante ejecución
- ⏰ **Programar en horarios de baja carga**
- 🧪 **Probar con `update=0`** antes de actualizar
- 📈 **Incrementar velocidad gradualmente**

## 🚀 Comandos de Inicio Rápido

### **🎯 Para Empezar (Configuración Segura)**
```bash
# 1. Verificar sistema
php test_extensions.php

# 2. Prueba sin actualizar
php script.php?id_empresa=1302&batch_size=10&proccess=1&update=0

# 3. Ejecución real (configuración rápida)
php script.php?id_empresa=1302&batch_size=25&batch_delay=1&proccess=1&update=1
```

### **⚡ Para Usuarios Avanzados**
```bash
# Configuración agresiva directa
php script.php?id_empresa=1302&batch_size=35&batch_delay=0&proccess=1&update=1

# Modo extremo (solo para APIs robustas)
php script.php?id_empresa=1302&batch_size=50&extreme=1&proccess=1&update=1
```

---

## 📞 Soporte y Documentación

### **🐛 Reportar Problemas**
Incluir en el reporte:
- 📋 Logs completos del error
- 🔧 Versión de PHP y extensiones
- 📊 Parámetros de ejecución utilizados
- 🗄️ Configuración de base de datos
- ⚡ Velocidad observada vs esperada

### **💡 Mejoras Futuras**
- 🔄 Sistema de colas


---
