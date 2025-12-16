<?php

namespace cedilla;
require_once __DIR__ . '/Session.php';

class Security {

	public static function checkToken(): bool{
		$headToken = self::getTokenHeader();
		return !is_null($headToken) && !empty($headToken) && defined('API_TOKEN') && !is_null(API_TOKEN) && !empty(API_TOKEN) && $headToken == API_TOKEN;
	}

	public static function checkCRSF(): bool{
		$headToken = self::getCRSFHeader();
		$sessionToken = self::getCSRF();
		return !is_null($headToken) && !empty($headToken) && !is_null($sessionToken) && !empty($sessionToken) && $headToken === $sessionToken;
	}

	public static function newCSRF(bool $force = false): string{
		if(!$force){
			$sessionToken = self::getCSRF();
			if(!is_null($sessionToken) && !empty($sessionToken)) return $sessionToken;
		}
		Session::set(['__cedilla', 'CSRFtoken'],  hash( 'sha512', bin2hex( openssl_random_pseudo_bytes( 64 ) ) ));
		return Session::get(['__cedilla', 'CSRFtoken']);
	}

	public static function CSRFTag(): void{
		$sessionToken = self::getCSRF();
		if(!is_null($sessionToken) && !empty($sessionToken)){
			echo "<script>ç.api.default.CSRFToken = '$sessionToken';</script>";
		}
	}

	public static function getCSRF(): ?string{
		if(!Session::isset('__cedilla') || !Session::isset(['__cedilla', 'CSRFtoken'])) return null;
		return Session::get(['__cedilla', 'CSRFtoken']);
	}

	public static function deleteCSRF(): void{
		if(!Session::isset('__cedilla') || !Session::isset(['__cedilla', 'CSRFtoken'])) return;
		Session::unset(['__cedilla', 'CSRFtoken']);
	}

	private static function getCRSFHeader(): ?string{
		foreach ( getallheaders() as $k => $v ) {
			if (preg_match( "/^csrftoken$/i", $k )) return $v;
		}
		return null;
	}

	private static function getTokenHeader(): ?string{
		foreach ( getallheaders() as $k => $v ) {
			if (preg_match( "/^apitoken$/i", $k )) return $v;
		}
		return null;
	}
}
