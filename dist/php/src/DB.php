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

	private string | false $dbName;
	private string $user;
	private string $pass;
	private string $host;
	private int $port;
	private string $type;
	private string $dsn;
	private bool $inTrans;
	private ?int $pk_user;
	public PDO $pdo;
	private PDOStatement $lastStmt;
	public bool $enableLog;

	public function __construct( $options, $pk_user = null ) {
		$this->pk_user = $pk_user;
		$this->dbName = getDef( $options, ['db', 'database'], false );
		$this->user = getDef( $options, ['user', 'username'], 'root' );
		$this->pass = getDef( $options, ['pass', 'password'], '' );
		$this->host = getDef( $options, ['host', 'hostname'], '127.0.0.1' );
		$this->port = getDef( $options, 'port', null );
		$this->type = getDef( $options, 'type', DB::DB_MYSQL );
		$this->dsn = getDef( $options, 'dsn', 'charset=utf8' );
		$this->enableLog = getDef( $options, ['log', 'enableLog'], false );
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
				if($this->dbName){
					$connString = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbName . ';' . $this->dsn;
				}else{
					$connString = 'mysql:host=' . $this->host . ';port=' . $this->port . ';' . $this->dsn;
				}
				$this->pdo = new PDO( $connString, $this->user, $this->pass, [
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_ALL_TABLES'",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				]);
				break;
			case DB::DB_MSSQL:
				if($this->dbName){
					$connString = 'sqlsrv:Server=' . $this->host . ';Database=' . $this->dbName . ';' . $this->dsn;
				}else{
					$connString = 'sqlsrv:Server=' . $this->host . ';' . $this->dsn;
				}
				$this->pdo = new PDO( $connString, $this->user, $this->pass, [
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

		if(is_null($enableLog)) $enableLog = $this->enableLog;

		if($enableLog){
			$logStmt = $this->pdo->prepare( "INSERT INTO auditing(query, params, fk_users, status) values(:query, :params, :fk_users, 0)");
			$logStmt->execute( [
				':query' => $sql,
				':params' => $params ? json_encode($params) : null,
				':fk_users' => $this->pk_user
			] );
			$logId = $this->lastPk();
			try{
				$this->lastStmt->execute( $params );
				$this->pdo->prepare( "UPDATE auditing SET status = 1 WHERE pk = :pk")->execute( [
					':pk' => $logId
				]);
			}catch(\PDOException $e){
				$this->pdo->prepare( "UPDATE auditing SET status = -1, exception = :exception WHERE pk = :pk")->execute( [
					':exception' => $e->getMessage(),
					':pk' => $logId
				]);
				throw $e;
			}
		}else{
			$this->lastStmt->execute( $params );
		}

		return $this->lastStmt;
	}
	public function name(): string{
		return $this->dbName;
	}

	public function lastPk(): string | false{
		return $this->pdo->lastInsertId();
	}

	public function closeCursor(): bool{
		return $this->lastStmt->closeCursor();
	}

}
