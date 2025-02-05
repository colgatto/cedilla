<?php

namespace cedilla;

class Route{
	
	private $api;
	private $matcher;
	private $dataset;
	private $args;
	private $cb;
	private $require;
	private $optional;
	private $check;
	private $csrf;
	private $priority;

	public $db;

	function __construct($api, $matcher, $optionsOrCb, $cb = null){
		$this->api = $api;
		$this->matcher = $matcher;
		$this->dataset = [];
		$this->args = [];

		if(is_null($cb)){
			$options = [];
			$cb = $optionsOrCb;
		}else{
			$options = $optionsOrCb;
		}

		$this->cb = $cb;
		$this->require = getDef($options, 'require', []);
		$this->optional = getDef($options, 'optional', []);
		$this->check = getDef($options, 'check', []);
		$this->priority = getDef($options, 'priority', 0);
		$this->csrf = getDef($options, 'csrf', false);
		$this->db = getDef($options, 'db', false);
	}

	public function getMatcher(){
		return $this->matcher;
	}
	public function getRequire(){
		return $this->require;
	}
	public function getOptional(){
		return $this->optional;
	}
	public function getCheck(){
		return $this->check;
	}
	public function getPriority(){
		return $this->priority;
	}
	public function getCSRF(){
		return $this->csrf;
	}

	public function overrideArgs($data){
		$this->args = $data;
	}

	public function isTriggered($value){
		if( is_array($this->matcher) && in_array($value, $this->matcher) ) return true;
		if( $this->isRegex($this->matcher) && preg_match($this->matcher, $value, $this->dataset)) return true;
		if( $this->matcher == $value ) return true;
	}
	
	public function validateCheck(){
		foreach ($this->check as $name => $cb) {
			if(!$cb($this->args)){
				$this->api->response->error("Check '$name' not passed", Error::CHECK_NOT_PASS, $name);
			}
		}
	}
	
	private function parseValue($key, $v, $vv){
		if(is_bool($v)) {
			if(!$v){
				$this->api->response->error("Parameter '$key' is not required", Error::PARAM_NOT_REQUIRED, $key);
			}
		}elseif(is_array($v) && !in_array($vv, $v)){
			$this->api->response->error("Parameter '$key' is not valid", Error::PARAM_INVALID, $key);
		}elseif(is_callable($v) && !$v($vv)){
			$this->api->response->error("Parameter '$key' is not valid", Error::PARAM_INVALID, $key);
		}elseif(is_string($v)){
			switch ($v) {
				case 'bool': return boolval($vv);
				case 'string': return strval($vv);
				case 'int': return intval($vv);
				case 'float': return floatval($vv);
				case 'raw': return $vv;
			}
		}
		return $vv;
	}

	public function appendOptional($data){
		foreach ($this->optional as $key => $v) {
			if(!isset($data[$key])) {
				//isset() non conta null come valore
				if(!array_key_exists('default', $v)) continue;
				$data[$key] = $v['default'];
			}
			$this->args[$key] = $this->parseValue($key, $v['val'], $data[$key]);
		}
	}

	public function appendRequire($data){
		foreach ($this->require as $key => $v) {
			if(!isset($data[$key])) $this->api->response->error("Parameter '$key' is required", Error::PARAM_REQUIRED, $key);
			$this->args[$key] = $this->parseValue($key, $v, $data[$key]);
		}
		return $this->args;
	}

	public function exec(){
		return $this->cb->bindTo($this->api)($this->args, $this->dataset);
	}
	
	private function isRegex($pattern){
		return is_string($pattern) && (@preg_match($pattern, null) !== false);
	}

}


?>