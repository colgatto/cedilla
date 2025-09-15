<?php

namespace cedilla;

class Response{
	
	private float $tstart;
	
	function __construct(float $tstart){
		$this->tstart = $tstart;
	}

	public function done(mixed $value=''): void{
		header('Content-Type: application/json');
		die(json_encode([
			'error' => false,
			'response' => $value,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function error(string $message = '', string $type = Error::INTERNAL_ERROR, null | int | string $code = null): void{
		header('Content-Type: application/json');
		die(json_encode([
			'error' => new Error($message, $type, $code),
			'response' => false,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function redirect(string $location): void{
		header('Location: ' . $location);
		die('');
	}
	
	public function html(string $value=''): void{
		header('Content-Type: text/html');
		die($value);
	}
}

?>