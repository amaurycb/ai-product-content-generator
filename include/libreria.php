<?php





//******************************************************************************************
// PRODUCTO
//******************************************************************************************

// Devuelve el id del producto si existe en catalago
// de lo contrario devuelve 0
function Get_ProductoExiste ( $id_producto ) {

	global $redis;
	global $redis_slave;

	$dato = 0;

	if( $id_producto>0 ){
		
		$sql = "SELECT producto_id FROM producto WHERE producto_id='".$id_producto."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1 ";
		
		$key_sql = 'SQL:'.md5($sql);

			
	    if( $redis_slave->exists($key_sql) and is_null($caching) ){

	        $row = json_decode($redis_slave->get($key_sql),true);
	        $row = array_map('utf8_decode',$row);
	        //echo '<br>From REDIS....';

	    } else { 
		
	    	$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
			$row = mysql_fetch_array($result);
	    
			//
			$redis->set($key_sql,json_encode(array_map('utf8_encode',$row)));
	        $redis->expire($key_sql,3600);
			
			//echo '<br>From MySql....';

		}
		
		if ( $row['producto_id']>0 ) {	
			$dato = $row['producto_id'];
		}

	}
		
	return $dato;
}




function Get_Producto ( $id_producto, $caching = null, $columns = null ) {

	$row = '';

	if( $id_producto>0 ){
			
		$sql = "SELECT * FROM producto WHERE producto_id='".$id_producto."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1 ";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
	
	}		
			
		
	return $row;
}


function Get_Producto_ByCodigo ( $codigo ) {
	
	$row = '';

	if( !empty($codigo) ){

		$sql = "SELECT * FROM producto WHERE producto_codigo_comercial='".mysql_real_escape_string($codigo)."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1 ";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
	}
		
	return $row;
}





function Get_ProductoId_ByCodigo ( $codigo ) {


	$dato = 0;
	
	if( !empty($codigo) ){	

		$sql = "SELECT producto_id FROM producto WHERE ( producto_codigo_comercial='".mysql_real_escape_string($codigo)."' OR producto_codigo_barra='".mysql_real_escape_string($codigo)."' ) AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			if($row['producto_id']>0){
				$dato = $row['producto_id'];
			}
	
	}

	return $dato;
}


function Get_ProductoId_ByCodigoComercial ( $codigo ) {

	$dato = 0;
	
	if( !empty($codigo) ){	

		$sql = "SELECT producto_id FROM producto WHERE producto_codigo_comercial='".mysql_real_escape_string($codigo)."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			if($row['producto_id']>0){
				$dato = $row['producto_id'];
			}
	
	}

	return $dato;
}


function Get_ProductoId_ByCodigoBarra ( $codigo ) {

	$dato = 0;
	
	if( !empty($codigo) ){	

		$sql = "SELECT producto_id FROM producto WHERE producto_codigo_barra='".mysql_real_escape_string($codigo)."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			if($row['producto_id']>0){
				$dato = $row['producto_id'];
			}
	
	}

	return $dato;
}



/*
Devuelve el Id de un producto a partir del codigo del producto del proveedor
*/
function Get_ProductoId_ByCodigoProveedor ( $codigo, $id_proveedor ) {

	$dato = 0;
	
	if( !empty($codigo) ){	

		$sql = "SELECT rel_producto_id FROM producto_proveedor_codigos 
				INNER JOIN producto ON producto.producto_id=producto_proveedor_codigos.rel_producto_id 
				WHERE producto_proveedor_codigos.codigo_producto='".mysql_real_escape_string($codigo)."' AND producto_proveedor_codigos.rel_proveedor_id='".$id_proveedor."' AND producto.rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1 ";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			if($row['rel_producto_id']>0){
				$dato = $row['rel_producto_id'];
			}

	}
		
	return $dato;
}



/*
Devuelve el codigo del proveedor de un producto
@param $id_producto
@param $id_proveedor
*/
function Get_ProductoCodigoProveedor ( $id_producto, $id_proveedor ) {

	$dato = '';
	
	if( $id_producto>0 and $id_proveedor>0 ){
		
		$sql = "SELECT codigo_producto FROM producto_proveedor_codigos 
				INNER JOIN producto ON producto.producto_id=producto_proveedor_codigos.rel_producto_id 
				WHERE producto_proveedor_codigos.rel_producto_id='".$id_producto."' AND producto_proveedor_codigos.rel_proveedor_id='".$id_proveedor."' AND producto.rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1 ";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			if( !empty($row['codigo_producto']) ){
				$dato = $row['codigo_producto'];
			}

	}	
		
	return $dato;
}









function Get_ProductoNombre ( $id_producto ) {

	$dato = 0;
	
	if( $id_producto>0 ){	

		$sql = "SELECT producto_nombre FROM producto WHERE producto_id='".$id_producto."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			$dato = $row['producto_nombre'];
		
	}

	return $dato;
}



function Get_ProductoCodigoComercial ( $id_producto ) {

	$dato = 0;

	if( $id_producto>0 ){
		
		$sql = "SELECT producto_codigo_comercial FROM producto WHERE producto_id='".$id_producto."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			$dato = $row['producto_codigo_comercial'];

	}
		
	return $dato;
}


function Get_ProductoCodigoSku ( $id_producto ) {

	$dato = 0;

	if( $id_producto>0 ){
		
		$sql = "SELECT producto_codigo_comercial FROM producto WHERE producto_id='".$id_producto."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			$dato = $row['producto_codigo_comercial'];
	
	}

	return $dato;
}



function Get_ProductoCodigoBarra ( $id_producto ) {

	$dato = 0;

	if( $id_producto>0 ){
		
		$sql = "SELECT producto_codigo_barra FROM producto WHERE producto_id='".$id_producto."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			$dato = $row['producto_codigo_barra'];
	
	}

	return $dato;
}



function Get_ProductoPeso ( $id_producto ) {

	$dato = 0;

	if( $id_producto>0 ){
		
		$sql = "SELECT producto_peso_fisico FROM producto WHERE producto_id='".$id_producto."' AND rel_empresa_id='".$_SESSION['id_empresa']."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			$dato = $row['producto_peso_fisico'];
		
	}

	return $dato;
}



function Get_ProductoImagen_webroot () {
	
	// url final
	$imagen = $_SESSION['webroot_data'].'/DocumentosGenerados/Empresa-'.$_SESSION['id_empresa'].'/imagenes_productos';
	
	// url actual, sera remplazada por la final
	$imagen = $_SESSION['webroot_data'].'/imagenes_productos';
	

	
	return $imagen;
}



function Get_ProductoImagen_serverroot () {
	
	// url final
	$imagen_root = $_SESSION['serverroot_data'].'/DocumentosGenerados/Empresa-'.$_SESSION['id_empresa'].'/imagenes_productos';

	// url actual, sera remplazada por la final
	$imagen_root = $_SESSION['serverroot_data'].'/imagenes_productos';


	
	return $imagen_root;
}


function Get_ProductoImagen_s3root () {
	
	// url final
	$imagen_root = 'DocumentosGenerados/Empresa-'.$_SESSION['id_empresa'].'/imagenes_productos';

	
	return $imagen_root;
}



/*
Devuelve el nombre de la imagen del producto,
Solo el nombre de la imagen, no devuelve la ruta
*/
function Get_ProductoImagen ( $id_producto, $ruta = null ) {
	
	$imagen_producto = '';
	$imagen_defecto = "imagen-no-disponible.jpg";

	if( $id_producto>0 ){
		
		if( $id_producto>0 ){

			$sql_imagen = "SELECT producto_imagen_id, producto_imagen_url, s3_link FROM producto_imagen WHERE rel_producto_id='".$id_producto."' ORDER BY producto_imagen_posicion ASC LIMIT 1";
			$result_imagen = mysql_query($sql_imagen, $GLOBALS["conexion_slave"]) or die(mysql_error());
			$row_imagen = mysql_fetch_array($result_imagen);

			$imagen_producto = $row_imagen['producto_imagen_url'];
		
		}
		
		
		

		
		$imagen = $imagen_producto;
		//$imagen_root = Get_ProductoImagen_serverroot().'/'.$imagen_producto;

		// si no existe la imagen fisicamente, devolvemos la default
		/////if( empty($imagen_producto) or !file_exists($imagen_root) ){
		if( empty($imagen_producto) ){
		  
		  $imagen = 'https://app.obuma.cl/obuma2.0/imagenes/'.$imagen_defecto;
		
		} else {

			// devuelve la imagen completa, incluida la ruta
			if( !is_null($ruta) ){
				// 1 -> webroot
				if($ruta==1){
					$imagen = Get_ProductoImagen_webroot().'/'.$imagen;
				}
				// 2 -> serverroot
				if($ruta==2){
					$imagen = Get_ProductoImagen_serverroot().'/'.$imagen;
				}
			}

		}

		// nueva ruta desde S3
		if( !empty($row_imagen['s3_link']) and $row_imagen['s3_link']!='error' ){
	        $imagen = $row_imagen['s3_link'];
	    }

	} else {
		$imagen = 'https://app.obuma.cl/obuma2.0/imagenes/'.$imagen_defecto;
	}

	
	return $imagen;
}




/*
Devuelve la imagen del producto desde el nombre de la imagen principal
*/
function Get_ProductoImagen2 ( $imagen_producto, $thumb_size = null, $id_empresa2 = null ) {

	//$imagen_defecto = "imagen-no-disponible.jpg";

	$id_empresa = $_SESSION['id_empresa'];
	if( $id_empresa2>0 ){
		$id_empresa = $id_empresa2;
	}


	if( empty($imagen_producto) ){
	  
	  	$imagen = 'https://cdn-productos.obuma.cl/imagen-no-disponible_thumb_128.jpg';
	
	} else {


		$imagen_producto = end( explode('/', $imagen_producto) );

		if( $thumb_size>0 ){
			    // Obtener la extensión del archivo
			    $extension = pathinfo($imagen_producto, PATHINFO_EXTENSION);
			    
			    // Obtener el nombre del archivo sin la extensión
			    $nombre_sin_extension = pathinfo($imagen_producto, PATHINFO_FILENAME);
			    
			    // Construir el nuevo nombre del archivo con _thumb_128
			    $imagen_producto = $nombre_sin_extension . "_thumb_128." . $extension;
		}



		$imagen_root = 'https://cdn-productos.obuma.cl/Empresa-'.$id_empresa;
		$imagen = $imagen_root.'/'.$imagen_producto;

	}

	return $imagen;
}


/*
Devuelve la imagen del producto desde el cdn a partir del link s3
Cambia : https://obuma-cl.s3.us-east-2.amazonaws.com/imagenes_productos/Empresa-2/cod2-p668022-8a7a65ee5c3cc183e42225e814d8be65.jpg
Por    : https://cdn-productos.obuma.cl/Empresa-2/cod2-p47156-chuleta_cerdo.jpg

*/
function Get_ProductoImagenCdn ( $imagen_producto ) {

	$web_root_image_products = 'https://cdn-productos.obuma.cl';


	$imagen = $imagen_producto; // $image["s3_link"];
		
	// Dividir la URL en partes usando "/" como delimitador
	$parts = explode('/', $imagen);
	// Obtener los dos últimos valores del array
	$last_two_values = array_slice($parts, -2);
	$last_two_values = implode('/', $last_two_values);

	$imagen = $web_root_image_products.'/'.$last_two_values;

	return $imagen;
}


/*
Devuelve un array con todas las imagenes del producto
*/
function Get_ProductoImagenes ( $id_producto, $ruta = null ) {
	
	$imagen_producto = '';
	$imagenes = array();

	$count_imagenes = 1;
	
	if( $id_producto>0 ){

		$sql_imagen = "SELECT producto_imagen_id, producto_imagen_url, s3_link FROM producto_imagen WHERE rel_producto_id='".$id_producto."' ORDER BY producto_imagen_posicion ASC";
		$result_imagen = mysql_query($sql_imagen, $GLOBALS["conexion_slave"]) or die(mysql_error());
		while ( $row_imagen = mysql_fetch_array($result_imagen) ){

			$imagen_producto = $row_imagen['producto_imagen_url'];
		
		
			$imagen_defecto = "imagen-no-disponible.jpg";
			

			
			$imagen = $imagen_producto;
			$imagen_root = Get_ProductoImagen_serverroot().'/'.$imagen_producto;

			// si no existe la imagen fisicamente, devolvemos la default
			if( empty($imagen_producto) or !file_exists($imagen_root) ){
			  
			  $imagen = $imagen_defecto;
			
			}

			// devuelve la imagen completa, incluida la ruta
			if( !is_null($ruta) ){
				// 1 -> webroot
				if($ruta==1){
					$imagen = Get_ProductoImagen_webroot().'/'.$imagen;
				}
				// 2 -> serverroot
				if($ruta==2){
					$imagen = Get_ProductoImagen_serverroot().'/'.$imagen;
				}
			}

			// nueva ruta desde S3
			if( !empty($row_imagen['s3_link']) and $row_imagen['s3_link']!='error' ){
		        $imagen = $row_imagen['s3_link'];
		    }



			$imagenes[] = array(
							'id'   => $row_imagen['producto_imagen_id'],
	        				'posicion' => $count_imagenes,
	        				'name' => $file_name, 
	        				'url'  => $imagen
							);


			$count_imagenes++;

		} mysql_free_result( $result_imagen );

	}

	
	return $imagenes;
}



function Get_ProductoBrochures ( $id_producto ) {

	$dato = array();

	$count_brochure = 1;

		// Brochures
		// -------------- Links de Brochures externos, no alojados en obuma
		$brochures = $producto['producto_brochure'];
		$brochures = explode(';', $brochures);
		// quitamos los elementos vacios
		$brochures = array_filter($brochures);

	
		
		foreach ($brochures as $brochure) {
			
			$dato[] = array( 
							'id'   => '',
	        				'posicion' => $count_brochure,
	        				'name' => '',
							'url' => $brochure 
							);
			
		$count_brochure++;
		}



	    // -------------- Brochures del producto alojados en obuma
	    
	    
	    $query_brochures = "SELECT * FROM producto_brochure WHERE rel_producto_id='".$id_producto."' ORDER BY producto_brochure_id ASC";
	    $result_brochures = mysql_query($query_brochures, $GLOBALS["conexion_slave"]) or die(mysql_error());
	    while ( $row_brochures = mysql_fetch_array($result_brochures) ){


	        //
	        $file_name = $row_brochures['producto_brochure_nombre'];
	        //
	        if( empty($row_brochures['producto_brochure_nombre']) ){
	            $file_name = $row_brochures['producto_brochure_url'];
	        }

	        $file_url = $_SESSION['webroot_data']."/DocumentosGenerados/Empresa-".$_SESSION['id_empresa']."/files_productos/".$row_brochures['producto_brochure_url'];

	        if( !empty($row_brochures['s3_link']) ){
	            $file_url = $row_brochures['s3_link'];
	        }


	        $dato[] = array( 
	        				'id'   => $row_brochures['producto_brochure_id'],
	        				'posicion' => $count_brochure,
	        				'name' => $file_name, 
	        				'url'  => $file_url
	        			 );


	    $count_brochure ++;
	    }
	    mysql_free_result($result_brochures);

    return $dato;
}



function Get_ProductoMarcaNombre ( $id_fabricante ) {
	
	$sql = "SELECT producto_fabricante_nombre FROM producto_fabricante WHERE producto_fabricante_id='".$id_fabricante."' LIMIT 1";
	$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die(mysql_error());
	$row = mysql_fetch_array($result);
	
	$nombre = $row['producto_fabricante_nombre'];
	
	
	return $nombre;
}



function Get_ProductoFabricanteNombre ( $id_fabricante ) {
	
	$sql = "SELECT producto_fabricante_nombre FROM producto_fabricante WHERE producto_fabricante_id='".$id_fabricante."' LIMIT 1";
	$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die(mysql_error());
	$row = mysql_fetch_array($result);
	
	$nombre = $row['producto_fabricante_nombre'];
	
	
	return $nombre;
}




function Get_ProductoCostoEstandar ( $id_producto ) {

	$dato = 0;
		
		$sql = "SELECT producto_costo_clp_neto_estandar FROM producto WHERE producto_id='".$id_producto."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			$dato = $row['producto_costo_clp_neto_estandar'];
		
	return $dato;
}



function Get_ProductoCostoPromedio ( $id_producto ) {

	$dato = 0;
		
		$sql = "SELECT producto_costo_clp_neto_promedio FROM producto WHERE producto_id='".$id_producto."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			$dato = $row['producto_costo_clp_neto_promedio'];
		
	return $dato;
}



function Get_ProductoCosto ( $id_producto ) {

	$dato = 0;
		
		$sql = "SELECT producto_costo_clp_neto FROM producto WHERE producto_id='".$id_producto."' LIMIT 1";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			$dato = $row['producto_costo_clp_neto'];
		
	return $dato;
}





function Set_ProductoHistory ( $status, $descripcion, $producto_id, $usuario=1 ) {
		
		if($usuario==0){
			$id_user = 0;
		} else {
			$id_user = $_SESSION['id_user'];
		}
		
		$sql = "INSERT INTO producto_history (ph_status, ph_descripcion, ph_fecha, rel_usuario_id, rel_producto_id, rel_empresa_id) VALUES ('".$status."', '".$descripcion."', '".date('Y-m-d H:i:s')."', '".$id_user."', '".$producto_id."', '".$_SESSION['id_empresa']."') ";
		$result = mysql_query($sql) or die( mysql_error() );
		
}



//***************************************************************************************************************************
// Stock


function Get_ProductoStock ( $id_producto, $id_bodega, $lote = null ) {

	//echo '0000000';
	
	$stock = 0;
	
	if( $id_bodega == 0 ){
	// saldo en todas las bodegas
	
		$query_bodegas = "SELECT empresa_bodega_id, empresa_bodega_externas_link FROM empresa_bodega WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY empresa_bodega_nombre ASC";
		$result_bodegas = mysql_query( $query_bodegas, $GLOBALS["conexion_slave"] ) or die( mysql_error() );
		while( $row_bodegas = mysql_fetch_array($result_bodegas) ){

			$external_link = $row_bodegas['empresa_bodega_externas_link'];
			//echo $row_bodegas['empresa_bodega_externas_link'];
			
			if( !empty($external_link) ){
				//echo '111';

				// consulta saldo stock desde un link externo
				// el link devuelve un json con el stock

				$sku = Get_ProductoCodigoComercial ( $id_producto );

				// 
				// https://obuma.cl/obuma2.0/webservices/obuma_replicabodegas/get_stock.php?id_empresa=2&id_bodega=&sku_producto={SKU}
				$external_link = str_replace('{SKU}', $sku, $external_link);

				//echo '<br>link:'.$external_link;

				// obtiene desde link
				$link_stock = file_get_contents($external_link);

				//echo '<br>json:'.$link_stock;
				
				/*
				$link_stock_ejemploooo = '
				{
				  "id_bodega":"33",
				  "id_producto":"10520",
				  "stock":"5"
				}';
				*/
				$a = json_decode($link_stock, true);
				//print_r($a);
				//echo '<br>stock: '.$a['stock'];

				if( !empty($a['stock']) ){
					$stock = $a['stock'];
				}


			} else {

				// consulta saldo en obuma	

				$stock_b = 0;
			
				$query_saldo = "SELECT pi_saldo, pi_lote_saldo FROM producto_inventario WHERE rel_producto_id='".$id_producto."' AND pi_lote='".$lote."' AND rel_bodega_id='".$row_bodegas['empresa_bodega_id']."' ORDER BY pi_id DESC LIMIT 1";
				if( is_null($lote) or empty($lote) ){
					$query_saldo = "SELECT pi_saldo, pi_lote_saldo FROM producto_inventario WHERE rel_producto_id='".$id_producto."' AND rel_bodega_id='".$row_bodegas['empresa_bodega_id']."' ORDER BY pi_id DESC LIMIT 1";
				}

				$result_saldo = mysql_query($query_saldo, $GLOBALS["conexion_slave"]) or die ( mysql_error() );
				$saldo = mysql_fetch_array($result_saldo);

				if( is_null($lote) or empty($lote) ){
					if( !empty($saldo['pi_saldo']) ){
						$stock_b = $saldo['pi_saldo'];
					}
				} else {
					if( !empty($saldo['pi_lote_saldo']) ){
						$stock_b = $saldo['pi_lote_saldo'];
					}
				}
				
				
				$stock = ( $stock + $stock_b );

			}
		
		} mysql_free_result($result_bodegas);


	} else {
	// saldo en una bodega

		$query_bodegas = "SELECT empresa_bodega_id, empresa_bodega_externas_link FROM empresa_bodega WHERE empresa_bodega_id='".$id_bodega."' ";
		$result_bodegas = mysql_query( $query_bodegas, $GLOBALS["conexion_slave"] ) or die( mysql_error() );
		$row_bodegas = mysql_fetch_array($result_bodegas);

				
		$external_link = $row_bodegas['empresa_bodega_externas_link'];
		//echo $row_bodegas['empresa_bodega_externas_link'];
		
		if( !empty($external_link) ){
			//echo '111';

			// consulta saldo stock desde un link externo
			// el link devuelve un json con el stock

			$sku = Get_ProductoCodigoComercial ( $id_producto );

			// 
			// https://obuma.cl/obuma2.0/webservices/obuma_replicabodegas/get_stock.php?id_empresa=2&id_bodega=&sku_producto={SKU}
			$external_link = str_replace('{SKU}', $sku, $external_link);

			//echo '<br>link:'.$external_link;

			// obtiene desde link
			$link_stock = file_get_contents($external_link);

			//echo '<br>json:'.$link_stock;
			
			/*
			$link_stock_ejemploooo = '
			{
			  "id_bodega":"33",
			  "id_producto":"10520",
			  "stock":"5"
			}';
			*/
			$a = json_decode($link_stock, true);
			//print_r($a);
			//echo '<br>stock: '.$a['stock'];

			if( !empty($a['stock']) ){
				$stock = $a['stock'];
			}


		} else {
			//echo '222';
		

			// consulta saldo en obuma	
			$query_saldo = "SELECT pi_saldo, pi_lote_saldo FROM producto_inventario WHERE rel_producto_id='".$id_producto."' AND pi_lote='".$lote."' AND rel_bodega_id='".$id_bodega."' ORDER BY pi_id DESC LIMIT 1";
			
			if( is_null($lote) or empty($lote) ){
				$query_saldo = "SELECT pi_saldo, pi_lote_saldo FROM producto_inventario WHERE rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' ORDER BY pi_id DESC LIMIT 1";
			}
			//echo $query_saldo;

			$result_saldo = mysql_query($query_saldo, $GLOBALS["conexion_slave"]) or die ( mysql_error() );
			$saldo = mysql_fetch_array($result_saldo);
			
			if( is_null($lote) or empty($lote) ){
				if( !empty($saldo['pi_saldo']) ){
					$stock = $saldo['pi_saldo'];
				}
			} else {
				if( !empty($saldo['pi_lote_saldo']) ){
					$stock = $saldo['pi_lote_saldo'];
				}
			}


		}





	}

		
	return $stock;
}

/*

Devuelve el stock positivo de un producto
Solo cuando se consulta todas las bodegas

*/
function Get_ProductoStockReal ( $id_producto, $id_bodega ) {
	
	$stock = 0;
	
	if( $id_bodega == 0 ){
	
		$query_bodegas = "SELECT empresa_bodega_id FROM empresa_bodega WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY empresa_bodega_nombre ASC";
		$result_bodegas = mysql_query( $query_bodegas, $GLOBALS["conexion_slave"] ) or die( mysql_error() );
		while( $row_bodegas = mysql_fetch_array($result_bodegas) ){
		
			$query_saldo = "SELECT pi_saldo FROM producto_inventario WHERE rel_producto_id='".$id_producto."' AND rel_bodega_id='".$row_bodegas['empresa_bodega_id']."' ORDER BY pi_id DESC LIMIT 1";
			$result_saldo = mysql_query($query_saldo, $GLOBALS["conexion_slave"]) or die ( mysql_error() );
			$saldo = mysql_fetch_array($result_saldo);
			
			if($saldo['pi_saldo']>0){
				$stock = $stock + $saldo['pi_saldo'];
			}
		
		} mysql_free_result($result_bodegas);


	} else {

		$query_saldo = "SELECT pi_saldo FROM producto_inventario WHERE rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' ORDER BY pi_id DESC LIMIT 1";
		$result_saldo = mysql_query($query_saldo, $GLOBALS["conexion_slave"]) or die ( mysql_error() );
		$saldo = mysql_fetch_array($result_saldo);
		
		if( !empty($saldo['pi_saldo']) ){
			$stock = $saldo['pi_saldo'];
		}

	}

		
	return $stock;
}


/**
 * Nueva funcion de stock
*/
function Get_ProductoStock2 ( $id_producto, $id_bodega, $lote = null ) {

	//echo '0000000';
	
	$stock = 0;
	
	if( $id_bodega == 0 ){


		$query_bodegas = "SELECT empresa_bodega_id FROM empresa_bodega WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY empresa_bodega_nombre ASC";
		$result_bodegas = mysql_query( $query_bodegas, $GLOBALS["conexion_slave"] ) or die( mysql_error() );
		while( $row_bodegas = mysql_fetch_array($result_bodegas) ){

			$id_bodega = $row_bodegas['empresa_bodega_id'];

			$stock_b = 0;
		
			$query_saldo = "SELECT ps_saldo FROM producto_stock WHERE ps_lote='".$lote."' AND rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";

			if( is_null($lote) or empty($lote) ){
				$query_saldo = "SELECT SUM(ps_saldo) as ps_saldo FROM producto_stock WHERE rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";
			}

			$result_saldo = mysql_query($query_saldo, $GLOBALS["conexion_slave"]) or die ( mysql_error() );
			$saldo = mysql_fetch_array($result_saldo);
			
			if( !empty($saldo['ps_saldo']) ){
				$stock_b = $saldo['ps_saldo'];
			}


			$stock = ( $stock + $stock_b );
		
		} mysql_free_result($result_bodegas);


	} else {


		$query_saldo = "SELECT ps_saldo FROM producto_stock WHERE ps_lote='".$lote."' AND rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";

		if( is_null($lote) or empty($lote) ){
			$query_saldo = "SELECT SUM(ps_saldo) as ps_saldo FROM producto_stock WHERE rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";
		}

		$result_saldo = mysql_query($query_saldo, $GLOBALS["conexion_slave"]) or die ( mysql_error() );
		$saldo = mysql_fetch_array($result_saldo);
		
		if( !empty($saldo['ps_saldo']) ){
			$stock = $saldo['ps_saldo'];
		}


	}

		
	return $stock;
}



/**
 * Nueva funcion de stock
*/
function Get_ProductoStockReal2 ( $id_producto, $id_bodega, $lote = null ) {

	//echo '0000000';
	
	$stock = 0;
	
	if( $id_bodega == 0 ){


		$query_bodegas = "SELECT empresa_bodega_id FROM empresa_bodega WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY empresa_bodega_nombre ASC";
		$result_bodegas = mysql_query( $query_bodegas, $GLOBALS["conexion_slave"] ) or die( mysql_error() );
		while( $row_bodegas = mysql_fetch_array($result_bodegas) ){

			$id_bodega = $row_bodegas['empresa_bodega_id'];

			$stock_b = 0;
		
			$query_saldo = "SELECT ps_saldo FROM producto_stock WHERE  ps_saldo>0 AND ps_lote='".$lote."' AND rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";

			if( is_null($lote) or empty($lote) ){
				$query_saldo = "SELECT SUM(ps_saldo) as ps_saldo FROM producto_stock WHERE ps_saldo>0 AND rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";
			}

			$result_saldo = mysql_query($query_saldo, $GLOBALS["conexion_slave"]) or die ( mysql_error() );
			$saldo = mysql_fetch_array($result_saldo);
			
			if( !empty($saldo['ps_saldo']) ){
				$stock_b = $saldo['ps_saldo'];
			}


			$stock = ( $stock + $stock_b );
		
		} mysql_free_result($result_bodegas);


	} else {


		$query_saldo = "SELECT ps_saldo FROM producto_stock WHERE ps_saldo>0 AND ps_lote='".$lote."' AND rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";

		if( is_null($lote) or empty($lote) ){
			$query_saldo = "SELECT SUM(ps_saldo) as ps_saldo FROM producto_stock WHERE ps_saldo>0 AND rel_producto_id='".$id_producto."' AND rel_bodega_id='".$id_bodega."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";
		}

		$result_saldo = mysql_query($query_saldo, $GLOBALS["conexion_slave"]) or die ( mysql_error() );
		$saldo = mysql_fetch_array($result_saldo);
		
		if( !empty($saldo['ps_saldo']) ){
			$stock = $saldo['ps_saldo'];
		}


	}

		
	return $stock;
}




/*

Devuelve el stock por arribar de las ordenes de compra emitidas
OC confirmadas, concepto inventario

*/
function Get_ProductoStockPorArribar ( $id_producto, $mostrar_como_array = null ) {

	// Productos por arribar

	$stock_por_arribar_array = array();
	$stock_por_arribar = 0;

	if( $id_producto>0 ){

		$consulta_compra_oc = "SELECT cantidad, cantidad_despachada, compra_oc_folio, compra_oc_fecha_arribo_productos FROM compra_oc  
		                            
		                           INNER JOIN compra_oc_detalle ON compra_oc.compra_oc_id=compra_oc_detalle.rel_compra_oc_id
		                           WHERE 
		                           compra_oc.compra_oc_confirmada=1 AND 
		                           compra_oc.compra_oc_concepto='inventario' AND 
		                           compra_oc.compra_oc_estado!='ANULADA' AND 
		                           compra_oc_detalle.producto_id='".$id_producto."' AND 
		                           compra_oc_detalle.cantidad>compra_oc_detalle.cantidad_despachada AND 
		                           compra_oc.rel_empresa_id='".$_SESSION['id_empresa']."' 
		                           ORDER BY compra_oc_id DESC
		                          ";
	 
	                                 
		    
		$result_compra_oc = mysql_query( $consulta_compra_oc, $GLOBALS["conexion_slave"] ) or die( mysql_error() );

		while ($row_oc = mysql_fetch_array($result_compra_oc))
		{


			$cantidad = ( $row_oc['cantidad'] - $row_oc['cantidad_despachada'] );

			$stock_por_arribar = ( $stock_por_arribar + $cantidad );

			//echo '<br>'.$row_oc['compra_oc_folio'].' '.$stock_por_arribar;

			$stock_por_arribar_array[] = array(
								'oc_folio' => $row_oc["compra_oc_folio"],
								'oc_fecha_arribo' => $row_oc["compra_oc_fecha_arribo_productos"],
								'id_producto' => $id_producto,
								'cantidad' => $cantidad,
							); 
			


		} mysql_free_result( $result_compra_oc );

	}


	if( $mostrar_como_array==1 ){
		
		$stock_por_arribar_array_ = array(
											'total' => $stock_por_arribar,
											'detalle' => $stock_por_arribar_array
										);

		return $stock_por_arribar_array_;

	} else {

		return $stock_por_arribar;

	}

}




/*

Devuelve el stock reservado
Notas de Venta

*/
function Get_ProductoStockReservado ( $id_producto, $mostrar_como_array = null ) {

	// Productos reservados

	$stock_reservado_array = array();
	$stock_reservado = 0;

	if( $id_producto>0 ){

		$query_ventas = "SELECT cantidad, venta_nro_dcto, venta_fecha_vencimiento, rel_vendedor_id FROM venta 
  					INNER JOIN venta_detalle ON venta.venta_id=venta_detalle.rel_venta_id 
  					WHERE 
  					venta_detalle.producto_id='".$id_producto."' AND 
  					venta.venta_tipo_dcto='4' AND venta.venta_estado='RESERVADO' AND venta.rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY venta.venta_fecha_vencimiento ASC";
	  	$result_ventas = mysql_query($query_ventas, $GLOBALS["conexion_slave"]) or die( mysql_error() );
	  	while ( $row_ventas = mysql_fetch_array($result_ventas) ) {
	  		
	  		$cantidad = $row_ventas['cantidad'];

	  		$stock_reservado = ( $stock_reservado + $cantidad );


			$stock_reservado_array[] = array(
								'folio' => $row_ventas["venta_nro_dcto"],
								'fecha_vcto' => $row_ventas["venta_fecha_vencimiento"],
								'id_producto' => $id_producto,
								'cantidad' => $cantidad,
							); 


	  	} mysql_free_result( $result_ventas );


	}


	if( $mostrar_como_array==1 ){
		
		$stock_reservado_array_ = array(
											'total' => $stock_reservado,
											'detalle' => $stock_reservado_array
										);

		return $stock_reservado_array_;

	} else {

		return $stock_reservado;

	}

}



function Get_ProductoCalculoCosto ( $id_producto ) {

	$dato = 0;

	if( $id_producto>0 ){
		
		$sql = "SELECT producto_costo_clp_neto_promedio FROM producto WHERE producto_id='".$id_producto."'";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
			
			$dato = $row['producto_costo_clp_neto_promedio'];
	
	}

	return $dato;
}











function Get_ProductoEstado ( $id ) {

	$estado = '';
	
	if ( $id==0 ) {
		$estado = 'Inactivo';
	} else if ( $id==1 ) {
		$estado = 'Activo';
	}

	return $estado;
}




function Get_ProductoTipo ( $id ) {

	$tipo = '';
	
	if ( $id==0 ) {
		$tipo = 'Estandar';
	} else if ( $id==1 ) {
		$tipo = 'Servicio';
	} else if ( $id==2 ) {
		$tipo = 'Kit-Combo';
	} else if ( $id==3 ) {
		$tipo = 'Fabricado';
	} else if ( $id==4 ) {
		$tipo = 'Virtual';
	}

	return $tipo;
}



function Get_ProductoTipoLista () {
	
	$array = array(
					0=>'Estandar', 
					1=>'Servicio', 
					2=>'Kit-Combo', 
					3=>'Fabricado', 
					4=>'Virtual'
				  );
	
	return $array;
}



//********************************************************************
// Fabricante

function Get_FabricanteNombre ( $id_fabricante ) {

	$dato = 0;
		
		$sql = "SELECT producto_fabricante_nombre FROM producto_fabricante WHERE producto_fabricante_id='".$id_fabricante."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);
		
		$key_sql = $_SESSION['id_empresa'].'-fabricante-'.$id_fabricante.'-nombre';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];
			
			$dato = $row['producto_fabricante_nombre'];
		
	return $dato;
}



function Get_FabricanteCodigo ( $id_fabricante ) {

	$dato = 0;
		
		$sql = "SELECT producto_fabricante_codigo FROM producto_fabricante WHERE producto_fabricante_id='".$id_fabricante."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);
		
		$key_sql = $_SESSION['id_empresa'].'-fabricante-'.$id_fabricante.'-codigo';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];
			
		$dato = $row['producto_fabricante_codigo'];
		
	return $dato;
}



function Get_FabricanteId_ByCodigo ( $codigo ) {

	$dato = 0;

	if( !empty($codigo) ){
		
		$sql = "SELECT producto_fabricante_id FROM producto_fabricante WHERE producto_fabricante_codigo='".$codigo."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);
		
		$key_sql = $_SESSION['id_empresa'].'-fabricantes-bycodigo-'.$codigo;
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];
			
			$dato = $row['producto_fabricante_id'];

	}
		
	return $dato;
}


function Get_Fabricantes_array_completo(){
	
	$query = "SELECT producto_fabricante_id,producto_fabricante_codigo,producto_fabricante_nombre,producto_fabricante_url FROM producto_fabricante WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY producto_fabricante_nombre ASC ";
	   		
	$key_sql = $_SESSION['id_empresa'].'-fabricantes-completo';
	
	$result = mysql_query_cache($query, 'slave', null, null, null, $key_sql);
	
	return $result;
}


function Get_Fabricantes_array(){
	
	$data = array();
	
		$query = "SELECT producto_fabricante_id, producto_fabricante_nombre FROM producto_fabricante WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY producto_fabricante_id ASC ";
	   	//$result_ = mysql_query($query_, $GLOBALS["conexion_slave"]) or die( mysql_error() );
	   	//while ( $row_ = mysql_fetch_array( $result_ ) ){
	   		
		$key_sql = $_SESSION['id_empresa'].'-fabricantes';
		$result = mysql_query_cache($query, 'slave', null, null, null, $key_sql);
		foreach ( $result as $row ){
	
			$data[$row['producto_fabricante_id']] = $row['producto_fabricante_nombre'];
			
		}
		//} mysql_free_result( $result_ );

	
	return $data;
}



function Get_Fabricantes_array_codigos(){
	
	$data = array();
	
		$query = "SELECT producto_fabricante_id, producto_fabricante_codigo FROM producto_fabricante WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY producto_fabricante_id ASC ";
						
	   	//$result_ = mysql_query($query_, $GLOBALS["conexion_slave"]) or die( mysql_error() );
	   	//while ( $row_ = mysql_fetch_array( $result_ ) ){
	   		
		$key_sql = $_SESSION['id_empresa'].'-fabricantes-codigos';
		$result = mysql_query_cache($query, 'slave', null, null, null, $key_sql);
		foreach ( $result as $row ){
	   		
			$data[$row['producto_fabricante_id']] = $row['producto_fabricante_codigo'];
			
		}
		//} mysql_free_result( $result_ );
	
	return $data;
}



function Get_Fabricantes_arrayNombre($array, $id){
	
	
	
	return $array[$id];
}




//********************************************************************
// Categorias

function Get_ProductoCategoriaNombre ( $id_categoria ) {

	$dato = 0;
		
		$sql = "SELECT producto_categoria_nombre FROM producto_categoria WHERE producto_categoria_id='".$id_categoria."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);
		
		$key_sql = $_SESSION['id_empresa'].'-categoria-'.$id_categoria.'-nombre';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];
			
			$dato = $row['producto_categoria_nombre'];
		
	return $dato;
}


function Get_ProductoCategoriaCodigo ( $id_categoria ) {

	$dato = 0;
		
		$sql = "SELECT producto_categoria_codigo FROM producto_categoria WHERE producto_categoria_id='".$id_categoria."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);
		
		$key_sql = $_SESSION['id_empresa'].'-categoria-'.$id_categoria.'-codigo';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];
			
			$dato = $row['producto_categoria_codigo'];
		
	return $dato;
}


function Get_ProductoCategoriaId_ByCodigo ( $codigo ) {

	$dato = 0;
		
		$sql = "SELECT producto_categoria_id FROM producto_categoria WHERE producto_categoria_codigo='".$codigo."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		$row = mysql_fetch_array($result);
		
		$key_sql = $_SESSION['id_empresa'].'-categorias-bycodigo-'.$codigo;
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];
			
			$dato = $row['producto_categoria_id'];
		
	return $dato;
}


function Get_ProductoCategoria_array ( ) {

	$data = array();
	
		$sql = "SELECT producto_categoria_id, producto_categoria_nombre FROM producto_categoria WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY producto_categoria_id ASC ";
						
	   	//$result_ = mysql_query($query_, $GLOBALS["conexion_slave"]) or die( mysql_error() );
	    //while ( $row_ = mysql_fetch_array( $result_ ) ){
		
			
		$key_sql = $_SESSION['id_empresa'].'-categorias';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		foreach ( $result as $row ){
	   		
			$data[$row['producto_categoria_id']] = $row['producto_categoria_nombre'];
			
		}
		//} mysql_free_result( $result_ );
	
	return $data;
}



function Get_ProductoCategoria_array_codigos ( ) {

	$data = array();
	
		$sql = "SELECT producto_categoria_id, producto_categoria_codigo FROM producto_categoria WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY producto_categoria_id ASC ";
						
	   	//$result_ = mysql_query($query_, $GLOBALS["conexion_slave"]) or die( mysql_error() );
	   	//while ( $row_ = mysql_fetch_array( $result_ ) ){
		
			
		$key_sql = $_SESSION['id_empresa'].'-categorias-codigos';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		foreach ( $result as $row ){
	   		
			$data[$row['producto_categoria_id']] = $row['producto_categoria_codigo'];
			
		}
		//} mysql_free_result( $result_ );
	
	return $data;
}

function Get_ProductoCategoria_array_completo ( ) {

	$data = array();
	
		$sql = "SELECT * FROM producto_categoria WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY producto_categoria_nombre ASC ";
						
	   	//$result_ = mysql_query($query_, $GLOBALS["conexion_slave"]) or die( mysql_error() );
	   	//while ( $row_ = mysql_fetch_array( $result_ ) ){
		
			
		$key_sql = $_SESSION['id_empresa'].'-categorias-completo';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		
		//} mysql_free_result( $result_ );
	
	return $result;
}


//Devuelve un listado completo de categorias y una columna adicional con la cantidad de  subcategorias
function Get_ProductoCategoria_array_completo2() {

	$sql = "SELECT producto_categoria_id,
					   producto_categoria_posicion,
					   producto_categoria_codigo,
					   producto_categoria_nombre,
					   producto_categoria_imagen,
					   producto_categoria_mostrar,
					   COUNT(producto_subcategoria.producto_subcategoria_id) as cantidad_subcategorias 
					   FROM producto_categoria 
					   LEFT JOIN producto_subcategoria 
					   ON producto_categoria.producto_categoria_id=producto_subcategoria.rel_producto_categoria_id 
					   WHERE producto_categoria.rel_empresa_id=".$_SESSION['id_empresa']." 
					   GROUP BY producto_categoria.producto_categoria_id ORDER BY producto_categoria_posicion ASC,producto_categoria_nombre ASC";
		
			
	$key_sql = $_SESSION['id_empresa'].'-categorias-completo2';

	$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
	
	return $result;
}

//Devuelve la cantidad de productos por categoria

function Get_ProductoCount($id_categoria) {

	$sql = "SELECT COUNT(producto_id) AS count  FROM producto WHERE producto_categoria={$id_categoria} AND rel_empresa_id={$_SESSION['id_empresa']}";
		
			
	$key_sql = $_SESSION["id_empresa"]."-cantidad-productos-por-categoria-".$id_categoria;

	$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
	
	return $result;
}



//********************************************************************
// Sub-Categorias

function Get_ProductoSubCategoriaNombre ( $id_subcategoria ) {

	$dato = 0;
		
		$sql = "SELECT producto_subcategoria_nombre FROM producto_subcategoria WHERE producto_subcategoria_id='".$id_subcategoria."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);
		
		$key_sql = $_SESSION['id_empresa'].'-subcategoria-'.$id_subcategoria.'-nombre';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];
			
		$dato = $row['producto_subcategoria_nombre'];
		
	return $dato;
}


function Get_ProductoSubCategoriaCodigo ( $id_subcategoria ) {

	$dato = 0;
		
		$sql = "SELECT producto_subcategoria_codigo FROM producto_subcategoria WHERE producto_subcategoria_id='".$id_subcategoria."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);
		
		$key_sql = $_SESSION['id_empresa'].'-subcategoria-'.$id_subcategoria.'-codigo';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];
			
			$dato = $row['producto_subcategoria_codigo'];
		
	return $dato;
}


function Get_ProductoSubCategoriaId_ByCodigo ( $codigo, $codigo_categoria ) {

	$dato = 0;
		
		//$sql_c = "SELECT producto_categoria_id FROM producto_categoria WHERE producto_categoria_codigo='".$codigo_categoria."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		//$result_c = mysql_query($sql_c, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row_c = mysql_fetch_array($result_c);

		$id_categoria = Get_ProductoCategoriaId_ByCodigo ( $codigo_categoria ); 
		
		$sql = "SELECT producto_subcategoria_id FROM producto_subcategoria WHERE rel_producto_categoria_id='".$id_categoria."' AND producto_subcategoria_codigo='".$codigo."' AND rel_empresa_id='".$_SESSION['id_empresa']."'";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);
				
		$key_sql = $_SESSION['id_empresa'].'-subcategorias-bycodigo-'.$codigo;
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];

			$dato = $row['producto_subcategoria_id'];
		
	return $dato;
}


function Get_ProductoSubCategoria_array () {

	$data = array();
	
		$sql = "SELECT producto_subcategoria_id, producto_subcategoria_nombre FROM producto_subcategoria WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY producto_subcategoria_id ASC ";
						
	   	//$result_ = mysql_query($query_, $GLOBALS["conexion_slave"]) or die( mysql_error() );
	    //while ( $row_ = mysql_fetch_array( $result_ ) ){		
		
		$key_sql = $_SESSION['id_empresa'].'-subcategorias';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		foreach ( $result as $row ){
				
			$data[$row['producto_subcategoria_id']] = $row['producto_subcategoria_nombre'];
			
		}
		//} mysql_free_result( $result_ );
	
	return $data;
}

function Get_ProductoSubCategoria_array_completo ($id_categoria) {
	
	$sql = "SELECT * FROM producto_subcategoria WHERE rel_empresa_id='".$_SESSION['id_empresa']."'AND rel_producto_categoria_id='".$id_categoria."' ORDER BY producto_subcategoria_posicion ASC,producto_subcategoria_nombre ASC";
			
	$key_sql = $_SESSION['id_empresa'].'-subcategorias-completo-'.$id_categoria;

	$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
	
	return $result;
}

function Get_ProductoSubCategoria_array_codigos ( ) {

	$data = array();
	
		$sql = "SELECT producto_subcategoria_id, producto_subcategoria_codigo FROM producto_subcategoria WHERE rel_empresa_id='".$_SESSION['id_empresa']."' ORDER BY producto_subcategoria_id ASC ";
						
	   	//$result_ = mysql_query($query_, $GLOBALS["conexion_slave"]) or die( mysql_error() );
	   	//while ( $row_ = mysql_fetch_array( $result_ ) ){		
		
		$key_sql = $_SESSION['id_empresa'].'-subcategorias-codigos';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		foreach ( $result as $row ){
	   		
			$data[$row['producto_subcategoria_id']] = $row['producto_subcategoria_codigo'];
			
		}
		//} mysql_free_result( $result_ );
	
	return $data;
}


//********************************************************************
// 

function Get_InventarioConceptoMovimiento ( $id ) {

	$dato = 0;
		
		$sql = "SELECT pic_nombre FROM producto_inventario_conceptos WHERE pic_id='".$id."'";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);	
		
		$key_sql = 'InventarioConceptoMovimiento-'.$id;
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		$row = $result[0];
			
			$dato = $row['pic_nombre'];
		
	return $dato;
}

function Get_InventarioConceptoMovimiento_array ( ) {

	$dato = 0;
		
		$sql = "SELECT * FROM producto_inventario_conceptos ";
		//$result = mysql_query($sql, $GLOBALS["conexion_slave"]) or die( mysql_error() );
		//$row = mysql_fetch_array($result);	
		
		$key_sql = 'InventarioConceptoMovimientos';
		$result = mysql_query_cache($sql, 'slave', null, null, null, $key_sql);
		
		
	return $result;
}


//********************************************************************
// calcular costos

function Get_ProductoCostos ( $id, $tipo = '' ) { 
                    
	$producto_costos = array();
	$result = array();

	$cont = 0;
	
	if( $tipo == '' ){	
		
		$sql_compra = "SELECT * FROM compra, compra_detalle 
						WHERE compra.compra_id=compra_detalle.rel_compra_id 
						AND compra_detalle.producto_id='$id' 
						AND compra.rel_empresa_id='".$_SESSION['id_empresa']."' 
						AND compra_tipo_dcto IN(30, 32, 33, 34, 55, 56, 60, 61, 914) 
						ORDER BY compra_id DESC";
		
	} else if( $tipo == 'ano' ){
		
		$sql_compra = "SELECT * FROM compra, compra_detalle WHERE 
					   compra.compra_id=compra_detalle.rel_compra_id 
					   AND compra_detalle.producto_id='$id' 
					   AND compra.rel_empresa_id='".$_SESSION['id_empresa']."' 
					   AND compra_ano_contable='".date('Y')."' 
					   AND compra_tipo_dcto IN(30, 32, 33, 34, 55, 56, 60, 61, 914) 
					   ORDER BY compra_id DESC";
		
	} else if( $tipo == 'mes' ){
	
		$sql_compra = "SELECT * FROM compra, compra_detalle WHERE 
					  compra.compra_id=compra_detalle.rel_compra_id 
					  AND compra_detalle.producto_id='$id' 
					  AND compra.rel_empresa_id='".$_SESSION['id_empresa']."' 
					  AND compra_ano_contable='".date('Y')."' AND compra_mes_contable='".date('m')."' 
					  AND compra_tipo_dcto IN(30, 32, 33, 34, 55, 56, 60, 61, 914) 
					  ORDER BY compra_id DESC";
	
	}
	// limitar a x ultimos meses
	// buscar fecha ultima compra
	// $row_ult_c = Get_ProductoFechaUltimaCompra ( $id )
	//$row_ult_c['fecha_ultima_compra'];

	// buscar los dctos
	//SELECT * FROM `compra` WHERE rel_empresa_id=1 AND CONCAT(compra_ano_contable,'-',compra_mes_contable,'-01') > DATE_SUB(CURDATE(),INTERVAL 12 MONTH)


	
	
	$result_compra = mysql_query($sql_compra, $GLOBALS["conexion_slave"]) or die(mysql_error());
	while ($row_compra = mysql_fetch_array($result_compra))
	{
		
		if($row_compra['compra_tipo_dcto']==60 or $row_compra['compra_tipo_dcto']==61){
			$tag = '-';
		} else {
			$tag = '';
		}
		
		$precio = $row_compra["precio"]; 
		
		$descuento_global = $row_compra["compra_descuento_porciento"];
		
		$costo = $row_compra["precio"] - ( ( $row_compra["precio"] * $row_compra["descuento"] ) / 100 );

		// guarda el costo final
		
		if($_SESSION['id_empresa']==2){
		if( $row_compra['compra_tipo_dcto']==914 and $row_compra['costo_final_importacion']>0 ){
			$costo = $row_compra['costo_final_importacion'];
		}
		}
		
		
		if ( $descuento_global>0 ) {
			$costo = ( $costo - ( ( $costo * $descuento_global ) / 100 ) );
			$subtotal = ( $costo * $row_compra["cantidad"] );
		}
		
		$subtotal = ( $costo * $row_compra["cantidad"] );
		
		$producto_costos[] = array( 
								'tipo_dcto'=> $row_compra['compra_tipo_dcto'],
								'folio_dcto'=> $row_compra['compra_folio'],
								'costo'=> $tag.$costo,
								'cantidad'=> $tag.$row_compra["cantidad"],
								'subtotal'=> $tag.$subtotal
							 );
							 
		
		$cont++;
	}
	mysql_free_result($result_compra);





	foreach ($producto_costos as $k => $v) {
	  
	  if($v['costo']>0 ){
	  	$tArray[$k] = $v['costo'];
	  }
	  	  
	}
	
	
	// suma cantidades
	foreach ($producto_costos as $k => $v) {
	  $tArray2[$k] = $v['cantidad'];
	}
	
	// suma subtotales
	foreach ($producto_costos as $k => $v) {
	  $tArray3[$k] = $v['subtotal'];
	}
	
	
	$result['cantidad'] = array_sum($tArray2);
	$result['subtotal'] = array_sum($tArray3);
	
	
	
	$min_value = min($tArray);
	$max_value = max($tArray);
	$avg_value = ($result['subtotal'] / $result['cantidad']);
	
	
	$result['min'] = $min_value;
	$result['max'] = $max_value;
	$result['avg'] = $avg_value;

	$result['cantidad_dctos'] = $cont;

	$result['detalle'] = $producto_costos;
	
	

	
	
	
	
	
	return $result;

}


function Get_ProductoCostoPrimeraCompra ( $id ) { 
                    
	
	
	$sql_compra = "SELECT * FROM compra, compra_detalle WHERE 
					  compra.compra_id=compra_detalle.rel_compra_id 
					  AND compra_detalle.producto_id='$id' 
					  AND compra.rel_empresa_id='".$_SESSION['id_empresa']."' 
					  AND compra_tipo_dcto IN(30, 32, 33, 34, 914) 
					  ORDER BY compra_ano ASC, compra_mes ASC, compra_dia ASC LIMIT 1";
	
		
	
	$result_compra = mysql_query($sql_compra, $GLOBALS["conexion_slave"]) or die(mysql_error());
	$row_compra = mysql_fetch_array($result_compra);
	
		
		$descuento_global = $row_compra["compra_descuento_porciento"];
		
		$costo = $row_compra["precio"] - ( ( $row_compra["precio"] * $row_compra["descuento"] ) / 100 );

		
		// guarda el costo final
		
		if($_SESSION['id_empresa']==2){
		if( $row_compra['compra_tipo_dcto']==914 and $row_compra['costo_final_importacion']>0 ){
			$costo = $row_compra['costo_final_importacion'];
		}
		}
		
		
		if ( $descuento_global>0 ) {
			$costo = $costo - ( ( $costo * $descuento_global ) / 100 );
		}
		
			
	return $costo;

}



function Get_ProductoCostoUltimaCompra ( $id ) { 
                    
	
	
	$sql_compra = "SELECT * FROM compra, compra_detalle WHERE 
					  compra.compra_id=compra_detalle.rel_compra_id 
					  AND compra_detalle.producto_id='$id' 
					  AND compra.rel_empresa_id='".$_SESSION['id_empresa']."' 
					  AND compra.compra_tipo_dcto IN(30, 32, 33, 34, 914) 
					  ORDER BY compra_ano DESC, compra_mes DESC, compra_dia DESC LIMIT 1";
	
		
	
	$result_compra = mysql_query($sql_compra, $GLOBALS["conexion_slave"]) or die(mysql_error());
	$row_compra = mysql_fetch_array($result_compra);
	
		
		$descuento_global = $row_compra["compra_descuento_porciento"];
		
		$costo = $row_compra["precio"] - ( ( $row_compra["precio"] * $row_compra["descuento"] ) / 100 );

		// guarda el costo final
		
		if($_SESSION['id_empresa']==2){
		if( $row_compra['compra_tipo_dcto']==914 and $row_compra['costo_final_importacion']>0 ){
			$costo = $row_compra['costo_final_importacion'];
		}
		}
		
		
		if ( $descuento_global>0 ) {
			$costo = $costo - ( ( $costo * $descuento_global ) / 100 );
		}
		
			
	return $costo;

}



function Get_ProductoFechaPrimeraCompra ( $id ) { 
                    
	
	$dato = array();
	
	$sql_compra = "SELECT compra_dia, compra_mes, compra_ano, compra_descuento_porciento, cantidad, precio, descuento FROM compra, compra_detalle WHERE 
					  compra.compra_id=compra_detalle.rel_compra_id 
					  AND compra_detalle.producto_id='$id' 
					  AND compra.rel_empresa_id='".$_SESSION['id_empresa']."' 
					  AND compra_tipo_dcto IN(30, 32, 33, 34, 914) 
					  ORDER BY compra_ano DESC, compra_mes DESC, compra_dia ASC LIMIT 1";
	
		
	
	$result_compra = mysql_query($sql_compra, $GLOBALS["conexion_slave"]) or die(mysql_error());
	$row_compra = mysql_fetch_array($result_compra);
	
		
		$dato['fecha_ultima_compra'] = $row_compra['compra_ano'].'-'.$row_compra['compra_mes'].'-'.$row_compra['compra_dia'];

		$dato['dias_desde_ultima_compra'] = restaFechas( Set_Fecha_Lat($dato['fecha_ultima_compra']) , date('d-m-Y') );

		$dato['cantidad_ultima_compra'] = $row_compra['cantidad'];


		// 
		$descuento_global = $row_compra["compra_descuento_porciento"];
		
		$costo = $row_compra["precio"] - ( ( $row_compra["precio"] * $row_compra["descuento"] ) / 100 );

		// guarda el costo final
		
		if($_SESSION['id_empresa']==2){
		if( $row_compra['compra_tipo_dcto']==914 and $row_compra['costo_final_importacion']>0 ){
			$costo = $row_compra['costo_final_importacion'];
		}
		}
		
		
		if ( $descuento_global>0 ) {
			$costo = $costo - ( ( $costo * $descuento_global ) / 100 );
		}

		$dato['costo'] = $costo;
		
			
	return $dato;

}


function Get_ProductoFechaUltimaCompra ( $id ) { 
                    
	
	$dato = array();
	
	$sql_compra = "SELECT compra_dia, compra_mes, compra_ano, compra_descuento_porciento, cantidad, precio, descuento FROM compra, compra_detalle WHERE 
					  compra.compra_id=compra_detalle.rel_compra_id 
					  AND compra_detalle.producto_id='$id' 
					  AND compra.rel_empresa_id='".$_SESSION['id_empresa']."' 
					  AND compra_tipo_dcto IN(30, 32, 33, 34, 914) 
					  ORDER BY compra_ano DESC, compra_mes DESC, compra_dia DESC LIMIT 1";
	
		
	
	$result_compra = mysql_query($sql_compra, $GLOBALS["conexion_slave"]) or die(mysql_error());
	$row_compra = mysql_fetch_array($result_compra);
	
		
		$dato['fecha_ultima_compra'] = $row_compra['compra_ano'].'-'.$row_compra['compra_mes'].'-'.$row_compra['compra_dia'];

		$dato['dias_desde_ultima_compra'] = restaFechas( Set_Fecha_Lat($dato['fecha_ultima_compra']) , date('d-m-Y') );

		$dato['cantidad_ultima_compra'] = $row_compra['cantidad'];


		// 
		$descuento_global = $row_compra["compra_descuento_porciento"];
		
		$costo = $row_compra["precio"] - ( ( $row_compra["precio"] * $row_compra["descuento"] ) / 100 );

		// guarda el costo final
		
		if($_SESSION['id_empresa']==2){
		if( $row_compra['compra_tipo_dcto']==914 and $row_compra['costo_final_importacion']>0 ){
			$costo = $row_compra['costo_final_importacion'];
		}
		}
		
		
		if ( $descuento_global>0 ) {
			$costo = $costo - ( ( $costo * $descuento_global ) / 100 );
		}

		$dato['costo'] = $costo;
		
			
	return $dato;

}




function Get_ProductoVentasPeriodo ($mes, $ano, $id_producto){


	$cantidad = 0;
	$subtotal = 0;


	$query = "SELECT venta_tipo_dcto, cantidad, subtotal, venta_detalle.producto_id 
	              FROM venta 
	              INNER JOIN venta_detalle ON venta.venta_id=venta_detalle.rel_venta_id 
	             ";	

	$conditions = array();

	
	$conditions[] = "producto.producto_id='".$id_producto."' AND venta.venta_mes='".$mes."' AND venta.venta_ano='".$ano."' AND venta.venta_tipo_dcto!='4' AND venta.venta_tipo_dcto!='50' AND venta.venta_tipo_dcto!='52' AND venta.rel_empresa_id='".$_SESSION['id_empresa']."' ";

    if (count($conditions) > 0) {
      $query .= " WHERE " . implode(' AND ', $conditions);
    }

	$result = mysql_query($query, $GLOBALS["conexion_slave"]) or die( mysql_error() );

	while( $row = mysql_fetch_array($result) ){
                 
	    


	    // aplica el descuento global al item
	    if ( $row['venta_descuento_porciento']>0 ) {

	      $row['subtotal'] = ( $row['subtotal'] - ( ( $row['subtotal'] * $row['venta_descuento_porciento'] ) / 100 ) );

	    }


	    if (in_array($row['venta_tipo_dcto'], $dctos_suman)) {
	      $tag = '';

	      $cantidad = ( $cantidad + $row['cantidad'] );
	      $subtotal = ( $subtotal + $row['subtotal'] );

	    }else{
	      $tag = '-';

	      $cantidad = ( $cantidad - $row['cantidad'] );
	      $subtotal = ( $subtotal - $row['subtotal'] );

	    }
    


   } mysql_free_result($result);



   return array( 'cantidad'=>$cantidad, 'subtotal'=>$subtotal );


}



//**********************************************************************************
// *** calculo comisiones

function Get_ProductoComision( $id_producto, $id_empleado, $subto, $cantidad, $costo_subtotal = 0 ){

	
	$result = array();
	
	$query_Producto = "SELECT producto_id, producto_comision_sistema, producto_comision_tipo, producto_comision_factor, producto_costo_clp_neto_estandar 
						FROM producto WHERE producto_id='".$id_producto."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";
	$result_Producto = mysql_query($query_Producto, $GLOBALS["conexion_slave"]) or die(mysql_error());
	$row_Producto = mysql_fetch_array($result_Producto);
	
	
	
	

	//******************************************************************************
	// - INICIA Comision vendedor
	$comision_base = 0;
	$vendedor = Get_Empleado ( $id_empleado );
	$comision_sistema = $row_Producto['producto_comision_sistema'];
	
	
	
	
	if( $comision_sistema != 0 ){ 
	
		if( $comision_sistema == 1 ){
		
		// Comision definida en ficha vendedor
			$comision_tipo = $vendedor['empleado_vendedor_comision_tipo'];
			$comision_factor = $vendedor['empleado_vendedor_comision_factor'];
			
			if( $comision_tipo == 1 ){
				// porcentaje de la venta
				$comision_base = ( ( $subto * $comision_factor ) / 100 );
			} else if( $comision_tipo == 2 ){
				// porcentaje de la ganancia
				$utilidad = ( $subto - $costo_subtotal);
				$comision_base = ( ( $utilidad * $comision_factor ) / 100 );
			}
			
		} else if( $comision_sistema == 2 ){
		
		// Comision segun esquema propio del producto
			$comision_tipo = $row_Producto['producto_comision_tipo'];
			$comision_factor = $row_Producto['producto_comision_factor'];
			
			if( $comision_tipo == 1 ){
				// porcentaje de la venta
				$comision_base = ( ( $subto * $comision_factor ) / 100 );
			} else if( $comision_tipo == 2 ){
				// porcentaje de la ganancia
				$utilidad = ( $subto - $costo_subtotal);
				$comision_base = ( ( $utilidad * $comision_factor ) / 100 );
			} else if( $comision_tipo == 3 ){
				// Monto fijo por producto
				$comision_base = ( $comision_factor * $cantidad );
			}
		
		}
	
	}
		
	// - FIN Comision vendedor
	//******************************************************************************

	$result['comision_sistema'] = $comision_sistema;
	$result['comision_tipo'] = $comision_tipo;
	$result['comision_factor'] = $comision_factor;
	$result['comision_base'] = $comision_base;
	
	return $result;
	
}

/**
 * Permite calcular las comisiones de una venta por el id 
 * 
 * 
 */
function Get_ProductoVentaComision( $id_venta ){

	//
	$query_venta = "SELECT * FROM venta WHERE venta_id='".$id_venta."' AND rel_empresa_id='".$_SESSION['id_empresa']."' ";
	$result_venta = mysql_query($query_venta, $GLOBALS["conexion_slave"]) or die(mysql_error());
	while ( $venta = mysql_fetch_array($result_venta) ){

		$id = $venta['venta_id'];

		$empleado = Get_Empleado($venta['rel_vendedor_id']);

		$descuento_global = $venta['venta_descuento_porciento'];
	 
	 	$tipo_dcto = $venta['venta_tipo_dcto'];


		$comision_total = 0;

		// obtiene comisiones de cada producto
		$query_detalle = "SELECT * FROM venta_detalle WHERE rel_venta_id='".$id."'";
		$result_detalle = mysql_query($query_detalle, $GLOBALS["conexion_slave"]) or die(mysql_error());
		while ($row_detalle = mysql_fetch_array($result_detalle))
		{
			
			$comision = 0;
			$subto = $row_detalle['subtotal'];
			$costo_subtotal = $row_detalle['costo_subtotal'];

			// las boletas se almacenan en bruto
			// le quitamos el IVA
			if( es_be($tipo_dcto)==1 and $row_detalle['producto_exento']==0 ){
				$subto = ( $subto / $_SESSION['tasa_iva2'] );
			}

			
			if( Get_ProductoExiste($row_detalle['producto_id']) > 0 ){
			
				$producto_comision = Get_ProductoComision($row_detalle['producto_id'], $empleado['empleado_id'], $subto, $row_detalle['cantidad'], $costo_subtotal);
				$comision = $producto_comision['comision_base'];

				$descuento = 0;
				if( $descuento_global > 0 ){
					$descuento = ( ( $comision * $descuento_global ) / 100 );
				}
				$comision = ( $comision - $descuento );


				//echo '<br>';
				//print_r($producto_comision);
			
			
				$comision_total = ( $comision_total + $comision );
				
				$sql_UpdateDetalle = "UPDATE venta_detalle SET 
									 comision_sistema = '".$producto_comision['comision_sistema']."', 
									 comision_tipo = '".$producto_comision['comision_tipo']."', 
									 comision_factor = '".$producto_comision['comision_factor']."', 
									 comision_base = '".$comision."' 
									 WHERE vd_id='".$row_detalle['vd_id']."' AND rel_venta_id='".$id."'
									 ";
				$result_UpdateDetalle = mysql_query($sql_UpdateDetalle) or die(mysql_error());
			
			}
			
			
		} mysql_free_result($result_detalle);
		
		
		
		
		// actualizamos el total de comision de la venta...
		 $sql_UpdateVenta = "UPDATE venta SET venta_comision_base='".$comision_total."' WHERE venta_id='".$id."'";
	     $result_UpdateVenta = mysql_query($sql_UpdateVenta) or die(mysql_error());

		// Guardamos historial
		Set_VentaHistory ( 'Comision re-calculada', '', '', date('d-m-Y'), date('H:i:s'), $id, $_SESSION['id_user'] );
		
		//echo '<br>Comision re-calculada...';
		

	} mysql_free_result($result_venta);


}


//**********************************************************************************
// *** crea movimiento de inventario
/*
	$data['fecha'] = dd-mm-yyyy
	$data['bodega'] = id bodega
	$data['tipo_movimiento'] = ENTRADA / SALIDA / TRASPASO
	$data['metodo'] = entrada / entrada-devolucion / salida / salida-devolucion
	$data['concepto'] = 
		
	$data['items'] = array con items
	
*/
function Set_LibroInventario( $data ){

	global $libro_id;

	$detener_movimiento = 0;
	
	$fecha = $data['fecha'];
	if( empty($fecha) ){
		$fecha = date('d-m-Y');
	}

	$iFecha = Set_Fecha_Usa($fecha);
	$fecha_ = explode('-', $iFecha);
	$iDia = $fecha_[2];
	$iMes = $fecha_[1];
	$iAno = $fecha_[0];



	$bodega = $data['bodega'];
	$tipo_movimiento = $data['tipo_movimiento'];
	$metodo = $data['metodo'];
	$iConcepto = $data['concepto'];
	$referencia_movimiento = $data['referencia'];

	$items = $data['items'];	

	// determina si se envia el webhook
	$enviar_webhook = 1;
	if( isset($data['enviar_webhook']) ){
	$enviar_webhook = $data['enviar_webhook'];
	}

	
	$proveedor_id = $data['proveedor_id'];
	$compra_id = $data['compra_id'];
	$compra_oc_id = $data['compra_oc_id'];
	
	$cliente_id = $data['cliente_id'];
	$venta_id = $data['venta_id'];
	$pedido_id = $data['pedido_id'];

	$external_id = $data['external_id'];

	// parametro para controlar manualmente la empresa
	
	$empresa_id = $_SESSION['id_empresa'];

	if( !empty($data['empresa_id']) and $data['empresa_id']>0 ){
		$empresa_id = $data['empresa_id'];
	}
	
	$id_empresa = $empresa_id;


	
	// paso 1
	// verificar bodega sea mayor 0

		if( $bodega==0 ){
			$detener_movimiento = 1;
		}

	// paso 2
	// revisar array de items, y ver si items son inventariables

		if( $detener_movimiento==0 ){

			$inventariable = 0;

			foreach ($items as $k => $v) {
				
			
				$query_verify_1 = "SELECT producto_inventariable FROM producto WHERE producto_id='".$v['id']."' AND rel_empresa_id='".$id_empresa."'";
				$result_verify_1 = mysql_query( $query_verify_1, $GLOBALS["conexion_slave"] ) or die( mysql_error() );
				$row_verify_1 = mysql_fetch_array( $result_verify_1 );	
				
				if($row_verify_1['producto_inventariable']==1){

					$inventariable++;

				}

			}
			
			if($inventariable==0){
				$detener_movimiento = 1;
			}

		}

	// paso 3
	// creamos el libro

		if( $detener_movimiento == 0 ){

			if( $libro_id<=0 ){

				// obtenemos folio
				$query_folio = "SELECT max(pil_folio) as folio FROM producto_inventario_libro WHERE rel_empresa_id='".$id_empresa."' ";
				$result_folio = mysql_query($query_folio) or die( mysql_error() );
				$row_folio = mysql_fetch_array($result_folio);

				$nuevo_folio = ( $row_folio['folio'] + 1 );

					
					

				// Insertamos registro 
				$sql_libroinventario = "INSERT INTO producto_inventario_libro SET 
										pil_folio='".$nuevo_folio."',
										pil_dia='".$iDia."',
										pil_mes='".$iMes."',
										pil_ano='".$iAno."',
										pil_fecha='".$iFecha."',
										
										pil_tipo='".$tipo_movimiento."',
										pil_concepto='".$iConcepto."',
										pil_referencia='".$referencia_movimiento."',
										pil_consumointerno='".$movimiento_consumointerno."',
										pil_ingreso_fecha='".date('d-m-Y H:i:s')."',
										pil_ingreso_usuario='".$_SESSION['id_user']."',
										
										rel_proveedor_id='".$proveedor_id."',
										rel_cliente_id='".$cliente_id."',
										rel_compra_id='".$compra_id."',
										rel_compra_oc_id='".$compra_oc_id."',
										rel_venta_id='".$venta_id."',
										rel_pedido_id='".$pedido_id."',
										rel_external_id='".$external_id."',
										
										rel_empresa_id='".$id_empresa."'
										";
				$result_libroinventario = mysql_query( $sql_libroinventario ) or die( mysql_error() );


				//Extraer id entrada
				$libro_id = mysql_insert_id();
			}

		}

		
	// paso 4
	// ingresamos los movimientos

		if( $libro_id>0 ){

			// verifica si existe el metodo
			/*
			if($metodo=='entrada'$metodo=='entrada'){

				$detener_movimiento = 1;

			}
			*/

				
			foreach ($items as $k => $v) {

				//
				// NO USAR este metodo > $row_producto = Get_Producto ( $v['id'] );
				// consultar directo en DB
				$sql_producto = "SELECT * FROM producto WHERE producto_id='".$v['id']."' AND rel_empresa_id='".$id_empresa."' ";
				$result_producto = mysql_query($sql_producto, $GLOBALS["conexion_slave"]) or die( mysql_error() );
				$row_producto = mysql_fetch_array($result_producto);

				
				// Kit-combo
				if( $row_producto['producto_tipo']==2 and $metodo=='salida' ){ 
					
					//*******************************************************************************************
					// Paso 1: Crea la salida de los componentes, materias primas	
						
						
					$query_componente = " SELECT rel_componente_id, pldm_cantidad FROM producto_ldm WHERE rel_producto_id='".$v['id']."' ";
					$result_componente = mysql_query( $query_componente, $GLOBALS["conexion_slave"] ) or die( mysql_error() );
					while( $row_componente = mysql_fetch_array( $result_componente ) ) {

						
						$query_verify_2 = "SELECT producto_tipo, producto_inventariable FROM producto WHERE producto_id='".$row_componente['rel_componente_id']."' AND rel_empresa_id='".$id_empresa."'";
						$result_verify_2 = mysql_query( $query_verify_2, $GLOBALS["conexion_slave"] ) or die( mysql_error() );
						$row_verify_2 = mysql_fetch_array( $result_verify_2 );
						
						
						if($row_verify_2['producto_inventariable']==1){
						
						
							$tipo_movimiento_2 = 'SALIDA';  //esta declaracion es temporal, debe ser verificada
							//$iObservacion = $NombreDTE_." # ".$folio;
							
							$query_saldo = "SELECT pi_saldo, pi_costo_promedio FROM producto_inventario WHERE rel_producto_id='".$row_componente['rel_componente_id']."' AND rel_bodega_id='".$bodega."' ORDER BY pi_id DESC LIMIT 1";
							$result_saldo = mysql_query($query_saldo) or die ( mysql_error() );
							$saldo = mysql_fetch_array($result_saldo);
							
								$iSalida = $row_componente['pldm_cantidad'] * $v['cantidad'];
								$iEntrada = 0;
								$SaldoHabia = $saldo['pi_saldo'];
								$SaldoNuevo = ($SaldoHabia - $iSalida);

								$iLote = ''; //$v['lote'];
								$iLoteFechaVcto = ''; //$v['lote_fecha_vcto'];

								
								//if( !isset($iConcepto) ) { $iConcepto = '6'; } // 6 = Ventas
								
								$iObservacion_2 = 'Transformar en Kit';
							
							
								
							$update_stock = "INSERT INTO producto_inventario (pi_dia, pi_mes, pi_ano, pi_fechaingreso, pi_tipo_movimiento, pi_concepto, pi_observacion, rel_origen_id, rel_producto_id, rel_bodega_id, rel_usuario_id, pi_costo, pi_costo_promedio, pi_entrada, pi_salida, pi_habia, pi_saldo, rel_empresa_id) 
							VALUES ('".$iDia."', '".$iMes."', '".$iAno."', '".date('Y-m-d H:i:s')."', '$tipo_movimiento_2', '$iConcepto', '$iObservacion_2', '$libro_id', '".$row_componente['rel_componente_id']."', '".$bodega."', '".$_SESSION['id_user']."', '{$v['producto_precio']}', '$CostoPromedio', '$iEntrada', '$iSalida', '$SaldoHabia', '$SaldoNuevo', '".$id_empresa."') ";
							$result_update_stock = mysql_query($update_stock) or die(mysql_error());
							

							
							// registra stock en tabla resumen
							
							$iClaveProducto = $id_empresa.'-'.$bodega.'-'.$row_componente['rel_componente_id'].'-'.$iLote;

							$update_stock2 = "INSERT INTO producto_stock (ps_id_unico, ps_saldo, ps_lote, rel_producto_id, rel_bodega_id, rel_empresa_id) VALUES ('".$iClaveProducto."','".$SaldoNuevo."','".$iLote."','".$row_componente['rel_componente_id']."','".$bodega."','".$id_empresa."') ON DUPLICATE KEY UPDATE ps_saldo=VALUES(ps_saldo), ps_fecha_actualizacion=VALUES(ps_fecha_actualizacion), ps_ultimo_movimiento=VALUES(ps_ultimo_movimiento)";
							$result_update_stock2 = mysql_query($update_stock2) or die(mysql_error());
							
						
						}
						

					} mysql_free_result( $result_componente);


					//*******************************************************************************************
					// Paso 2: Crea la entrada del Kit



						
						$query_verify_3 = "SELECT producto_inventariable FROM producto WHERE producto_id='".$v['id']."' AND rel_empresa_id='".$id_empresa."'";
						$result_verify_3 = mysql_query( $query_verify_3, $GLOBALS["conexion_slave"]  ) or die( mysql_error() );
						$row_verify_3 = mysql_fetch_array( $result_verify_3 );	
						
						if($row_verify_3['producto_inventariable']==1){
						
							$tipo_movimiento_3 = 'ENTRADA';  //esta declaracion es temporal, debe ser verificada
							//$iObservacion = $NombreDTE_." # ".$folio;
							
							$query_saldo = "SELECT pi_saldo, pi_costo_promedio FROM producto_inventario WHERE rel_producto_id='".$v['id']."' AND rel_bodega_id='".$bodega."' ORDER BY pi_id DESC LIMIT 1";
							$result_saldo = mysql_query($query_saldo) or die ( mysql_error() );
							$saldo = mysql_fetch_array($result_saldo);
							
								$iSalida = 0;
								$iEntrada = $v['cantidad'];
								$SaldoHabia = $saldo['pi_saldo'];
								$SaldoNuevo = ($SaldoHabia + $iEntrada);

								$iLote = ''; //$v['lote'];
								$iLoteFechaVcto = ''; //$v['lote_fecha_vcto'];

								
								$CostoPromedio_Anterior = Get_ProductoCostoPromedio ($v['id']);
								$CostoHabia = ( $CostoPromedio_Anterior * $SaldoHabia );
								$CostoNuevo =  ( $v['producto_precio'] * $iEntrada );
								$CostoPromedio = (($CostoHabia + $CostoNuevo) / $SaldoNuevo);
								
								$iObservacion_3 = 'Kit transformado';
								
							
							
								
							$update_stock = "INSERT INTO producto_inventario (pi_dia, pi_mes, pi_ano, pi_fechaingreso, pi_tipo_movimiento, pi_concepto, pi_observacion, rel_origen_id, rel_producto_id, rel_bodega_id, rel_usuario_id, pi_costo, pi_costo_promedio, pi_entrada, pi_salida, pi_habia, pi_saldo, rel_empresa_id) 
							VALUES ('".$iDia."', '".$iMes."', '".$iAno."', '".date('Y-m-d H:i:s')."', '$tipo_movimiento_3', '$iConcepto', '$iObservacion_3', '$libro_id', '{$v['id']}', '".$bodega."', '".$_SESSION['id_user']."', '{$v['producto_precio']}', '$CostoPromedio', '$iEntrada', '$iSalida', '$SaldoHabia', '$SaldoNuevo', '".$id_empresa."') ";
							$result_update_stock = mysql_query($update_stock) or die(mysql_error());
							

							
							// registra stock en tabla resumen
							
							$iClaveProducto = $id_empresa.'-'.$bodega.'-'.$v['id'].'-'.$iLote;

							$update_stock2 = "INSERT INTO producto_stock (ps_id_unico, ps_saldo, ps_lote, rel_producto_id, rel_bodega_id, rel_empresa_id) VALUES ('".$iClaveProducto."','".$SaldoNuevo."','".$iLote."','".$v['id']."','".$bodega."','".$id_empresa."') ON DUPLICATE KEY UPDATE ps_saldo=VALUES(ps_saldo), ps_fecha_actualizacion=VALUES(ps_fecha_actualizacion), ps_ultimo_movimiento=VALUES(ps_ultimo_movimiento)";
							$result_update_stock2 = mysql_query($update_stock2) or die(mysql_error());

							
						}
						
				}


				// mueve el producto
				if( $row_producto['producto_inventariable']==1 ){

					$iLote = $v['lote'];
					$iLoteFechaVcto = $v['lote_fecha_vcto'];
					
				
					$query_saldo = "SELECT pi_saldo FROM producto_inventario WHERE rel_producto_id='".$v['id']."' AND rel_bodega_id='".$bodega."' ORDER BY pi_id DESC LIMIT 1";
					$result_saldo = mysql_query($query_saldo) or die ( mysql_error() );
					$saldo = mysql_fetch_array($result_saldo);
					
					if($metodo=='entrada'){
						$iSalida = 0;
						$iEntrada = $v['cantidad'];
						$SaldoHabia = $saldo['pi_saldo'];
						$SaldoNuevo = ($SaldoHabia + $iEntrada);
					}
					if($metodo=='entrada-devolucion'){
						$iSalida = 0;
						$iEntrada = ( - $v['cantidad'] );
						$SaldoHabia = $saldo['pi_saldo'];
						$SaldoNuevo = ($SaldoHabia + $iEntrada);
					}

					if($metodo=='salida'){
						$iSalida = $v['cantidad'];
						$iEntrada = 0;
						$SaldoHabia = $saldo['pi_saldo'];
						$SaldoNuevo = ($SaldoHabia - $iSalida);
					}
					if($metodo=='salida-devolucion'){
						$iSalida = ( - $v['cantidad'] );
						$iEntrada = 0;
						$SaldoHabia = $saldo['pi_saldo'];
						$SaldoNuevo = ($SaldoHabia - $iSalida);
					}

					// saldo total del LOTE
					
					$SaldoHabiaLote = $SaldoHabia;

					if( !empty($iLote) ){
						$query_saldoLote = "SELECT pi_lote_saldo, pi_costo_promedio FROM producto_inventario WHERE rel_producto_id='".$v['id']."' AND pi_lote='".$iLote."' AND rel_bodega_id='".$bodega."' ORDER BY pi_id DESC LIMIT 1";
						$result_saldoLote = mysql_query($query_saldoLote) or die ( mysql_error() );
						$saldo_lote = mysql_fetch_array($result_saldoLote);
						
						$SaldoHabiaLote = $saldo_lote['pi_lote_saldo'];
					}

					if($metodo=='entrada'){
						$iSalidaLote = 0;
						$iEntradaLote = $v['cantidad'];
						//$SaldoHabiaLote = $saldo['pi_lote_saldo'];
						$SaldoNuevoLote = ($SaldoHabiaLote + $iEntradaLote);
					}

					if($metodo=='entrada-devolucion'){
						$iSalidaLote = 0;
						$iEntradaLote = ( - $v['cantidad'] );
						//$SaldoHabiaLote = $saldo['pi_lote_saldo'];
						$SaldoNuevoLote = ($SaldoHabiaLote + $iEntradaLote);
					}

					if($metodo=='salida'){
						$iSalidaLote = $v['cantidad'];
						$iEntradaLote = 0;
						//$SaldoHabiaLote = $saldo['pi_lote_saldo'];
						$SaldoNuevoLote = ($SaldoHabiaLote - $iSalidaLote);
					}

					if($metodo=='salida-devolucion'){
						$iSalidaLote = ( - $v['cantidad'] );
						$iEntradaLote = 0;
						//$SaldoHabiaLote = $saldo['pi_lote_saldo'];
						$SaldoNuevoLote = ($SaldoHabiaLote - $iSalidaLote);
					}
					


					
					
					// costos...
					$costo_historico = Get_ProductoCostos ( $v['id'], '' );
					$CostoPromedio = $costo_historico['avg'];
					$CostoPromedio = number_format($costo_historico['avg'],0,'','');
					
					
					if( !isset($iConcepto) ) { $iConcepto = '1'; } // 3 = Compras

			
			
					// costo estandar solo se actualiza manualmente
					// desde la ficha del producto
					// o desde los excels de importacion/actualizacion
					if(!isset($CostoEstandar)){
						$CostoEstandar = $v['producto_precio'];
					}
					

					//
					$update_stock = "INSERT INTO producto_inventario (pi_dia, pi_mes, pi_ano, pi_fechaingreso, pi_tipo_movimiento, pi_concepto, pi_observacion, rel_origen_id, rel_producto_id, rel_bodega_id, rel_usuario_id, pi_costo, pi_costo_promedio, pi_entrada, pi_salida, pi_habia, pi_saldo, pi_lote, pi_lote_habia, pi_lote_saldo, pi_fecha_vcto, rel_empresa_id) 
					VALUES ('".$iDia."', '".$iMes."', '".$iAno."', '".date('Y-m-d H:i:s')."', '$tipo_movimiento', '$iConcepto', '$iObservacion', '$libro_id', '{$v['id']}', '".$bodega."', '".$_SESSION['id_user']."', '$CostoEstandar', '$CostoPromedio', '$iEntrada', '$iSalida', '$SaldoHabia', '$SaldoNuevo', '$iLote', '$SaldoHabiaLote', '$SaldoNuevoLote', '$iLoteFechaVcto', '".$id_empresa."') ";
					$result_update_stock = mysql_query($update_stock) or die(mysql_error());
					
					

					// registra stock en tabla resumen
					
					$iClaveProducto = $id_empresa.'-'.$bodega.'-'.$v['id'].'-'.$iLote;

					$update_stock2 = "INSERT INTO producto_stock (ps_id_unico, ps_saldo, ps_lote, ps_lote_fecha_vcto, ps_fecha_actualizacion, ps_ultimo_movimiento, rel_producto_id, rel_bodega_id, rel_empresa_id) VALUES ('".$iClaveProducto."','".$SaldoNuevoLote."','".$iLote."','".$iLoteFechaVcto."','".date('Y-m-d H:i:s')."','".$libro_id."','".$v['id']."','".$bodega."','".$id_empresa."') ON DUPLICATE KEY UPDATE ps_saldo=VALUES(ps_saldo), ps_fecha_actualizacion=VALUES(ps_fecha_actualizacion), ps_ultimo_movimiento=VALUES(ps_ultimo_movimiento)";
					$result_update_stock2 = mysql_query($update_stock2) or die(mysql_error());

							

			
					if( $metodo=='entrada' or $metodo=='entrada-devolucion' ){
					if( !isset($CostoOmitir) ){

						if($row_producto['producto_tipo']!=2 and $row_producto['producto_tipo']!=3){
						// los productos de tipo fabricado y kombo no se actualizan

							// Actualiza costos producto
							if ( $tipo_dcto=='30' or $tipo_dcto=='33' or $tipo_dcto=='914' ) {
								$update_producto = "UPDATE producto 
													SET 
													producto_costo_clp_neto='{$v['producto_precio']}' 
													WHERE producto_id='{$v['id']}'";
								$result_update_producto = mysql_query($update_producto) or die(mysql_error());
							}
							
							// actualiza costo promedio
							if ( $tipo_dcto>0 ){
								$update_producto = "UPDATE producto 
													SET 
													producto_costo_clp_neto_promedio='$CostoPromedio' 
													WHERE producto_id='{$v['id']}'";
								$result_update_producto = mysql_query($update_producto) or die(mysql_error());		
							}
							
						}
						
					}
					}


				}


			}			
		
		
		}




	//******************************************************************************************
	// WebHooks notifications
	//if($_SESSION['id_empresa']==2){
	if( $enviar_webhook==1 ){

		$event = 'productoStock.created';
		$webhook_list = get_webhook($event); 


		if ( count($webhook_list)>0 ){ 
			foreach ($webhook_list as $webhook) {


				if( $webhook['webhooks_id']>0 ){
					// enviamos el webhook

					$data = array();
					$data['event'] = $webhook['webhooks_event'];
					$data['url'] = $webhook['webhooks_url'];
					$data['client_secret'] = $webhook['webhooks_client_secret'];

					// data a enviar de la entidad
					// Se envia de forma diferida por cron job, para esperar 
					// a que se ingrese el detalle de los items
					$data['envio_diferido'] = 1;
					$data['data_diferido'] = 'producto_inventario_libro:'.$libro_id;


					$data['data'] = '';

					

					set_webhook_send($data);


				}

			}
		}

	}
	// FIN WebHooks
	//******************************************************************************************



		

	return $libro_id;

}







// actualizar costos de producto con LdM
//


        function Set_Producto_CostosFromLdM ( $row_producto, $empresa_configuracion ) {

            $producto_id = $row_producto['producto_id'];



            $costo_a_usar = $empresa_configuracion['catalogo_costo_a_usar'];

            if($costo_a_usar==0){   //costo promedio
                $costo_tag = 'Costo promedio';
                $costo_field = 'producto_costo_clp_neto_promedio';
            }
            if($costo_a_usar==1){   //costo ultima compra
                $costo_tag = 'Costo ultima compra';
                $costo_field = 'producto_costo_clp_neto';
            }
            if($costo_a_usar==2){   //costo estandar
                $costo_tag = 'Costo estandar';
                $costo_field = 'producto_costo_clp_neto_estandar';
            }
            

            // comienza recorrido para calculo
            
            $costo_subtotal_total = 0;
            $precio_subtotal_total = 0;
            
            $cont=1;
            
            $query_ldm = "SELECT pldm_id, producto_id, producto_codigo_comercial, producto_unidad_medida, producto_nombre, producto_costo_clp_neto_promedio, producto_costo_clp_neto, producto_costo_clp_neto_estandar, producto_precio_clp_neto, pldm_cantidad 
                            FROM producto_ldm 
                            INNER JOIN producto ON producto.producto_id=producto_ldm.rel_componente_id 
                                                                                                            
             WHERE producto_ldm.rel_producto_id='".$producto_id."' ORDER BY producto_nombre ASC";
            $result_ldm = mysql_query($query_ldm, $GLOBALS["conexion_slave"]) or die ( mysql_error() );
            while ( $row_ldm = mysql_fetch_array($result_ldm) ) {


                if($costo_a_usar==0){   //costo promedio
                    
                    $costo = $row_ldm['producto_costo_clp_neto_promedio'];
                }
                if($costo_a_usar==1){   //costo ultima compra
                    
                    $costo = $row_ldm['producto_costo_clp_neto'];
                }
                if($costo_a_usar==2){   //costo esandar
                    
                    $costo = $row_ldm['producto_costo_clp_neto_estandar'];
                }


            
                $costo_subtotal = ( $costo * $row_ldm['pldm_cantidad'] );

                $precio_subtotal = ( $row_ldm['producto_precio_clp_neto'] * $row_ldm['pldm_cantidad'] );
                
                
                
                $costo_subtotal_total = ( $costo_subtotal_total + $costo_subtotal );
                $precio_subtotal_total = ( $precio_subtotal_total + $precio_subtotal );

            } mysql_free_result($result_ldm); 




            $alert_cambio = 0;

            $row_producto[$costo_field] = number_format($row_producto[$costo_field],0,'','');
    		$costo_subtotal_total = number_format($costo_subtotal_total,0,'','');

            if($row_producto[$costo_field]!=$costo_subtotal_total and $empresa_configuracion['productos_actualizar_costos_segun_ldm']==1 ){
                
                

                // actualiza el costo
                if($costo_subtotal_total>0){

                    $sql_UpdateCosto="UPDATE producto 
                                      SET 
                                      $costo_field='".$costo_subtotal_total."'
                                      WHERE 
                                      producto_id='".$producto_id."' 
                                      AND rel_empresa_id='".$_SESSION['id_empresa']."'
                                     ";
                    $result_UpdateCosto = mysql_query($sql_UpdateCosto) or die( mysql_error() );
                    //echo $sql_UpdateCosto;
                

                    $alert_cambio = 1;
                    $alert_cambio_text = "Costo producto almacenado no es igual a Costo subtotal LdM. Se actualiza costo en uso.";

                    echo '<br>Actualizando costos productos segun costo LdM... ';
                    echo '<br>Costo almacenado: '.$row_producto[$costo_field].' Costo LdM : '.$costo_subtotal_total;
                    
                }

            }



            //*******************************************************************
            // precio


            $row_producto['producto_precio_clp_neto'] = number_format($row_producto['producto_precio_clp_neto'],0,'','');
    		$precio_subtotal_total = number_format($precio_subtotal_total,0,'','');

            if($row_producto['producto_precio_clp_neto']!=$precio_subtotal_total and $empresa_configuracion['productos_actualizar_precios_segun_ldm']==1 ){

                
                // actualiza el precio
                if($precio_subtotal_total>0){

                    $new_precio_neto = $precio_subtotal_total;
                    $new_precio_iva = ( ( $new_precio_neto * $_SESSION['tasa_iva'] ) / 100 );
                    $new_precio_total = ( $new_precio_neto + $new_precio_iva );

                    $sql_UpdatePrecio="UPDATE producto 
                                      SET 
                                      producto_precio_clp_neto='".$new_precio_neto."',
                                      producto_precio_clp_iva='".$new_precio_iva."',
                                      producto_precio_clp_total='".$new_precio_total."' 
                                      WHERE 
                                      producto_id='".$row_producto['producto_id']."' 
                                      AND rel_empresa_id='".$_SESSION['id_empresa']."'
                                     ";
                    $result_UpdatePrecio = mysql_query($sql_UpdatePrecio) or die( mysql_error() );
                    //echo $sql_UpdatePrecio;
                

                    $alert_cambio = 2;
                    $alert_cambio_text = "Precio producto almacenado no es igual a Precio subtotal LdM. Se actualiza precio.";

                    echo '<br>Actualizando precios productos segun precio LdM... ';
                    echo '<br>Precio almacenado: '.$row_producto['producto_precio_clp_neto'].' Precio LdM : '.$precio_subtotal_total;


                }

            }


            return $alert_cambio;

        }



// detecta si este producto es componente de otro
//


        function Get_Producto_EsComponenteLdM ( $id_componente ) {


            $es_componente = 0;

                $sql_ = "SELECT pldm_id FROM producto_ldm  
                                    WHERE 
                                    rel_componente_id='".$id_componente."' AND rel_empresa_id='".$_SESSION['id_empresa']."'
                                    ";
                $result_ = mysql_query($sql_, $GLOBALS["conexion_slave"]) or die(mysql_error());
                $row_ = mysql_fetch_array($result_);

                if( $row_['pldm_id']>0 ){
                    $es_componente = $row_['pldm_id']; 
                }


            return $es_componente;

        }




// detecta los productos de los cuales es componente
//


        function Get_Producto_ComponenteLdMs ( $id_componente ) {

            $dato = array();

                $sql_ = "SELECT rel_producto_id FROM producto_ldm  
                                    WHERE 
                                    rel_componente_id='".$id_componente."' AND rel_empresa_id='".$_SESSION['id_empresa']."'
                                    ";
                $result_ = mysql_query($sql_, $GLOBALS["conexion_slave"]) or die(mysql_error());
                while ( $row_ = mysql_fetch_array($result_) ){

                    $dato[] = $row_['rel_producto_id'];

                } mysql_free_result( $result_ );


            return $dato;

        }




// devuelve la LDM de un producto
//


        function Get_Producto_LdMs ( $id_producto, $full = 0 ) {

            $dato = array();

                $sql_ = "SELECT rel_componente_id, pldm_cantidad FROM producto_ldm  
                         WHERE 
                         rel_producto_id='".$id_producto."' AND rel_empresa_id='".$_SESSION['id_empresa']."'
                                    ";
                $result_ = mysql_query($sql_, $GLOBALS["conexion_slave"]) or die(mysql_error());
                while ( $row_ = mysql_fetch_array($result_) ){


                	if( $full==0 ){
		                
		                $dato[] = $row_['rel_componente_id'];

                	} else {
                		
                		$dato[] = array( 
                						'id_componente' => $row_['rel_componente_id'],
                						'cantidad' => $row_['pldm_cantidad']
                						);
                	
                	}



                } mysql_free_result( $result_ );


            return $dato;

        }

/**
 * Devuelve stock posible de los kits
 * en base a la cantidad de kits que se pueden armar con los stocks de los componentes
 * 
 * 
 * 
 * 
 * */

        function Get_ProductoStockLdm ( $id_producto, $id_bodega ) {

        	$dato = array();

        	$componentes = Get_Producto_LdMs ( $id_producto, '1' );

        	foreach ($componentes as $k => $v) {
        	
        		$id_componente = $v['id_componente'];
        		$cantidad = $v['cantidad'];

        		$stock_actual = Get_ProductoStock2( $id_componente, $id_bodega );

        		
	        	$kits_posibles = ( $stock_actual / $cantidad );
				
				if( $kits_posibles>=0 ){
	        		$dato[] = (int)$kits_posibles;
	        	}
        		
        	
        	}


        	return min($dato);


        }




?>