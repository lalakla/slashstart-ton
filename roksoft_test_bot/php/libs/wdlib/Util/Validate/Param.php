<?php

namespace WDLIB;

class Util_Validate_Param {

	const IMAGE_SRC_REGEXP = "/^http[s]{0,1}:\/\//i";

	static public function getUrl($var) : string
	{
		$var = (string)$var;
		$var = preg_replace_callback("/['\"()\\s\\t\\r\\n]+/", function($matches) {
			return urlencode($matches[0]);
		}, $var);

		return $var;
	}

	static public function getImgUrl($var) : string
	{
		$var = self::getUrl($var);
		$var = preg_match(self::IMAGE_SRC_REGEXP, $var) ? $var : "";

		return $var;
	}

	static public function getNum($var, int $sub = 0)
	{
		$var = trim($var);
		if($sub) {
			$var = mb_substr($var, 0, $sub);
		}
		if(!preg_match("/^[0-9]+$/", $var)) {
			$var = null;
		}

		return $var;
	}
	static public function getStr($var, int $sub = 0) : string
	{
		$var = (string)$var;
		$var = trim($var);
		if($sub) {
			$var = mb_substr($var, 0, $sub);
		}
		return $var;
	}
}
