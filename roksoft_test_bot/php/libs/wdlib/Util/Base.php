<?php

namespace WDLIB;

class Util_Base {

	static public function max($a, $b)
	{
		return ($a < $b) ? $b : $a;
	}
	static public function min($a, $b)
	{
		return ($a < $b) ? $a : $b;
	}
}
