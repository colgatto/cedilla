<?php

require_once __DIR__ . '/../dist/php/cedilla.php';

use cedilla\Api;

$api = new Api([
	'db' => [
		'database' => 'dadomaster',
		'password' => 'root'
	]
]);

/////////////////

//DA GESTIRE SE ROUTE è FUNZIONE
$api->route([ 'cleanTest', 'test', 'main', 'root' ])
	->do(function(){
		return 'done';
	});

/////////////////

/*
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
/**/

$api->route('queryTest')
	->require('danno', 'int')
	->db(true)
	->do(function($p){
		$v = $this->db->exec('SELECT * FROM arma WHERE fk_tipo_danno = :fk_tipo_danno', [
			':fk_tipo_danno' => $p['danno']
		])->fetchAll();
		return $v;
	});

$api->route('testInsert')
	->require('danno', 'int')
	->db(true)
	->do(function($p){
		$this->db->beginTransaction();
		try{
			$v = $this->db->exec('INSERT INTO arma(nome,danni,fk_tipo_danno,tp_stat,fk_tipo_arma) VALUES (:nome, :danni, :fk_tipo_danno, :tp_stat, :fk_tipo_arma)', [
				':nome' => 'Prova6',
				':danni' => $p['danno'],
				':tp_stat' => 'FOR',
				':fk_tipo_danno' => 15,
				':fk_tipo_arma' => 7
			])->rowCount();
			$this->db->commit();
			return $v;
		}catch(Exception $e){
			$this->db->rollback();
			return 'problem on db: ' . $e;
		}
	});

$api->route('customBD')
	->db([
		'database' => 'portal',
		'user' => 'root',
		'pass' => 'root',
	])
	->do(function(){
		$v = $this->db->exec('SELECT * FROM booking_uffici')->fetchAll();
		return $v;
	});

/////////////////
/*
$api->route('requireTest', [
	'require' => [
		'testV' => true
	], 
], function($p){
	return $p['testV'];
});

$api->route('requireIntTest', [
	'require' => [
		'testV' => 'int'
	], 
], function($p){
	return $p['testV'];
});

$api->route('requireListTest', [
	'require' => [
		'testV' => ['qui','quo','qua']
	], 
], function($p){
	return $p['testV'];
});

/////////////////

$api->route('checkPassed', [
	'check' => [
		'3UNDER30' => function(){ return 3 < 30; }
	] 
], function($p){
	return 'done';
});

$api->route('checkNotPassedTest', [
	'check' => [
		'3OVER30' => function(){ return 3 > 30; }
	] 
], function($p){
	return 'done';
});

/////////////////

$api->route('login', [
	'require' => [
		'username' => 'string',
		'password' => 'string'
	], 
], function($p){
	if($p['username'] == 'pippo' && $p['password'] == '12345' ){
		$_SESSION['user'] = 'pippo';
	}
	return $this->response->done();
});

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

/////////////////

$api->route('/testRegex([0-9]+)/', function($p, $matches){
	return 'passato con ' . $matches[1];
});

/////////////////

$api->route('testPriority',[
	'priority' => 3
], function($p, $matches){
	return 'vince 1';
});

$api->route('testPriority',function($route){
//	$route->priority(6);
}, function($p, $matches){
	return 'vince 2';
});
*/

$api->server();

?>