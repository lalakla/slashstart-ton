<?php

namespace WDLIB;

class Model_Request extends Model_Base {

	public $uri = "";
	public $query = "";

	public $controller = "";
	public $method = "";

	public $request = null;

	public $params = null;
	public $cookies = null;

	public $_is_https = false;

	public function __construct()
	{
		parent::__construct();

		$this->request = array();

		$this->uri = Util_Array::isset($_SERVER, "REQUEST_URI", "");
		$explode = explode('/', $this->uri, 10);
		for($i=0; $i<count($explode); ++$i) {
			if(!$explode[$i]) continue;

			$this->request[] = $explode[$i];
		}

		$this->query = Util_Array::isset($_SERVER, "QUERY_STRING", "");

		while(count($this->request) < 2) {
			$this->request[] = "";
		}

		$this->controller = $this->request[0];
		$this->method = $this->request[1];

		$this->params = new Util_Mapss($_REQUEST);
		$this->cookies = new Util_Mapss($_COOKIE);

		// check IF HTTPS used
		if(($p = Util_Array::isset($_SERVER, "HTTPS")) && "off" !== strtolower($p)) {
			$this->_is_https = true;
		}
	}

	public function get($var, $def = null)
	{
		return $this->params->get($var, $def);
	}
	public function getint(string $var, int $def = 0) : int
	{
		return $this->params->getint($var, $def);
	}
	public function getfloat(string $var, float $def = 0.0) : float
	{
		return $this->params->getfloat($var, $def);
	}
	public function getstr(string $var, string $def = "", bool $escape = true, string $re = "") : string
	{
		$retval = $this->params->getstr($var, $def);
		if($escape) {
			$retval = htmlspecialchars($retval, ENT_QUOTES, "UTF-8");
		}
		if($re) {
			$retval = preg_match($re, $retval) ? $retval : $def;
		}
		return $retval;
	}
	public function geturl(string $var, string $def = "", string $re = "") : string
	{
		$retval = $this->getstr($var, "", false, $re);
		$retval = preg_replace_callback("/['\"()\\s\\t\\r\\n]+/", function($matches) {
			return urlencode($matches[0]);
		}, $retval);
		
		return $retval ? $retval : $def;
	}
	public function getjson(string $var, $def = null)
	{
		$retval = $this->getstr($var, "", false);
		if($retval) {
			$retval = json_decode($retval, true);
		}
		else {
			$retval = null;
		}
		return $retval !== null ? $retval : $def;
	}
	public function isset(string $var) : bool
	{
		return $this->params->isset($var);
	}

	public function getenv($var)
	{
		return Util_Array::isset($_SERVER, $var);
	}

	public function getParamsSorted() : array
	{
		return $this->params->getSorted();
	}

	public function debug(bool $need_params = false) : string
	{
		$ss = "/".implode('/', $this->request);
		if($need_params) {
			$ss .= " ".$this->params->debug();
		}
		return $ss;
	}
}

