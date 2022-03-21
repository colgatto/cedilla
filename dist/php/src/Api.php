<?php

namespace cedilla;

require_once __DIR__ . '/Route.php';
require_once __DIR__ . '/Error.php';

class Api{
	
	function __construct($options = []){
		$this->tstart = microtime(true);
		$this->routes = [];
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

		$this->response->done( $route->exec() );
	}
}

?>