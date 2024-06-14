<?php 

namespace WDLIB;

class Util_Form_Base {

	static public function getstr(\WDLIB\Model_Request $http, string $key, int $sub, bool $_clear = true) : string
	{
		return self::_str($http->getstr($key), $sub, $_clear);
	}

	static public function str(array $data, string $key, int $sub, bool $_clear = true) : string
	{
		return self::_str(\WDLIB\Util_Array::isset($data, $key, ""), $sub, $_clear);
	}

	static public function _str(string $str, int $sub, bool $_clear = true) : string
	{
		if($_clear) {
			$str = self::clearstr($str);
		}
		else {
			$str = trim($str);
		}
		$str = mb_substr($str, 0, $sub);
		return $str;
	}

	static public function clearstr(string $str) : string
	{
		$str = str_replace(array("\n", "\r", "\t"), " ", $str);
		$str = preg_replace("/[ ]{2,}/", " ", $str);
		$str = trim($str);

		return $str;
	}
}
