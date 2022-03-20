<?php

namespace cedilla;

use \PDO;
use \Exception;

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
	
	function __construct($options = []){
		$this->tstart = microtime(true);
		$this->routes = [];
		$this->dataset = [];
		$this->response = null;
		if(isset($options['db'])){
			$this->db = new DB(
				$options['db']['database'],
				isset($options['db']['user']) ? $options['db']['user'] : 'root',
				isset($options['db']['pass']) ? $options['db']['pass'] : '',
				isset($options['db']['host']) ? $options['db']['host'] : '127.0.0.1',
				isset($options['db']['port']) ? $options['db']['port'] : null,
				isset($options['db']['type']) ? $options['db']['type'] : DB::DB_MYSQL,
				isset($options['db']['dsn']) ? $options['db']['dsn'] : 'charset=utf8'
			);
		}else{
			$this->db = new DB();
		}
		$this->db->setApi($this);
	}

	public function route($name, $optionsOrCb, $cb = null){
		$this->routes[$name] = is_null($cb) ? [
			'options' => [],
			'cb' => $optionsOrCb->bindTo($this)
		] : [
			'options' => $optionsOrCb,
			'cb' => $cb->bindTo($this)
		];
		return $this;
	}

	private function parseRequire($options){
		if(!isset($options['require'])) return [];
		$args = [];
		$obj = $options['require'];
		var_dump($_POST);
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

	private function isRegex($pattern){
		return @preg_match($pattern, null) !== false;
	}

	private function findPossibleRoute($value){
		foreach ($this->routes as $finder => $route) {
			if( is_array($finder) && in_array($value, $finder) ) return $route;
			if( $this->isRegex($finder) && preg_match($finder, $value, $this->dataset) ) return $route;
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

		if(is_callable($options)){
			$hook = new RouteHook();
			$options($hook);
			$options = $hook->options;
		}

		$args = $this->parseRequire($options);

		$this->response->dieForError();
		
		$this->parseCheck($options);
		
		$this->response->dieForError();

		$this->response->done( $cb($args, $this->dataset, $this) );

	}
	
}

class RouteHook{
	public function __construct(){
		$this->options = [
			'require' => [],
			'check' => []
		];
	}
	public function require($key, $val){
		$this->options['require'][$key] = $val;
		return $this;
	}
	public function check($name, $cb){
		$this->options['check'][$name] = $cb;
		return $this;
	}
}

class DB{

	const DB_MYSQL = 'MYSQL';
	const DB_OCI = 'OCI';
	const DB_POSTGRESS = 'PG';
	const DB_MSSQL = 'SH1T';

	public function __construct($db = false, $user = 'root', $pass = '', $host = '127.0.0.1', $port = null, $type = self::DB_MYSQL, $dsn = 'charset=utf8'){
		$this->api = false;
		$this->link = null;
		$this->db = $db;
		$this->user = $user;
		$this->pass = $pass;
		$this->host = $host;
		$this->port = $port;
		$this->type = $type;
		$this->dsn = $dsn;
		//if($db) $this->init($db, $user, $pass, $host, $port, $type, $dsn);
	}

	public function setApi($api){
		$this->api = $api;
	}

	public function connect(){
		try{
			switch ($this->type) {
				case self::DB_MYSQL:
					$this->link = self::mysql_init($this->db, $this->user, $this->pass, $this->host, is_null($this->port) ? 3306 : $this->port, $this->dsn);
					break;
				case self::DB_OCI:
					$this->link = self::oci_init($this->db, $this->user, $this->pass, $this->host, is_null($this->port) ? 1521 : $this->port);
					break;
				case self::DB_POSTGRESS:
					$this->link = self::pg_init($this->db, $this->user, $this->pass, $this->host, is_null($this->port) ? 5432 : $this->port);
					break;
			}
		}catch(Exception $e){
			$this->api->response->endError('E:' . trim($e->getMessage()));
		}
	}

	public function query($query, $params = []){
		if(is_null($this->link)){
			$this->connect();
		}
		try{
			switch ($this->type) {
				case self::DB_MYSQL:
					return self::mysql_query($this->link, $query, $params);
				case self::DB_OCI:
					return self::oci_query($this->link, $query, $params);
				case self::DB_POSTGRESS:
					return self::pg_query($this->link, $query, $params);
			}
		}catch(Exception $e){
			$this->api->response->endError('E:' . trim($e->getMessage()));
		}
	}

	static public function mysql_init($db, $user = 'root', $pass = '', $host = '127.0.0.1', $port = 3306, $dsn = 'charset=utf8'){
		return new PDO( 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db . ';' . $dsn, $user, $pass, [
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_ALL_TABLES'",
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		]);
	}

	static public function mysql_query($link, $query, $params = []){
		$s = $link->prepare($query);
		foreach($params as $key => $v) {
			$s->bindParam($key, $v);
		}
		if($s->execute()){
			$r = $s->fetchAll(PDO::FETCH_ASSOC);
			return $r;
		} else {
			trigger_error(htmlentities(implode(' - ', $s->errorInfo()), ENT_QUOTES), E_USER_ERROR);
		}
	}

	static public function oci_init($service, $user = 'root', $pass = '', $host = '127.0.0.1', $port = 1521){
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

	static public function pg_init($db, $user = 'root', $pass = '', $host = '127.0.0.1', $port = 5432){
		return pg_connect("host=$host port=$port dbname=$db user=$user password=$password");
		//TODO gestione errore
	}

	static public function pg_query($link, $query, $params = []){
		//TODO
	}
}

?>