<?php 
set_time_limit(0);
ini_set('display_errors', true);
ini_set('memory_limit', '9999M');
require 'Cache.php';

printf('<a href="%1$s?test=big">Valores grandes</a> | <a href="%1$s?test=med">Medianos</a> | <a href="%1$s?test=small">Pequeños</a>', $_SERVER['PHP_SELF']);

if( ! (isset($_GET['test']) && in_array($_GET['test'], array('big', 'med', 'small')) ) ) {
	die('<br>Test no válido');
}

$valuefunc = 'get_rand_val_' . $_GET['test'];

echo "<pre>";

Cache::configure(array(
	'cache_path' => dirname(__FILE__) . '/cache',
	'expires' => 180
));

function get_rand_key() {
	return uniqid(rand());
}

function get_rand_val_small() {
	return md5(get_rand_key());
}

function get_rand_val_med() {
	$ret = array();

	for( $i = 0; $i < 10; $i++ ) {
		$ret[get_rand_val_small()] = get_rand_val_small();
	}
	return $ret;
}

function get_rand_val_big() {
	$ret = array();

	for( $i = 0; $i < 50; $i++ ) {
		$ret[get_rand_val_small()] = get_rand_val_med();
	}
	return $ret;
}

function make_filecache_loop() {
	global $valuefunc;
	$keys = array();
	$vals = array();

	echo "Comenzando a poner keys\n";
	for( $i = 0; $i < 1000; $i++ ) {
		$key = get_rand_key();
		$keys[] = $key;

		Cache::put($key, call_user_func($valuefunc));
	}

	echo "Comenzando a obtener keys\n";
	foreach ($keys as $key) {
		$vals[] = Cache::get($key);
	}

	echo "Comenzando a eliminar keys\n";
	foreach ($keys as $key) {
		Cache::delete($key);
	}
}

function make_apc_loop() {
	global $valuefunc;
	$keys = array();
	$vals = array();

	echo "Comenzando a poner keys\n";
	for( $i = 0; $i < 1000; $i++ ) {
		$key = get_rand_key();
		$keys[] = $key;

		apc_store($key, call_user_func($valuefunc));
	}

	echo "Comenzando a obtener keys\n";
	foreach ($keys as $key) {
		$vals[] = apc_fetch($key);
	}

	echo "Comenzando a eliminar keys\n";
	foreach ($keys as $key) {
		apc_delete($key);
	}
}

echo "Comienzo del test 1\n";

$filecache_init = microtime(true);


make_filecache_loop();

$filecache_end = microtime(true);
echo "Fin del test 1\n";

$filecache_dif = $filecache_end - $filecache_init;

echo "Comienzo del test 2\n";
$apc_init = microtime(true);

make_apc_loop();

$apc_end = microtime(true);
echo "Fin del test 2\n";
$apc_dif = $apc_end - $apc_init;

echo var_export(array(
	'filecache' => array(
		'init' => $filecache_init,
		'end' => $filecache_end,
		'dif' => $filecache_dif,
	),
	'apc' => array(
		'init' => $apc_init,
		'end' => $apc_end,
		'dif' => $apc_dif,
	)
));


echo "\nDiferencia total: ";

echo (abs($apc_dif - $filecache_dif)) . " segundos\n\n";

echo "Diferencia porcentual de velocidad: ";
echo 100 -  abs( $apc_dif / $filecache_dif * 100 ). '%';

echo "</pre>";