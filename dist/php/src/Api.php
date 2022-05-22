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
	}

	public function route($name, $optionsOrCb, $cb = null){
		array_push($this->routes, new Route($this, $name, $optionsOrCb, $cb));
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