<?php

require_once __DIR__ . '/cedilla.php';

use ç\Api;

$api = new Api();

$api->route('testCedilla', [
	//'require' => [
	//	'post' => [ 'username' => 'string', 'password' => 'string' ]
	//]
], function($p, $response){
	$response->ok($_SERVER['PHP_SELF'] . ' Test Done!');
});

$api->server();

?>