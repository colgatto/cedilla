<?php

namespace cedilla;

require_once __DIR__ . '/config.php';

class Response{
	
	function __construct($tstart){
		$this->tstart = $tstart;
		$this->errors = [];
	}

	public function done($value=''){
		header('Content-Type: application/json');
		die(json_encode([
			'errors' => $this->errors,
			'response' => $value,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function addError($error){
		array_push($this->errors, $error);
	}

	public function endError($error){
		array_push($this->errors, $error);
		$this->done();
	}

	public function dieForError(){
		if(count($this->errors) > 0){
			$this->done();
		}
	}
	public function redirect($location){
		header('Location: ' . $location);
		die('');
	}
	
}

class Api{
	
	function __construct(){
		$this->tstart = microtime(true);
		$this->routes = [];
		$this->dataset = [];
		$this->response = null;
	}

	public function route($name, $optionsOrCb, $cb = null){
		$this->routes[$name] = is_null($cb) ? [
			'options' => [],
			'cb' => $optionsOrCb
		] : [
			'options' => $optionsOrCb,
			'cb' => $cb
		];
		return $this;
	}


	private function parseRequire($options){
		if(!isset($options['require'])) return [];
		$args = [];
		$obj = $options['require'];
		foreach ($obj as $key => $v) {
			if(!isset(CEDILLA_PARAMS_METHOD[$key])){
				$this->response->addError('R:' . $key);
				continue;
			}
			$vv = CEDILLA_PARAMS_METHOD[$key];
			if(is_bool($v)) {
				if(!$v){
					$this->response->addError('N:' . $key);
				}
			}elseif(is_array($v) && !in_array($vv, $v)){
				$this->response->addError('I:' . $key);
			}elseif(is_callable($v) && !$v($vv)){
				$this->response->addError('I:' . $key);
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
					$this->response->addError('C:' . $name);
				}
			}
		}
	}

	private function is_regex($pattern){
		return @preg_match($pattern, null) !== false;
	}

	private function findPossibleRoute($value){
		foreach ($this->routes as $finder => $route) {
			if( is_array($finder) && in_array($value, $finder) ) return $route;
			if( $this->is_regex($finder) && preg_match($finder, $value, $this->dataset) ) return $route;
			if( $finder == $value ) return $route;
		}
		$this->response->endError('B:' . $value);
	}

	public function server(){
		
		$this->response = new Response($this->tstart);

		if(!isset(CEDILLA_ROUTE_METHOD['_cedilla_route'])){
			$this->response->endError('A');
		}

		$route = $this->findPossibleRoute( CEDILLA_ROUTE_METHOD['_cedilla_route'] );

		$options = $route['options'];
		$cb = $route['cb'];

		$args = $this->parseRequire($options);

		$this->response->dieForError();
		
		$this->parseCheck($options);
		
		$this->response->dieForError();

		$this->response->done( $cb($args, $this->dataset, $this) );

	}
	
}

?>