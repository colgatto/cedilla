<?php

namespace cedilla;

use \PDO;
use \Exception;

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

	public function new($db = false, $user = 'root', $pass = '', $host = '127.0.0.1', $port = null, $type = self::DB_MYSQL, $dsn = 'charset=utf8'){
		$newDB = new DB($db, $user, $pass, $host, $port, $type, $dsn);
		$newDB->setApi($this->api);
		return $newDB;
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