<?php

namespace cedilla;

require_once __DIR__ . '/RouteHook.php';

class Route{
	
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
			$cb = $cb;
		}

		if(is_callable($options)){
			$hook = new RouteHook();
			$options($hook);
			$options = $hook->options;
		}

		$this->cb = $cb;
		$this->require = getDef($options, 'require', []);
		$this->check = getDef($options, 'check', []);
		$this->priority = getDef($options, 'priority', 0);
		$this->db = getDef($options, 'db', false);
	}

	public function isTriggered($value){
		if( is_array($this->matcher) && in_array($value, $this->matcher) ) return true;
		if( $this->isRegex($this->matcher) && preg_match($this->matcher, $value, $this->dataset)) return true;
		if( $this->matcher == $value ) return true;
	}
	
	public function validateCheck(){
		foreach ($this->check as $name => $cb) {
			if(!$cb()){
				$this->api->response->error("Check '$name' not passed", Error::CHECK_NOT_PASS);
			}
		}
	}
	
	public function validateRequire(){
		$data = file_get_contents('php://input');
		$data = empty($data) ? [] : json_decode($data, true);
		foreach ($this->require as $key => $v) {
			if(!isset($data[$key])){
				$this->api->response->error("Parameter '$key' is required", Error::PARAM_REQUIRED);
				continue;
			}
			$vv = $data[$key];
			if(is_bool($v)) {
				if(!$v){
					$this->api->response->error("Parameter '$key' is not required", Error::PARAM_NOT_REQUIRED);
				}
			}elseif(is_array($v) && !in_array($vv, $v)){
				$this->api->response->error("Parameter '$key' is not valid", Error::PARAM_INVALID);
			}elseif(is_callable($v) && !$v($vv)){
				$this->api->response->error("Parameter '$key' is not valid", Error::PARAM_INVALID);
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
			$this->args[$key] = $vv;
		}
		return $this->args;
	}

	public function exec(){
		return $this->cb->bindTo($this->api)($this->args, $this->dataset);
	}
	
	private function isRegex($pattern){
		return @preg_match($pattern, null) !== false;
	}

}


?>