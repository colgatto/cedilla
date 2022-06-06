<?php

namespace cedilla;

require_once __DIR__ . '/Route.php';
require_once __DIR__ . '/Error.php';

class Api{
	
	function __construct($options = []){
		$this->tstart = microtime(true);
		$this->routes = [];
		$this->response = null;
		$this->db = new DB( getDef($options, 'db', []) );
		//$this->_current_route_name = $name;
		$this->_current_route_data = [
			'require' => [],
			'check' => [],
			'priority' => 0,
			'db' => false
		];
	}

	public function route($name){
		$this->_current_route_name = $name;
		$this->_current_route_data = [
			'require' => [],
			'check' => [],
			'priority' => 0,
			'db' => false
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
		
		if(count($trigg) == 0) $this->response->error("Route '$value' is not valid", Error::ROUTE_INVALID);
		
		usort($trigg, function ($a, $b){
			if($a->priority > $b->priority) return -1; 
			if($a->priority < $b->priority) return 1; 
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
		$route->validateRequire();
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