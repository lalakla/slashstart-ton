<?php

namespace WDLIB;

class View_Base implements \ArrayAccess {

	public $HTTPS = false;

	public $data;

	protected $templ;
	protected $includes = array();

	public function __construct()
	{
		$this->data = array();
	}

	public function __destruct()
	{
	}

	public function offsetExists($offset) : bool
	{
		return array_key_exists($offset, $this->data);
	}
	public function offsetSet($offset, $val)
	{
		$this->data[$offset] = $val;
	}
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
	public function offsetGet($offset)
	{
		return Util_Array::isset($this->data, $offset);
	}

	public function template(string $file)
	{
		$this->templ = Core::config("templates").'/'.$file;
		include $this->templ;
	}

	public function templateJson()
	{
		header("Content-Type: application/json; charset=utf-8");
		$content = json_encode($this->data);

		if($content === FALSE) {
			// ERROR
			Logger::log("JSON ERROR: ".json_last_error_msg());
		}

		echo $content;
	}
	public function templateJsonP(string $callback)
	{
		header("Content-Type: application/javascript; charset=utf-8");
		$content = json_encode($this->data);

		if($content === FALSE) {
			// ERROR
			Logger::log("JSON ERROR: ".json_last_error_msg());
		}

		echo "$callback($content)";
	}

	public function redirect(string $url)
	{
		//echo "url: ".$url;
		header("Location: ".$url);
		exit;
	}

	// HELPER FUNCTIONS ----------------------------------------------------------

	public function _isset(string $key, $def = null)
	{
		$args = func_get_args();

		$retval = Util_Array::isset($this->data, $key, null);

		if(count($args) > 2) {
			$def = end($args);
			for($i=1; $i<count($args) - 1; ++$i) {
				if(!is_array($retval)) {
					$retval = null;
					break;
				}
				$retval = Util_Array::isset($retval, $args[$i]);
			}
		}

		if($retval === null) {
			$retval = $def;
		}

		return $retval;
	}

	public function _static(string $url) : string
	{
		$_url = $this->_https(Core::config("static_url"));
		return $_url . $url;
	}
	public function _url(string $url) : string
	{
		$_url = $this->_https(Core::config("project_url"));
		return $_url . $url;
	}
	public function _appurl(string $url) : string
	{
		$appurl = $this->_https(Core::config("app_url"));
		return $appurl . $url;
	}

	public function _liburl(string $url) : string
	{
		$_url = $this->_https(Core::config("lib_url"));
		return $_url . $url;
	}
	public function _toString($data, int $options = 0) : string
	{
		$type = gettype($data);
		if($type == "array" || $type == "object") {
			$data = json_encode($data, JSON_UNESCAPED_UNICODE | $options);
		}

		return htmlspecialchars($data);
	}
	public function _date(int $date, string $fmt = "Y-m-d H:i:s") : string
	{
		return date($fmt, $date);
	}

	public function _include(string $file, array $vars = null)
	{
		$current = empty($this->includes) ? $this->templ : end($this->includes);

		$file = dirname($current)."/".$file;
		$this->includes[] = $file;

		if(is_array($vars)) {
			extract($vars);
		}

		include $file;

		array_pop($this->includes);
	}

	public function _https(string $url) : string
	{
		if($this->HTTPS) {
			$url = Util_Url::https($url);
		}

		return $url;
	}
}

