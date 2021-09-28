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
		$this->response = null;
	}

	private function parseRequire($obj){
		$args = [];
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

	public function route($name, $options, $cb){
		$this->routes[$name] = [
			'options' => $options,
			'cb' => $cb
		];
		return $this;
	}

	public function server(){
		
		$this->response = new Response($this->tstart);

		if(!isset(CEDILLA_ROUTE_METHOD['_cedilla_route'])){
			$this->response->addError('A');
			$this->response->done();
		}
		$a = CEDILLA_ROUTE_METHOD['_cedilla_route'];
		if(!isset($this->routes[$a])){
			$this->response->addError('B:' . $a);
			$this->response->done();
		}
		$options = $this->routes[$a]['options'];
		$cb = $this->routes[$a]['cb'];

		$args = $this->parseRequire($options['require']);

		$this->response->dieForError();
		
		if(isset($options['check'])){
			foreach ($options['check'] as $name => $cb) {
				if(!$cb()){
					$this->response->addError('C:' . $name);
				}
			}
		}
		
		$this->response->dieForError();

		$this->response->done( $cb($args, $this->response) );

	}
	
}


?>