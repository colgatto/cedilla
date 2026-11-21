<?php

namespace cedilla;

class Response{

	private float $tstart;

	function __construct(float $tstart){
		$this->tstart = $tstart;
	}

	public function done(mixed $value=''): void{
		if(CUSTOM_APACHE_LOG) apache_setenv('TEMPLATILLA_RESPONSE', 'OK');
		header('Content-Type: application/json');
		die(json_encode([
			'error' => false,
			'response' => $value,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function error(string $message = '', int | string $code = 0, string $type = Error::GENERIC_ERROR, ?string $details = null): void{
		if(CUSTOM_APACHE_LOG) apache_setenv('TEMPLATILLA_RESPONSE', "$type($code)");
		header('Content-Type: application/json');
		die(json_encode([
			'error' => new Error($message, $code, $type, $details),
			'response' => false,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function debug(mixed $value=''): void{
		if(CUSTOM_APACHE_LOG) apache_setenv('TEMPLATILLA_RESPONSE', 'DEB');
		header('Content-Type: application/json');
		die(json_encode([
			'error' => false,
			'debug' => $value,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function log(mixed $value=''): void{
		if(CUSTOM_APACHE_LOG) apache_setenv('TEMPLATILLA_RESPONSE', 'LOG');
		header('Content-Type: application/json');
		die(json_encode([
			'error' => false,
			'log' => $value,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function redirect(string $location): void{
		if(CUSTOM_APACHE_LOG) apache_setenv('TEMPLATILLA_RESPONSE', 'RED');
		header('Location: ' . $location);
		die('');
	}

	public function html(string $value=''): void{
		if(CUSTOM_APACHE_LOG) apache_setenv('TEMPLATILLA_RESPONSE', 'HTM');
		header('Content-Type: text/html');
		die($value);
	}
}

?>