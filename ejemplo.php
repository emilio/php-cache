<?php 
require_once 'Cache.php';
function obtener_datos() {
	// Realizar una consulta
}
$datos = Cache::get('mi_identificador');

if( ! $datos ) {
	$datos = obtener_datos();
	Cache::put('mi_identificador', $datos);
}

// Trabajar con los datos