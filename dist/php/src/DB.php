<?php

namespace cedilla;

use \PDO;
use \PDOStatement;
use \Exception;

class DB {

	const DB_MYSQL = 'Mysql';
	const MYSQL_DEFAULT_PORT = 3306;
	const DB_ORACLE = 'Oracle';
	const ORACLE_DEFAULT_PORT = 1521;
	const DB_POSTGRESS = 'Postgres';
	const POSTGRESS_DEFAULT_PORT = 5432;

	const DB_SQLITE = 'Sqlite';
	const DB_MSSQL = 'Sqlserver';
	const MSSQL_DEFAULT_PORT = 1433;
	const DB_DB2 = 'Db2';
	const DB2_DEFAULT_PORT = 50000;
	const DB_FIREBIRD = 'Firebird';
	const FIREBIRD_DEFAULT_PORT = 3050;

	private string | false $dbName;
	private string $user;
	private string $pass;
	private string $host;
	private ?int $port;
	private string $type;
	private string $dsn;
	private ?string $connectionString;
	private ?string $path;
	private bool $inTrans;
	public ?PDO $pdo;
	private PDOStatement $lastStmt;
	public bool $enableLog;

	public function __construct( $options ) {

		$this->type = getDef( $options, 'type', DB::DB_MYSQL );
		$this->host = getDef( $options, ['host', 'hostname'], '127.0.0.1' );
		$this->port = getDef( $options, 'port', null );
		$this->user = getDef( $options, ['user', 'username'], 'root' );
		$this->pass = getDef( $options, ['pass', 'password'], '' );
		$this->dbName = getDef( $options, ['db', 'database'], false );

		//$this->dsn = getDef( $options, 'dsn', 'charset=utf8' );
		$this->dsn = getDef( $options, 'dsn', '' );

		$this->connectionString = getDef( $options, ['connectionString', 'connectionstring'], null );
		$this->path = getDef( $options, 'path', null );

		$this->enableLog = getDef( $options, ['log', 'enableLog'], false );
		$this->inTrans = false;
		$this->pdo = null;
	}

	public function connect( $options = [] ): PDO{
		$this->type = getDef( $options, 'type', $this->type );
		$this->host = getDef( $options, ['host', 'hostname'], $this->host );
		$this->port = getDef( $options, 'port', $this->port );
		$this->user = getDef( $options, ['user', 'username'], $this->user );
		$this->pass = getDef( $options, ['pass', 'password'], $this->pass );
		$this->dbName = getDef( $options, ['db', 'database'], $this->dbName );
		$this->dsn = getDef( $options, 'dsn', $this->dsn );

		$this->connectionString = getDef( $options, ['connectionString', 'connectionstring'], $this->connectionString );
		$this->path = getDef( $options, 'path', $this->path );

		switch ($this->type) {
			case DB::DB_MYSQL:
				if(!$this->port) $this->port = DB::MYSQL_DEFAULT_PORT;
				if($this->connectionString){
					$connString = $this->connectionString;
				}elseif($this->dbName){
					$connString = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};{$this->dsn}";
				}else{
					$connString = "mysql:host={$this->host};port={$this->port};{$this->dsn}";
				}
				$this->pdo = new PDO( $connString, $this->user, $this->pass, [
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_ALL_TABLES'",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				]);
				break;
			case DB::DB_SQLITE:
				$this->pdo = new PDO("sqlite:{$this->dbName}");
				break;
			case DB::DB_MSSQL:
				if(!$this->port) $this->port = DB::MSSQL_DEFAULT_PORT;
				if($this->connectionString){
					$connString = $this->connectionString;
				}elseif($this->dbName){
					$connString = "sqlsrv:Server={$this->host}, {$this->port};Database={$this->dbName};{$this->dsn}";
				}else{
					$connString = "sqlsrv:Server={$this->host}, {$this->port};{$this->dsn}";
				}
				$this->pdo = new PDO( $connString, $this->user, $this->pass, [
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				]);
				break;
			case DB::DB_POSTGRESS:
				if(!$this->port) $this->port = DB::POSTGRESS_DEFAULT_PORT;
				if($this->connectionString){
					$connString = $this->connectionString;
				}elseif($this->dbName){
					$connString = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbName};{$this->dsn}";
				}else{
					$connString = "pgsql:host={$this->host};port={$this->port};{$this->dsn}";
				}
				$this->pdo = new PDO( $connString, $this->user, $this->pass, [
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				]);
				break;
			case DB::DB_ORACLE:
				if(!$this->port) $this->port = DB::ORACLE_DEFAULT_PORT;
				if($this->connectionString){
					$connString = $this->connectionString;
				}else{
					$connString = "oci:dbname=(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = {$this->host})(PORT = {$this->port})) (CONNECT_DATA = (SERVICE_NAME = orcl) (SID = orcl)));{$this->dsn}";
				}
				$this->pdo = new PDO($connString, $this->user, $this->pass, [
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				]);
				break;
			default:
				throw new Exception( 'Unknown database type: ' . $this->type );
		}

		return $this->pdo;
	}

	public function beginTransaction(): bool{
		if ( !$this->inTrans ) {
			$this->inTrans = true;
			return $this->pdo->beginTransaction();
		} else {
			throw new Exception( 'already in transaction' );
		}
	}

	public function commit(): bool{
		$this->inTrans = false;
		return $this->pdo->commit();
	}

	public function rollback(): bool{
		$this->inTrans = false;
		return $this->pdo->rollback();
	}

	public function exec(string $sql, ?array $params = null, ?bool $enableLog = null ): PDOStatement | false{
		$this->lastStmt = $this->pdo->prepare( $sql );
		$this->lastStmt->execute( $params );
		return $this->lastStmt;
	}
	public function name(): string{
		return $this->dbName;
	}

	public function dbType(): string{
		return $this->type;
	}

	public function lastPk(): string | false{
		return $this->pdo->lastInsertId();
	}

	public function closeCursor(): bool{
		return $this->lastStmt->closeCursor();
	}

	public function stored($name, ?array $params = null, ?bool $enableLog = null ): PDOStatement | false{
		switch ($this->type) {
			case DB::DB_POSTGRESS:
			case DB::DB_MYSQL:
				if(is_null($params)){
					return $this->exec("CALL $name()", null, $enableLog);
				}
				$pStr = [];
				$pVal = [];
				if(array_is_list($params)){
					for ($i=0, $l = count($params); $i < $l; $i++) { 
						array_push($pStr, ":param_$i");
						$pVal[":param_$i"] = $params[$i];
					}
				}else{
					$i = 0;
					foreach ($params as $value) {
						array_push($pStr, ":$i");
						$pVal[":$i"] = $value;
						$i++;
					}
				}
				return $this->exec("CALL $name(" . implode(',', $pStr) . ")", $pVal, $enableLog);

			case DB::DB_ORACLE:
			case DB::DB_MSSQL:
				if(is_null($params)){
					return $this->exec("EXECUTE $name", null, $enableLog);
				}
				$pStr = [];
				$pVal = [];
				foreach ($params as $key => $value) {
					array_push($pStr, "@$key = :$key");
					$pVal[":$key"] = $value;
				}
				return $this->exec("EXECUTE $name " . implode(',', $pStr), $pVal, $enableLog);
			default:
				throw new Exception('Invalid DB type');
		}
	}
}
