<?php

namespace WDLIB;

class Util_Array {

	static public function & isset(array &$arr, $key, $default = null)
	{
		$retval =& $default;

		if(is_array($arr) && array_key_exists($key, $arr)) {
			$retval =& $arr[$key];
		}

		return $retval;
	}

	static public function constIsset(array $arr, $key, $default = null)
	{
		$retval = $default;

		if(is_array($arr) && array_key_exists($key, $arr)) {
			$retval = $arr[$key];
		}

		return $retval;
	}

	static public function isAssoc(array $arr) : bool
	{
		if(array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	static public function randomItem(array $arr)
	{
		if(empty($arr)) {
			return null;
		}
		if(count($arr) == 1) {
			return $arr[0];
		}

		$rnd = rand(0, count($arr) - 1);
		return $arr[$rnd];
	}
}
