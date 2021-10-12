<?php

namespace cedilla;

require_once __DIR__ . '/config.php';

class Response{
	
	function __construct($tstart){
		$this->tstart = $tstart;
		$this->errors = [];
	}

	public function done($value=''){
		header('Content-Type: application/json');
		die(json_encode([
			'errors' => $this->errors,
			'response' => $value,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function addError($error){
		array_push($this->errors, $error);
	}

	public function endError($error){
		array_push($this->errors, $error);
		$this->done();
	}

	public function dieForError(){
		if(count($this->errors) > 0){
			$this->done();
		}
	}
	
	public function redirect($location){
		header('Location: ' . $location);
		die('');
	}
	
}

class Api{
	
	function __construct(){
		$this->tstart = microtime(true);
		$this->routes = [];
		$this->dataset = [];
		$this->response = null;
	}

	public function route($name, $optionsOrCb, $cb = null){
		$this->routes[$name] = is_null($cb) ? [
			'options' => [],
			'cb' => $optionsOrCb
		] : [
			'options' => $optionsOrCb,
			'cb' => $cb
		];
		return $this;
	}

	private function parseRequire($options){
		if(!isset($options['require'])) return [];
		$args = [];
		$obj = $options['require'];
		foreach ($obj as $key => $v) {
			if(!isset(CEDILLA_PARAMS_METHOD[$key])){
				$this->response->addError('R:' . $key);
				continue;
			}
			$vv = CEDILLA_PARAMS_METHOD[$key];
			if(is_bool($v)) {
				if(!$v){
					$this->response->addError('N:' . $key);
				}
			}elseif(is_array($v) && !in_array($vv, $v)){
				$this->response->addError('I:' . $key);
			}elseif(is_callable($v) && !$v($vv)){
				$this->response->addError('I:' . $key);
			}elseif(is_string($v)){
				switch ($v) {
					case 'string':
						$vv = strval($vv);
						break;
					case 'int':
						$vv = intval($vv);
						break;
					case 'float':
						$vv = floatval($vv);
						break;
				}
			}
			$args[$key] = $vv;
		}
		return $args;
	}

	private function parseCheck($options){
		if(isset($options['check'])){
			foreach ($options['check'] as $name => $cb) {
				if(!$cb()){
					$this->response->addError('C:' . $name);
				}
			}
		}
	}

	private function is_regex($pattern){
		return @preg_match($pattern, null) !== false;
	}

	private function findPossibleRoute($value){
		foreach ($this->routes as $finder => $route) {
			if( is_array($finder) && in_array($value, $finder) ) return $route;
			if( $this->is_regex($finder) && preg_match($finder, $value, $this->dataset) ) return $route;
			if( $finder == $value ) return $route;
		}
		$this->response->endError('B:' . $value);
	}

	public function server(){
		
		$this->response = new Response($this->tstart);

		if(!isset(CEDILLA_ROUTE_METHOD['_cedilla_route'])){
			$this->response->endError('A');
		}

		$route = $this->findPossibleRoute( CEDILLA_ROUTE_METHOD['_cedilla_route'] );

		$options = $route['options'];
		$cb = $route['cb'];

		$args = $this->parseRequire($options);

		$this->response->dieForError();
		
		$this->parseCheck($options);
		
		$this->response->dieForError();

		$this->response->done( $cb($args, $this->dataset, $this) );

	}
	
}

class DB{

	const DB_MYSQL = 'MYSQL';
	const DB_OCI = 'OCI';
	const DB_POSTGRESS = 'PG';

	public function __construct($db, $user = 'root', $pass = '', $host = '127.0.0.1', $port = null, $type = self::DB_MYSQL, $dsn = 'charset=utf8'){
		$this->type = $type;
		$this->link = null;
		switch ($this->type) {
			case self::DB_MYSQL:
				$this->link = self::buildMysql($db, $user, $password, $host, is_null($port) ? 3306 : $port, $dsn);
				break;
			case self::DB_OCI:
				$this->link = self::buildOci($db, $user, $password, $host, is_null($port) ? 1521 : $port);
				break;
			case self::DB_POSTGRESS:
				$this->link = self::buildPg($db, $user, $password, $host, is_null($port) ? 5432 : $port);
				break;
		}
	}

	public function query($query, $params = []){
		switch ($this->type) {
			case self::DB_MYSQL:
				return self::pdo_query($this->link, $query, $params);
			case self::DB_OCI:
				return self::oci_query($this->link, $query, $params);
		}
	}

	static public function buildMysql($db, $user = 'root', $pass = '', $host = '127.0.0.1', $port = 3306, $dsn = 'charset=utf8'){
		$link = null;
		$link = new PDO( 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db . ';' . $dsn, $user, $password, [
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_ALL_TABLES'"
		]);
		if(is_null($link)){
			trigger_error(htmlentities('db connection exception', ENT_QUOTES), E_USER_ERROR);
		}
		return $link;
	}

	static public function mysql_query($link, $query, $params = []){
		$s = $link->prepare($query);
		foreach($binderObj as $key => $_unused) {
			$s->bindParam($key, $binderObj[$key]);
		}
		if($s->execute()){
			$r = $s->fetchAll(PDO::FETCH_ASSOC);
			return $r;
		} else {
			trigger_error(htmlentities(implode(' - ', $s->errorInfo()), ENT_QUOTES), E_USER_ERROR);
		}
	}

	static public function buildOci($service, $user = 'root', $pass = '', $host = '127.0.0.1', $port = 1521){
		$link = oci_connect($user, $password, '(DESCRIPTION=(CONNECT_DATA=(SERVICE_NAME=' . $service . '))(ADDRESS=(PROTOCOL=TCP)(HOST=' . $host . ')(PORT=' . $port . ')))');
		if (!$link) {
			$e = oci_error();
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}
		return $link;
	}

	static public function oci_query($link, $query, $params = []){
		// Prepare the statement
		$stid = oci_parse($link, $query);
		if (!$stid) {
			$e = oci_error($conn);
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}
		//bind params
		foreach ($params as $k => $v) {
			if(!is_array($v)){
				$v = [
					'type' => is_int($v) ? SQLT_INT : SQLT_CHR,
					'val' => $v
				];
			}
			oci_bind_by_name($stid, $k, $v['val'], -1, $v['type']);
		}

		// Perform the logic of the query
		$r = oci_execute($stid);
		if (!$r) {
			$e = oci_error($stid);
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}
		$res = [];
		while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
			array_push($res, $row);
		}
		oci_free_statement($stid);
		return $res;
	}

	static public function buildPg($db, $user = 'root', $pass = '', $host = '127.0.0.1', $port = 5432){
		return pg_connect("host=$host port=$port dbname=$db user=$user password=$password");
		//TODO gestione errore
	}

	static public function pg_query($link, $query, $params = []){
		//TODO
	}
}

?>