<?php

namespace MyDB;

class Error implements \ArrayAccess {

	const ERROR = -1;
	const ERROR_ARGUMENTS = -2;
	const ERROR_NO_CONNECTION = -10;
	const ERROR_TABLE_UNEXISTS = 1146;

	public $mess = "";
	public $code = 0;
	public $last_query = "";

	public function __construct($code = 0, $mess = "")
	{
		$this->mess = $mess;
		$this->code = $code;
	}

	public function offsetSet($offset, $val)
	{
	}
	public function offsetExists($offset) : bool
	{
		return FALSE;
	}
	public function offsetUnset($offset)
	{
	}
	public function offsetGet($offset)
	{
		switch($offset) {
			case "code":
				return $this->code;
			case "mess":
				return $this->mess;
		}

		return null;
	}
}
