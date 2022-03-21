<?php

namespace cedilla;

class Response{
	
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

	public function error($message = '', $code = Error::INTERNAL_ERROR){
		header('Content-Type: application/json');
		die(json_encode([
			'error' => new Error($message, $code),
			'response' => false,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function redirect($location){
		header('Location: ' . $location);
		die('');
	}
	
}

?>