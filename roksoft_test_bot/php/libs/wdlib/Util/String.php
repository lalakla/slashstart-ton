<?php

namespace WDLIB;

class Util_String {

	const URL_REGEXP = "/(https?|ftp):\/\/([\w\.-]+)([\/?#]?[^\s]*)/i";
	const GENDER_PATTERN = "/%gender\(([^,]+),([^\)]+)\)%/";

	static public function base64URLencode(string $str) : string
	{
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}


	static public function parseLinksToA(string $str) : string
	{
		return preg_replace_callback(self::URL_REGEXP, function($match) {
			$url = $match[0];
			$value = "{$match[1]}://{$match[2]}";
			if($match[3]) {
				$value .= mb_substr($match[3], 0, 8) . "...";
			}

			return "<a href=\"{$url}\" target=\"_blank\" rel=\"nofollow\">{$value}</a>";
		}, $str);
	}

	static public function _plural(int $n, string $s1, string $s2, string $s3) : string
	{
		$m = $n % 10;

		if ((($n % 100) > 10) && (($n % 100) < 20)) {
			return $s3;
		}
		else if($m == 1) {
			return $s1;
		}
		else if($m > 1 && $m < 5) {
			return $s2;
		}

		return $s3;
	}
	static public function plural(int $n, string $s1, string $s2, string $s3) : string
	{
		return "$n ".self::_plural($n, $s1, $s2, $s3);
	}

	static public function gender(int $sex, string $m, string $f) : string
	{
		return ($sex == \WDLIB\MALE) ? $m : $f;
	}
	static public function genderPattern(int $sex, string $str) : string
	{
		return preg_replace_callback(self::GENDER_PATTERN, function($match) use ($sex) {
			return ($sex == \WDLIB\MALE) ? $match[1] : $match[2];
		}, $str);
	}
}
