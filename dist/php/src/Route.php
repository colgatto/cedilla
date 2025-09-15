<?php

namespace cedilla;

class Route{
	
	private Api $api;
	private string | array $matcher;
	private array $dataset;
	private array $args;
	private $cb;
	private array $require;
	private array $optional;
	private array $check;
	private string | bool $csrf;
	private int $priority;

	public $db;

	function __construct(Api $api, string | array $matcher, array | callable $optionsOrCb, ?callable $cb = null){
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

	public function getMatcher(): string | array{
		return $this->matcher;
	}
	public function getRequire(): array{
		return $this->require;
	}
	public function getOptional(): array{
		return $this->optional;
	}
	public function getCheck(): array{
		return $this->check;
	}
	public function getPriority(): int{
		return $this->priority;
	}
	public function getCSRF(): bool{
		return $this->csrf;
	}

	public function overrideArgs(array $data): void{
		$this->args = $data;
	}

	public function isTriggered(string $value): bool{
		if( is_array($this->matcher) && in_array($value, $this->matcher) ) return true;
		if( is_string($this->matcher) && $this->isRegex($this->matcher) && preg_match($this->matcher, $value, $this->dataset)) return true;
		if( $this->matcher == $value ) return true;
		return false;
	}
	
	public function validateCheck(): void{
		$fullCheck = array_merge($this->api->globalCheck, $this->check);
		foreach ($fullCheck as $name => $cb) {
			if(is_bool($cb)){
				if(!$cb) $this->api->response->error("Check '$name' not passed", Error::CHECK_NOT_PASS, $name);
			}elseif(!$cb($this->args)){
				$this->api->response->error("Check '$name' not passed", Error::CHECK_NOT_PASS, $name);
			}
		}
	}
	
	private function parseValue(string $key, mixed $v, mixed $vv): mixed{
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

	public function appendOptional(array $data) : void{
		foreach ($this->optional as $key => $v) {
			if(!isset($data[$key])) {
				//isset() non conta null come valore
				if(!array_key_exists('default', $v)) continue;
				$data[$key] = $v['default'];
			}
			$this->args[$key] = $this->parseValue($key, $v['val'], $data[$key]);
		}
	}

	public function appendRequire(array $data): void{
		foreach ($this->require as $key => $v) {
			if(!isset($data[$key])) $this->api->response->error("Parameter '$key' is required", Error::PARAM_REQUIRED, $key);
			$this->args[$key] = $this->parseValue($key, $v, $data[$key]);
		}
	}

	public function exec(): mixed{
		return $this->cb->bindTo($this->api)($this->args, $this->dataset);
	}
	
	private function isRegex(string $pattern): bool{
		if($this->api->debug){
			set_error_handler(function(){ return true; });
			$res = @preg_match($pattern, '') !== false;
			set_error_handler($this->api->_error_handler);
		}else{
			$res = @preg_match($pattern, '') !== false;
		}
		return $res;
	}

}

?>