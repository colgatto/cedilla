<?php

namespace cedilla;

class RouteHook{
	public function __construct(){
		$this->options = [
			'require' => [],
			'check' => [],
			'priority' => 0,
			'db' => false,
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
	public function priority($value){
		$this->options['priority'] = $value;
		return $this;
	}
	public function db($value){
		$this->options['db'] = $value;
		return $this;
	}
}

?>