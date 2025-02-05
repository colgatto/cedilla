<?php

namespace cedilla;

use \PDO;
use \PDOStatement;
use \Exception;

class DB {

	const DB_MYSQL = 'Mysql';
	const DB_OCI = 'Oracle';
	const DB_POSTGRESS = 'Postgres';
	const DB_SQLITE = 'Sqlite';
	const DB_MSSQL = 'Sqlserver';
	const DB_DB2 = 'Db2';
	const DB_FIREBIRD = 'Firebird';

	private $dbName;
	private $user;
	private $pass;
	private $host;
	private $port;
	private $type;
	private $dsn;
	private $inTrans;
	public $pdo;

	public function __construct( $options ) {
		$this->dbName = getDef( $options, ['db', 'database'], false );
		$this->user = getDef( $options, ['user', 'username'], 'root' );
		$this->pass = getDef( $options, ['pass', 'password'], '' );
		$this->host = getDef( $options, ['host', 'hostname'], '127.0.0.1' );
		$this->port = getDef( $options, 'port', null );
		$this->type = getDef( $options, 'type', DB::DB_MYSQL );
		$this->dsn = getDef( $options, 'dsn', 'charset=utf8' );
		$this->inTrans = false;
	}

	public function connect( $options = [] ): PDO{
		$this->dbName = getDef( $options, ['db', 'database'], $this->dbName );
		$this->user = getDef( $options, ['user', 'username'], $this->user );
		$this->pass = getDef( $options, ['pass', 'password'], $this->pass );
		$this->host = getDef( $options, ['host', 'hostname'], $this->host );
		$this->port = getDef( $options, 'port', $this->port );
		$this->type = getDef( $options, 'type', $this->type );
		$this->dsn = getDef( $options, 'dsn', $this->dsn );

		switch ($this->type) {
			case DB::DB_MYSQL:
				$this->pdo = new PDO( 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbName . ';' . $this->dsn, $this->user, $this->pass, [
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_ALL_TABLES'",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				] );
				break;
			case DB::DB_MSSQL:
				$this->pdo = new PDO( 'sqlsrv:Server=' . $this->host . ';Database=' . $this->dbName . ';', $this->user, $this->pass, [
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				] );
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

	function exec(string $sql, ?array $params = null ): PDOStatement | false{
		$stmt = $this->pdo->prepare( $sql );
		$stmt->execute( $params );
		return $stmt;
	}
	function name(): string{
		return $this->dbName;
	}

	function lastPk(): string | false{
		return $this->pdo->lastInsertId();
	}
}
