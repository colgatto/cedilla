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

	public null | int | string $code;
	public string $type;
	public string $message;

	public function __construct(string $message = '', string $type = Error::INTERNAL_ERROR, null | int | string $code = null ){
		$this->message = $message;
		$this->type = $type;
		$this->code = $code;
	}

}

?>