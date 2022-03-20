<?php

namespace cedilla;

class Response{
	
	function __construct($tstart){
		$this->tstart = $tstart;
		$this->errors = [];
	}

	public function done($value=''){
		header('Content-Type: application/json');
		die(json_encode([
			'errors' => $this->errors,
			'response' => $value,
			'time' => microtime(true) - $this->tstart
		]));
	}

	public function addError($error){
		array_push($this->errors, $error);
	}

	public function endError($error){
		array_push($this->errors, $error);
		$this->done();
	}

	public function dieForError(){
		if(count($this->errors) > 0){
			$this->done();
		}
	}
	
	public function redirect($location){
		header('Location: ' . $location);
		die('');
	}
	
}

?>