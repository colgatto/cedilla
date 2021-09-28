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
	
	const METHOD = [
		'request' => 'R',
		'post' => 'P',
		'get' => 'G'
	];

	function __construct(){
		$this->tstart = microtime(true);
		$this->routes = [];
		$this->response = null;
	}

	private function parseV($action, $obj, $_GLOB){
		$args = [];
		foreach ($obj as $key => $v) {
			if(!isset($_GLOB[$key])){
				$this->response->addError('R' . Api::METHOD[$action] . ':' . $key);
				continue;
			}
			$vv = $_GLOB[$key];
			if(is_bool($v)) {
				if(!$v){
					$this->response->addError('N' . Api::METHOD[$action] . ':' . $key);
				}
			}elseif(is_array($v) && !in_array($vv, $v)){
				$this->response->addError('I' . Api::METHOD[$action] . ':' . $key);
			}elseif(is_callable($v) && !$v($vv)){
				$this->response->addError('I' . Api::METHOD[$action] . ':' . $key);
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
		$args = [];
		
		if(isset($options['require'])){
			$req = $options['require'];
			if(isset($req['request'])){
				$args = $this->parseV('request', $req['request'], $_REQUEST);
			}else{
				$g_args = [];
				$p_args = [];
				if(isset($req['get'])){
					$g_args = $this->parseV('get', $req['get'], $_GET);
				}
				if(isset($req['post'])){
					$p_args = $this->parseV('post', $req['post'], $_POST);
				}
				if(count($g_args) > 0 && count($p_args) > 0){
					$args = [
						'get' => $g_args,
						'post' => $p_args
					];
				}elseif(count($g_args) > 0){
					$args = $g_args;
				}elseif(count($p_args) > 0){
					$args = $p_args;
				}
			}
		}

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