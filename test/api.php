<?php

require_once __DIR__ . '/../dist/php/cedilla.php';

use cedilla\Api;

/*
$api = new Api();
/**/
$api = new Api([
	//'csrf' => true
	'debug' => true,
	'override_error' => true,
	'show_error' => true,
	'db' => [
		'database' => 'templatilla',
		//'database' => 'templatilla2',
		'password' => 'root',
		'port' => 3306
	]
]);

$api->server();

?>