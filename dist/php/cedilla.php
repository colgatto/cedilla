<?php
require_once __DIR__ . '/src/Session.php';

use cedilla\Session;

if(!Session::isset('__cedilla')){
	Session::set(['__cedilla', 'CSRFtoken'], null);
}

function getDef(array $in, array | string $keys, mixed $def): mixed{
	if (!is_array($keys)) $keys = [$keys];
	for ($i=0; $i < count($keys); $i++) { 
		$k = explode('.', $keys[$i]);
		$inC = $in;
		$break = false;
		for ($j=0; $j < count($k); $j++) { 
			$kk = $k[$j];
			if(!isset($inC[$kk])){
				$break = true;
				break;
			}
			$inC = $inC[$kk];
		}
		if(!$break) return $inC;
	}
	return $def;
}

require_once __DIR__ . '/src/Security.php';
require_once __DIR__ . '/src/Response.php';
require_once __DIR__ . '/src/Api.php';
require_once __DIR__ . '/src/DB.php';

?>