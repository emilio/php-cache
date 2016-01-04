<?php
require_once 'Cache.php';
function get_data() {
	// Perform a really dificult query and return the results
}

$data = Cache::get('my_key');

if( ! $data ) {
	$data = get_data();
	Cache::put('my_key', $data);
}

// Work with the data
