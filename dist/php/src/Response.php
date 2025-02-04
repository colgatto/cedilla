<?php

namespace cedilla;

class Response{
	
	private $tstart;
	
	function __construct($tstart){
		$this->tstart = $tstart;
	}

	public function done($value=''){
		header('Content-Type: application/json');
		die(json_encode([
			'error' => false,
			'response' => $value,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function error($message = '', $type = Error::INTERNAL_ERROR, $code = null){
		header('Content-Type: application/json');
		die(json_encode([
			'error' => new Error($message, $type, $code),
			'response' => false,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function redirect($location){
		header('Location: ' . $location);
		die('');
	}
	
	public function html($value=''){
		header('Content-Type: text/html');
		die($value);
	}
}

?>