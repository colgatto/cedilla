<?php

namespace cedilla;

class Error{

	const ROUTE_UNDEFINED = 'ROUTE_UNDEFINED';
	const ROUTE_INVALID = 'ROUTE_INVALID';
	const CHECK_NOT_PASS = 'CHECK_NOT_PASS';
	const PARAM_REQUIRED = 'PARAM_REQUIRED';
	const PARAM_NOT_REQUIRED = 'PARAM_NOT_REQUIRED';
	const PARAM_INVALID = 'PARAM_INVALID';
	const INTERNAL_ERROR = 'INTERNAL_ERROR';

	public $code;
	public $type;
	public $message;

	public function __construct( $message = '', $type = Error::INTERNAL_ERROR, $code = null ){
		$this->message = $message;
		$this->type = $type;
		$this->code = $code;
	}

}

?>