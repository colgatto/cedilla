<?php

require_once __DIR__ . '/cedilla.php';

use cedilla\Api;

$api = new Api();

$api->route( 'testCedilla', [
	'require' => [
		'testP' => true
	], 
	'check' => [
		'login' => function(){ return isset($_SESSION['user']); }
	]
], function($p, $response){
	//$response->done($p);
	return $p;
});

$api->server();

?>