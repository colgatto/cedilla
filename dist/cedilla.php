<?php

namespace ç;

class Response{
	
	function __construct($tstart){
		$this->tstart = $tstart;
	}
	public function error($msg){
		header('Content-Type: application/json');
		die(json_encode([
			'error' => true,
			'message' => $msg,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function ok($data = ''){
		header('Content-Type: application/json');
		die(json_encode([
			'error' => false,
			'response' => $data,
			'time' => microtime(true) - $this->tstart
		]));
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
		//$this->errorsRequire = [];
		//$this->check = [];
		//$this->args = [];
		//$this->cb = [];
		/*
		
		controllo e apro sessione, 
		$_SESSION[
			'ç_api_stat' = 
		]

		*/
	}

	private function parseV($obj, $_GLOB, &$errors){
		$args = [];
		foreach ($obj as $key => $v) {
			if(!isset($_GLOB[$key])){
				array_push($errors, $key . ' required');
				continue;
			}
			$vv = $_GLOB[$key];
			if(is_array($v) && !in_array($vv, $v)){
				array_push($errors, $key . ' invalid');
				continue;
			}elseif(is_callable($v) && !$v($vv)){
				array_push($errors, $key . ' invalid');
				continue;
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

		$r = new Response($this->tstart);
		
		if(!isset($_POST['_action'])) $r->error('action required');
		$a = $_POST['_action'];
		if(!isset($this->routes[$a])) $r->error('action invalid');

		$options = $this->routes[$a]['options'];
		$cb = $this->routes[$a]['cb'];
		$args = [];
		
		if(isset($options['require'])){
			$errors = [];
			$req = $options['require'];
			if(isset($req['request'])){
				$args = $this->parseV($req['request'], $_REQUEST, $errors);
			}else{
				$g_args = [];
				$p_args = [];
				if(isset($req['get'])){
					$g_args = $this->parseV($req['get'], $_GET, $errors);
				}
				if(isset($req['post'])){
					$p_args = $this->parseV($req['post'], $_POST, $errors);
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
			if(count($errors) > 0){
				$r->error( 'require:' . implode(",", $errors) );
			}
		}

		if(isset($options['check'])){
			$errors = [];
			foreach ($options['check'] as $name => $cb) {
				if(!$cb()){
					array_push($errors, $name);
				}
			}
			if(count($errors) > 0){
				$r->error( 'check:' . implode(",", $errors) );
			}
		}
		
		$cb( $args, $r);

	}
	
}


?>