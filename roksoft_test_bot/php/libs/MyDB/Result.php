<?php

namespace MyDB;

abstract class Result implements \Iterator, \ArrayAccess {

	public $error = null;

	public $inserted_id = 0;
	public $affected_rows = 0;
	public $num_rows = 0;

	public function __construct()
	{
	}

	public function offsetExists($offset) : bool
	{
		return FALSE;
	}
	public function offsetSet($offset, $val)
	{
	}
	public function offsetUnset($offset)
	{
	}
	public function offsetGet($offset)
	{
		return null;
	}
}
