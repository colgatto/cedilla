<?php

function getDef($in, $keys, $def){
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

require_once __DIR__ . '/src/Response.php';
require_once __DIR__ . '/src/RouteHook.php';
require_once __DIR__ . '/src/Api.php';
require_once __DIR__ . '/src/DB.php';

?>