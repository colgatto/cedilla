<?php

require_once __DIR__ . '/../dist/php/cedilla.php';

use cedilla\Api;
use cedilla\DB;

$api = new Api([
	'db' => [
		'database' => 'dadomaster'
	]
]);

/////////////////

$api->route( 'cleanTest', function(){
	return 'done';
});

/////////////////

$api->route( 'queryTest', function($route){
	$route->require('danno', 'int');
},function($p){
	$v = $this->db->query('SELECT * FROM arma WHERE fk_tipo_danno = :fk_tipo_danno', [
		':fk_tipo_danno' => $p['danno']
	]);
	return $v;
});

$api->route( 'customBD', function(){
	$customDB = $this->db->new('portal');
	$v = $customDB->query('SELECT * FROM booking_uffici');
	return $v;
});

/////////////////

$api->route( 'requireTest', [
	'require' => [
		'testV' => true
	], 
], function($p){
	return $p['testV'];
});

$api->route( 'requireIntTest', [
	'require' => [
		'testV' => 'int'
	], 
], function($p){
	return $p['testV'];
});

$api->route( 'requireListTest', [
	'require' => [
		'testV' => ['qui','quo','qua']
	], 
], function($p){
	return $p['testV'];
});

/////////////////

$api->route( 'checkPassed', [
	'check' => [
		'3UNDER30' => function(){ return 3 < 30; }
	] 
], function($p){
	return 'done';
});

$api->route( 'checkNotPassedTest', [
	'check' => [
		'3OVER30' => function(){ return 3 > 30; }
	] 
], function($p){
	return 'done';
});

/////////////////

$api->route( 'login', [
	'require' => [
		'username' => 'string',
		'password' => 'string'
	], 
], function($p, $response){
	if($p['username'] == 'pippo' && $p['password'] == '12345' ){
		$_SESSION['user'] = 'pippo';
	}
	return $response->done();
});

$api->route( 'testCedilla', [
	'require' => [
		'valA' => 'int',
		'valB' => 'int',
		'action' => ['sum', 'sub', 'mul', 'div']
	], 
	'check' => [
		'login' => function(){ return isset($_SESSION['user']); }
	]
], function($p){
	switch($p['action']){
		case 'sum': $result = $p['valA'] + $p['valB']; break;
		case 'sub': $result = $p['valA'] - $p['valB']; break;
		case 'mul': $result = $p['valA'] * $p['valB']; break;
		case 'div': $result = $p['valA'] / $p['valB']; break;
	}
	return 'hello ' . $_SESSION['user'] . ' result is ' . $result;
});

/////////////////

$api->route( '/testRegex([0-9]+)/', function($p, $matches){
	return 'passato con ' . $matches[1];
});

$api->server();

?>