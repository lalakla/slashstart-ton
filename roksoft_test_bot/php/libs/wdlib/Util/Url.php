<?php

namespace WDLIB;

class Util_Url {

	public $uri = "";
	public $params = array();

	private $_str = "";

	public function __construct(string $uri = "", array $params = null)
	{
		$this->uri = $uri;
		$this->params = is_array($params) ? $params : $this->params;
	}

	public function addParam(string $key, string $val) : Util_Url
	{
		$this->params[$key] = $val;
		$this->_str = "";
		return $this;
	}

	public function toString() : string
	{
		if($this->_str) {
			return $this->_str;
		}

		$str = $this->uri . "?";

		$first = true;
		foreach($this->params as $k => $v) {
			if($first) {$first = false;} else {$str .= "&";}

			$str .= "$k=$v";
		}

		$this->_str = $str;

		return $str;

	}

	static public function https(string $url) : string
	{
		$pos = strpos($url, "http");
		if($pos !== FALSE && $url[$pos + 4] == ':') {
			$url = substr_replace($url, "https", $pos, 4);
		}

		return $url;
	}
}
