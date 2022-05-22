<?php

namespace cedilla;

use \PDO;
use \Exception;

class DB{

	const DB_MYSQL = 'MYSQL';
	const DB_OCI = 'OCI';
	const DB_POSTGRESS = 'PG';
	const DB_MSSQL = 'SH1T';

	public function __construct($options){
		$this->api = false;
		$this->link = null;
		$this->dbName = getDef($options, ['db','database'], false);
		$this->user = getDef($options, ['user','username'], 'root');
		$this->pass = getDef($options, ['pass','password'], '');
		$this->host = getDef($options, ['host','hostname'], '127.0.0.1');
		$this->port = getDef($options, 'port', null);
		$this->type = getDef($options, 'type', DB::DB_MYSQL);
		$this->dsn = getDef($options, 'dsn', 'charset=utf8');
		$this->inTrans = false;
	}

	public function connect($options = []){
		$this->dbName = getDef($options, ['db','database'], $this->dbName);
		$this->user = getDef($options, ['user','username'], $this->user);
		$this->pass = getDef($options, ['pass','password'], $this->pass);
		$this->host = getDef($options, ['host','hostname'], $this->host);
		$this->port = getDef($options, 'port', $this->port);
		$this->type = getDef($options, 'type', $this->type);
		$this->dsn = getDef($options, 'dsn', $this->dsn);
		$this->pdo = new PDO( 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbName . ';' . $this->dsn, $this->user, $this->pass, [
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_ALL_TABLES'",
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		]);
	}

	public function beginTransaction(){
		if(!$this->inTrans){
			$this->inTrans = true;
			return $this->pdo->beginTransaction();
		}else{
			throw 'already in transaction';
		}
	}

	public function commit(){
		$this->inTrans = false;
		return $this->pdo->commit();
	}
	
	public function rollback(){
		$this->inTrans = false;
		return $this->pdo->rollback();
	}
	
	function exec($sql, $params = NULL){
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return $stmt;
	}

}

?>