<?php

// dominio es obtenido desde conexion.php

// --------------------------------------------------------------------------------------
// ------------- Override dinámico basado en JSON en empresa_configuracion2 -------------

if ($domain_ext == 'com') {

    $config_regional_permitidas = [
        'pais_id', 'pais_language', 'pais_code',
        'tasa_iva', 'tasa_iva2',
        'moneda_simbolo',
        'numero_cant_decimal', 'numero_separador_decimal', 'numero_separador_miles',
        'fecha_separador', 'fecha_formato', 'hora_formato'
    ];

    if ( isset($_SESSION['id_empresa']) && $_SESSION['id_empresa']>0 ) {

        if (!isset($_SESSION['config_regional_json'])) {
            //echo '<br>---> step 1...';
            // Si no existe, consultar
            $sql_conf = "SELECT ec_valor FROM empresa_configuracion2 
                        WHERE ec_nombre='configuracion_regional' 
                        AND rel_empresa_id = '" . (int)$_SESSION['id_empresa'] . "'
                        LIMIT 1";
            $res_conf = mysql_query($sql_conf, $conexion_slave);
            if ($row_conf = mysql_fetch_assoc($res_conf)) {
                $_SESSION['config_regional_json'] = $row_conf['ec_valor']; // Guardar en sesión
            }

        }

        // Si existe configuración dinámica
        if (!empty($_SESSION['config_regional_json'])) {
            //echo '<br>---> step 2...';
            $configuracion_pais = json_decode($_SESSION['config_regional_json'], true);
            if (is_array($configuracion_pais)) {
                foreach ($configuracion_pais as $clave => $valor) {
                    if (in_array($clave, $config_regional_permitidas)) {
                        $_SESSION[$clave] = $valor;
                    }
                }
                $_SESSION['config_pais_loaded'] = true;
            }
        }
    }

}

// --------------------------------------------------------------------------------------
// ------------- Carga de configuración estándar por dominio (solo si no hay JSON) -------------

if (empty($_SESSION['config_pais_loaded'])) {
	//echo '<br>---> step 3...';

    if ($domain_ext == 'cl' || $domain_ext == 'net') {
        include('config_global_sessions-chile.php');
    }
    if ($domain_ext == 'pe') {
        include('config_global_sessions-peru.php');
    }
    if ($domain_ext == 'com') {
        include('config_global_sessions-usa.php');
    }
}

// --------------------------------------------------------------------------------------
// Parametro global - setear en cada refresh update

$_SESSION['software_version'] = '3.1.250415'; // YearMonthDay

?>
