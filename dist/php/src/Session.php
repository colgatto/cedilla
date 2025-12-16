<?php

namespace cedilla;

class Session{

	private static function start(): void{
		if(session_status() == PHP_SESSION_NONE || session_status() == PHP_SESSION_DISABLED){
			session_start();
		}
	}

	public static function set(string|array $k, mixed $v): void{
		if(!is_array($k)) $k = [$k];
		Session::start();
		$o = $_SESSION;
		$oo = &$o;
		$l = count($k);
		for ($i = 0; $i < $l-1; $i++) {
			$kk = $k[$i];
			if(!isset($oo[$kk])) $oo[$kk] = [];
			$oo = &$oo[$kk];
		}
		$oo[$k[$l-1]] = $v;
		$_SESSION = $o;
		session_write_close();
	}

	public static function get(string|array $k): mixed{
		if(!is_array($k)) $k = [$k];
		Session::start();
		$o = $_SESSION;
		for ($i = 0, $l = count($k); $i < $l; $i++) { 
			$o = &$o[$k[$i]];
		}
		session_write_close();
		return $o;
	}

	public static function isset(string|array $k): bool{
		if(!is_array($k)) $k = [$k];
		Session::start();
		$o = $_SESSION;
		$l = count($k);
		for ($i = 0; $i < $l-1; $i++) {
			$o = &$o[$k[$i]];
		}
		$is = isset($o[$k[$l-1]]);
		session_write_close();
		return $is;
	}
	
	public static function unset(string|array $k): void{
		if(!is_array($k)) $k = [$k];
		Session::start();
		$o = $_SESSION;
		$oo = &$o;
		$l = count($k);
		for ($i = 0; $i < $l-1; $i++) {
			$kk = $k[$i];
			if(!isset($oo[$kk])) $oo[$kk] = [];
			$oo = &$oo[$kk];
		}
		unset($oo[$k[$l-1]]);
		$_SESSION = $o;
		session_write_close();
	}

}

?>