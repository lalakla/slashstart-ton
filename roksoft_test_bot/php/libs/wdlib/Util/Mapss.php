<?php

namespace WDLIB;

class Util_Mapss {

	private $_data;
	private $_sorted = false;

	public function __construct(array $data = null)
	{
		$this->_data = ($data) ? $data : array();
	}

	public function get(string $var, $def = null)
	{
		return Util_Array::isset($this->_data, $var, $def);
	}
	public function getint(string $var, int $def = 0) : int
	{
		return (int)$this->get($var, $def);
	}
	public function getfloat(string $var, float $def = 0.0) : float
	{
		return (float)$this->get($var, $def);
	}
	public function getstr(string $var, string $def = "") : string
	{
		return (string)$this->get($var, $def);
	}
	public function isset(string $var) : bool
	{
		return array_key_exists($var, $this->_data) ? true : false;
	}

	public function getSorted() : array
	{
		if(!$this->_sorted) {
			ksort($this->_data);
			$this->_sorted = true;
		}

		return $this->_data;
	}

	public function debug() : string
	{
		$str = "";
		foreach($this->_data as $k => $v) {
			$str .= $k."=".$v."; ";
		}
		return $str;
	}
}

