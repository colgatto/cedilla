<?php

namespace cedilla;

class RouteHook{
	public function __construct(){
		$this->options = [
			'require' => [],
			'check' => [],
			'priority' => 1
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
}

?>