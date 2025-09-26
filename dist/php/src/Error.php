<?php

namespace cedilla;

class Error{

	const ROUTE_UNDEFINED = 'ROUTE_UNDEFINED';
	const ROUTE_INVALID = 'ROUTE_INVALID';
	const CHECK_NOT_PASS = 'CHECK_NOT_PASS';
	const PARAM_REQUIRED = 'PARAM_REQUIRED';
	const PARAM_NOT_REQUIRED = 'PARAM_NOT_REQUIRED';
	const PARAM_INVALID = 'PARAM_INVALID';

	const PDO_ERROR = 'PDO_ERROR';
	
	const INTERNAL_ERROR = 'INTERNAL_ERROR';
	const GENERIC_ERROR = 'GENERIC_ERROR';

	const FATAL_ERROR = 'FATAL_ERROR';
	const EXCEPTION_ERROR = 'EXCEPTION_ERROR';

	public null | int | string $code;
	public string $type;
	public string $message;

	public function __construct(string $message = '', int | string $code = 0, string $type = Error::GENERIC_ERROR ){
		$this->message = $message;
		$this->code = $code;
		$this->type = $type;
	}

}

?>