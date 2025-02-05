<?php

namespace cedilla;

require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Route.php';
require_once __DIR__ . '/Error.php';

class Api{

	private $tstart;
	private $routes;
	private $debug;
	private $_current_route_data;
	private $_current_route_name;
	private $enableCSRF;
	public $response;
	public $db;
	
	function __construct($options = []){
		$this->tstart = microtime(true);
		$this->routes = [];
		$this->response = null;
		$this->db = new DB( getDef($options, 'db', []) );
		$this->debug = getDef($options, 'debug', false);
		$this->enableCSRF = getDef($options, 'csrf', false);
		$this->_current_route_data = null;
	}

	public function getRoutes(){
		return $this->routes;
	}
	
	public function route($name){
		$this->_current_route_name = $name;
		$this->_current_route_data = [
			'optional' => [],
			'require' => [],
			'check' => [],
			'priority' => 0,
			'csrf' => $this->enableCSRF,
			'db' => false
		];
		return $this;
	}

	public function optional($key, $val, $default = null){
		$args = func_get_args();
		$this->_current_route_data['optional'][$key] = count($args) == 2 ? [
			'val' => $val
		] : [
			'val' => $val,
			'default' => $default
		];
		return $this;
	}
	public function require($key, $val){
		$this->_current_route_data['require'][$key] = $val;
		return $this;
	}
	public function check($name, $cb){
		$this->_current_route_data['check'][$name] = $cb;
		return $this;
	}
	public function priority($value){
		$this->_current_route_data['priority'] = $value;
		return $this;
	}
	public function csrf($value){
		$this->_current_route_data['csrf'] = $value;
		return $this;
	}
	public function db($value){
		$this->_current_route_data['db'] = $value;
		return $this;
	}

	public function do($cb){
		array_push($this->routes, new Route($this, $this->_current_route_name, $this->_current_route_data, $cb));
		return $this;
	}

	private function findPossibleRoute($value){
		
		$trigg = [];
		
		foreach ($this->routes as $route) {
			if( $route->isTriggered($value) ) array_push($trigg, $route );
		}
		
		if(count($trigg) == 0) $this->response->error("Route '$value' is not valid", Error::ROUTE_INVALID, $value);
		
		usort($trigg, function ($a, $b){
			if($a->getPriority() > $b->getPriority()) return -1; 
			if($a->getPriority() < $b->getPriority()) return 1; 
			return 0;
		});

		return $trigg[0];

	}

	public function server(){
		
		$this->response = new Response($this->tstart);

		if(!isset($_GET['_cedilla_route'])){
			$this->response->error("Must define route", Error::ROUTE_UNDEFINED);
		}
		
		$route = $this->findPossibleRoute( $_GET['_cedilla_route'] );

		if($route->getCSRF() && !Security::checkCRSF()){
			$this->response->error("CSRF not passed", Error::INTERNAL_ERROR, 'csrf');
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