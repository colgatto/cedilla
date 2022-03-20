<?php

namespace cedilla;

class RouteHook{
	public function __construct(){
		$this->options = [
			'require' => [],
			'check' => []
		];
	}
	public function require($key, $val){
		$this->options['require'][$key] = $val;
		return $this;
	}
	public function check($name, $cb){
		$this->options['check'][$name] = $cb;
		return $this;
	}
}

?>