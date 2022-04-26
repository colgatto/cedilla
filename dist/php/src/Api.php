<?php

namespace cedilla;

require_once __DIR__ . '/Route.php';
require_once __DIR__ . '/Error.php';

function getDef($in, $keys, $def){
	if (!is_array($keys)) $keys = [$keys];
	for ($i=0; $i < count($keys); $i++) { 
		$k = explode('.', $keys[$i]);
		$inC = $in;
		$break = false;
		for ($j=0; $j < count($k); $j++) { 
			$kk = $k[$j];
			if(!isset($inC[$kk])){
				$break = true;
				break;
			}
			$inC = $inC[$kk];
		}
		if(!$break) return $inC;
	}
	return $def;
}

class Api{
	
	function __construct($options = []){
		$this->tstart = microtime(true);
		$this->routes = [];
		$this->response = null;
		if(isset($options['db'])){
			$this->db = new DB(
				$options['db']['database'],
				getDef($options, ['db.user','db.username'], 'root'),
				getDef($options, ['db.pass','db.password'], ''),
				getDef($options, ['db.host','db.hostname'], '127.0.0.1'),
				getDef($options, 'db.port', null),
				getDef($options, 'db.type', DB::DB_MYSQL),
				getDef($options, 'db.dsn', 'charset=utf8'),
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