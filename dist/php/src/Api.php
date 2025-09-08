<?php

namespace cedilla;

require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Route.php';
require_once __DIR__ . '/Error.php';

class Api{
	
	public $_error_handler;
	public array $globalCheck;
	private $_fatal_error_handler;
	private $_exception_error_handler;
	private float $tstart;
	private array $routes;
	public bool $debug;
	private array $_current_route_data;
	private string $_current_route_name;
	private bool $enableCSRF;
	private ?int $pk_user;
	public Response $response;
	public DB $db;
	
	function __construct(array $options = []){
		$this->tstart = microtime(true);
		$this->routes = [];
		$this->response = new Response($this->tstart);
		$this->pk_user = getDef($options, 'pk_user', null);
		$this->db = new DB( getDef($options, 'db', []), $this->pk_user);
		$this->debug = getDef($options, 'debug', false);
		$this->enableCSRF = getDef($options, 'csrf', false);
		$this->globalCheck = getDef($options, 'check', []);

		if($this->debug){

			$this->_error_handler = function($errno, $errstr, $errfile = null, $errline = null, $errcontext = null){
				$this->response->error( $errstr . ($errfile ? ("\n" . $errfile . ":" . $errline) : ''), $errno, Error::FATAL_ERROR);
			};

			$this->_fatal_error_handler = function(){
				$error = error_get_last();
				if ( $error ) {
					/*
					E_ERROR: 1
					E_PARSE: 4
					E_CORE_ERROR: 16
					E_COMPILE_ERROR: 64
					E_USER_ERROR: 256
					*/
					if( $error["type"] == E_ERROR || $error["type"] == E_PARSE || $error["type"] == E_CORE_ERROR || $error["type"] == E_COMPILE_ERROR || $error["type"] == E_USER_ERROR ) {
						$this->response->error( $error, $error["type"], Error::FATAL_ERROR);
					}
				}
			};

			$this->_exception_error_handler = function($e){
				$this->response->error(get_class($e) . " " . $e->getMessage() . "\n" . $e->getTraceAsString(), 0, Error::EXCEPTION_ERROR);
			};

			if(function_exists('xdebug_disable')) xdebug_disable();
			register_shutdown_function( $this->_fatal_error_handler );
			set_error_handler($this->_error_handler);
			set_exception_handler($this->_exception_error_handler);
		}

	}
	public function getRoutes(): array{
		return $this->routes;
	}

	public function route(string | array $name): Api{
		$this->_current_route_name = $name;
		$this->_current_route_data = [
			'optional' => [],
			'require' => [],
			'check' => [],
			'priority' => 0,
			'csrf' => $this->enableCSRF,
			'filepath' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'],
			'db' => false
		];
		return $this;
	}

	public function optional(string $key, string $val, mixed $default = null): Api{
		$args = func_get_args();
		$this->_current_route_data['optional'][$key] = count($args) == 2 ? [
			'val' => $val
		] : [
			'val' => $val,
			'default' => $default
		];
		return $this;
	}
	public function require(string $key, string | array $val): Api{
		$this->_current_route_data['require'][$key] = $val;
		return $this;
	}
	public function check(string $name, callable | bool $cb): Api{
		$this->_current_route_data['check'][$name] = $cb;
		return $this;
	}
	public function priority(int $value): Api{
		$this->_current_route_data['priority'] = $value;
		return $this;
	}
	public function csrf(bool $value): Api{
		$this->_current_route_data['csrf'] = $value;
		return $this;
	}
	public function db(bool | array $value): Api{
		$this->_current_route_data['db'] = $value;
		return $this;
	}

	public function do(callable $cb): Api{
		array_push($this->routes, new Route($this, $this->_current_route_name, $this->_current_route_data, $cb));
		return $this;
	}

	private function findPossibleRoute(string $value): Route{

		$expV = explode(':', $value);
		$last_value = array_pop($expV);
		if(count($expV) > 0) {

			preg_match('/(\\\\|\/)[^\\\\\/]+$/', $_SERVER['SCRIPT_FILENAME'], $matches, PREG_OFFSET_CAPTURE);
			$routeLocation = substr($_SERVER['SCRIPT_FILENAME'], 0, $matches[1][1]+1 ) . 'routes';

			for ($i=0, $l=count($expV); $i < $l; $i++) {
				if(!preg_match('/^[a-zA-Z0-9_-]+$/', $expV[$i])) $this->response->error("Route '$value' is not valid", $value, Error::ROUTE_INVALID);
				$routeLocation .= '/' . $expV[$i];
			}
			$api = $this;
			require_once $routeLocation . '.php';
		}

		$trigg = [];
		foreach ($this->routes as $route) {
			if( $route->isTriggered($last_value) ) array_push($trigg, $route );
		}
		
		if(count($trigg) == 0) $this->response->error("Route '$value' is not valid", $value, Error::ROUTE_INVALID);
		
		usort($trigg, function ($a, $b){
			if($a->getPriority() > $b->getPriority()) return -1; 
			if($a->getPriority() < $b->getPriority()) return 1; 
			return 0;
		});

		return $trigg[0];

	}

	public function server(): void{
		
		if(!isset($_GET['_cedilla_route'])){
			$this->response->error("Must define route", 0, Error::ROUTE_UNDEFINED);
		}

		$route = $this->findPossibleRoute( $_GET['_cedilla_route'] );

		if($route->getCSRF() && !Security::checkCRSF()){
			$this->response->error("CSRF not passed", 'csrf', Error::INTERNAL_ERROR);
		}

		$raw = isset($_GET['_raw']);

		if(isset($_GET['_get_params']) && $_GET['_get_params']){
			$params = $_GET;
		}else{
			$params = file_get_contents('php://input');
			if(!$raw)
				$params = empty($params) ? [] : json_decode($params, true);
		}

		if($raw) {
			$route->overrideArgs($params);
		}else{
			$route->appendRequire($params);
			$route->appendOptional($params);
		}
		$route->validateCheck();

		if(is_array($route->db)){
			$this->db->connect($route->db);
		}elseif($route->db){
			$this->db->connect();
		}

		$this->response->done( $route->exec() );
	}
}

?>