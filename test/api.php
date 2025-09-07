<?php

require_once __DIR__ . '/../dist/php/cedilla.php';

use cedilla\Api;
use cedilla\Security;

/*
$api = new Api();
/**/
$api = new Api([
	//'csrf' => true
	'db' => [
		'database' => 'templatilla',
		'password' => 'root',
		'port' => 3306
	]
]);

/*
/////////////////

//DA GESTIRE SE ROUTE è FUNZIONE
$api->route([ 'cleanTest', 'test', 'main', 'root' ])
->do(function(){
	return 'done';
});

/**
$db = new DB('dadomaster', 'root', 'root');
$db->connect();
$db->beginTransaction();
try{
	// Getting single column from the single row
	$user = $db->exec("SELECT name FROM users WHERE email = ?", [$email])->fetchColumn();
	$v = $db->exec("INSERT INTO tipo_danno(nome) VALUES(:nome)", [
		':nome' => 'test2'
	]);
	var_dump($v);
	/**
	// Getting single row
	$user = $db->exec("SELECT * FROM users WHERE email = ?", [$email])->fetch();
	var_dump($user);
	// Getting array of rows
	$users = $db->exec("SELECT * FROM users LIMIT ?,?", [$offset, $limit])->fetchAll();
	var_dump($users);
	// Count Updated
	$updated = $db->exec("UPDATE users SET balance = ? WHERE id = ?", [$balance, $id])->rowCount();
	var_dump($updated);
	/**
	$db->commit();
	echo 'ok';
}catch(Exception $e){
	$db->rollback();
	echo $e;
}
/**

$api->route('customBD')
	->db([
		'database' => 'portal',
		'user' => 'root',
		'pass' => 'root',
	])
	->do(function(){
		$v = $this->db->exec('SELECT * FROM users')->fetchAll();
		return $v;
	});

/////////////////
/**

$api->route('requireIntTest')
->require('testV', 'int')
->do(function($p){
	return $p['testV'];
});

$api->route('requireListTest')
->require('testV', ['qui','quo','qua'])
->do(function($p){
	return $p['testV'];
});

/////////////////
/*
$api->route('testCedilla', [
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

/**/

$api->server();

?>