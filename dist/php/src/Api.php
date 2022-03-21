<?php

namespace cedilla;

require_once __DIR__ . '/Error.php';

class Api{
	
	function __construct($options = []){
		$this->tstart = microtime(true);
		$this->routes = [];
		$this->dataset = [];
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
		$this->routes[$name] = is_null($cb) ? [
			'options' => [],
			'cb' => $optionsOrCb->bindTo($this)
		] : [
			'options' => $optionsOrCb,
			'cb' => $cb->bindTo($this)
		];
		return $this;
	}

	private function parseRequire($options){
		if(!isset($options['require'])) return [];
		$args = [];
		$obj = $options['require'];
		$data = file_get_contents('php://input');
		$data = empty($data) ? [] : json_decode($data, true);
		foreach ($obj as $key => $v) {
			if(!isset($data[$key])){
				$this->response->error("Parameter '$key' is required", Error::PARAM_REQUIRED);
				continue;
			}
			$vv = $data[$key];
			if(is_bool($v)) {
				if(!$v){
					$this->response->error("Parameter '$key' is not required", Error::PARAM_NOT_REQUIRED);
				}
			}elseif(is_array($v) && !in_array($vv, $v)){
				$this->response->error("Parameter '$key' is not valid", Error::PARAM_INVALID);
			}elseif(is_callable($v) && !$v($vv)){
				$this->response->error("Parameter '$key' is not valid", Error::PARAM_INVALID);
			}elseif(is_string($v)){
				switch ($v) {
					case 'string':
						$vv = strval($vv);
						break;
					case 'int':
						$vv = intval($vv);
						break;
					case 'float':
						$vv = floatval($vv);
						break;
				}
			}
			$args[$key] = $vv;
		}
		return $args;
	}

	private function parseCheck($options){
		if(isset($options['check'])){
			foreach ($options['check'] as $name => $cb) {
				if(!$cb()){
					$this->response->error("Check '$name' not passed", Error::CHECK_NOT_PASS);
				}
			}
		}
	}

	private function isRegex($pattern){
		return @preg_match($pattern, null) !== false;
	}

	private function findPossibleRoute($value){
		foreach ($this->routes as $finder => $route) {
			if( is_array($finder) && in_array($value, $finder) ) return $route;
			if( $this->isRegex($finder) && preg_match($finder, $value, $this->dataset) ) return $route;
			if( $finder == $value ) return $route;
		}
		$this->response->error("Route '$value' is not valid", Error::ROUTE_INVALID);
	}

	public function server(){
		
		$this->response = new Response($this->tstart);

		if(!isset($_GET['_cedilla_route'])){
			$this->response->error("Must define route", Error::ROUTE_UNDEFINED);
		}

		$route = $this->findPossibleRoute( $_GET['_cedilla_route'] );

		$options = $route['options'];
		$cb = $route['cb'];

		if(is_callable($options)){
			$hook = new RouteHook();
			$options($hook);
			$options = $hook->options;
		}

		$args = $this->parseRequire($options);

		$this->parseCheck($options);

		$this->response->done( $cb($args, $this->dataset) );

	}
}

?>