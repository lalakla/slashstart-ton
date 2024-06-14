<?php

namespace WDLIB;

class Util_Wrapper {

	public $obj = null;

	public function __construct($obj)
	{
		$this->obj = $obj;
	}

	public function __destruct()
	{
		$this->obj = null;
	}
}
