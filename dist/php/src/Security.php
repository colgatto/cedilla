<?php

namespace cedilla;

class Security {

	public static function checkCRSF(): bool{
		$headToken = self::getCRSFHeader();
		$sessionToken = self::getCSRF();
		return !is_null($headToken) && !empty($headToken) && !is_null($sessionToken) && !empty($sessionToken) && $headToken == $sessionToken;
	}

	public static function newCSRF(bool $force = false): string{
		if(!$force){
			$sessionToken = self::getCSRF();
			if(!is_null($sessionToken) && !empty($sessionToken)) return $sessionToken;
		}
		$_SESSION['__cedilla']['CSRFtoken'] = hash( 'sha512', bin2hex( openssl_random_pseudo_bytes( 64 ) ) );
		return $_SESSION['__cedilla']['CSRFtoken'];
	}

	public static function CSRFTag(): void{
		$sessionToken = self::getCSRF();
		if(!is_null($sessionToken) && !empty($sessionToken)){
			echo "<script>ç.api.default.CSRFToken = '$sessionToken';</script>";
		}
	}

	public static function getCSRF(): ?string{
		if(!isset($_SESSION['__cedilla']) || !isset($_SESSION['__cedilla']['CSRFtoken'])) return null;
		return $_SESSION['__cedilla']['CSRFtoken'];
	}

	public static function deleteCSRF(): void{
		if(!isset($_SESSION['__cedilla']) || !isset($_SESSION['__cedilla']['CSRFtoken'])) return;
		unset($_SESSION['__cedilla']['CSRFtoken']);
	}

	private static function getCRSFHeader(): ?string{
		foreach ( getallheaders() as $k => $v ) {
			if (preg_match( "/^csrftoken$/i", $k )) return $v;
		}
		return null;
	}
}
