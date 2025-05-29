<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

// paso 1 : obtiene subdominio

// dominio
$domain = $_SERVER['HTTP_HOST'];
if (empty($domain)) {
	// get domain in cronjobs
	$host_ = getopt('h:');
	$domain = $host_['h'];
	$via_cron = 1;
}
$domain = strtolower($domain);

$domain_ = $domain;
$domain_ = trim($domain_);
$domain_ = explode(".", $domain_);
$domain_ext = end($domain_);
$domain_sub = reset($domain_);
$domain_parts = count($domain_);


$domain = str_replace('http://', '', $domain);
$domain = str_replace('https://', '', $domain);
$domain = str_replace('www.', '', $domain);
$domain_www = 'www.' . $domain;


$_SESSION['client_domain'] = $domain;
$_SESSION['client_domain_ext'] = $domain_ext;


if (!isset($client_portal)) {

	// detecta subdominio cliente
	$domain2 = $domain;
	$domain2 = str_replace('obuma.cl', '', $domain2);
	$domain2 = str_replace('obuma.pe', '', $domain2);
	$domain2 = str_replace('obuma.co', '', $domain2);
	$domain2 = str_replace('obuma.mx', '', $domain2);
	$domain2 = str_replace('obuma.com', '', $domain2);
	$domain2 = str_replace('obuma.es', '', $domain2);
	$domain2 = str_replace('obuma.net', '', $domain2);
	$domain2 = str_replace('megapos.cl', '', $domain2);



	$exp = explode(".", $domain2);
	//print_r($exp);
	$client_portal = trim(strtolower($exp[0]));
	$client_pool = trim(strtolower($exp[1]));

	if (empty($client_pool)) {
		//$client_portal = '';
	}
	if (empty($client_pool) and !empty($client_portal)) {
		$client_pool = $client_portal;
		$client_portal = '';
	}

	$_SESSION['client_portal'] = $client_portal;
	$_SESSION['client_pool'] = $client_pool;

	// obtenemos el dominio y la extension

	$domain2_ = array_reverse($domain_);
	$domain2 = $domain2_[1] . '.' . $domain2_[0];

	/*
	print_r($ext);
	echo '<br>$domain_ext:'.$domain_ext;
	echo '<br>$client_portal:'.$client_portal;
	echo '<br>$client_pool:'.$client_pool;
	*/
}



/* Conexion segun pais por extension dominio
---------------------------------------------------------------------- */
if ($domain_ext == 'cl' or $domain_ext == 'net') {
	include('conexion-chile.php');
}
if ($domain_ext == 'pe') {
	include('conexion-peru.php');
}
if ($domain_ext == 'com') {
	include('conexion-usa.php');
}


//echo '<br>$company_info:';


/* Conexion REDIS
---------------------------------------------------------------------- */
include_once 'redis/ObumaRedis.php';

$redis = ObumaRedis::connect();

$redis_slave = ObumaRedis::connect("read");
