<?php

namespace WDLIB;

class Driver_Memcached {

	private $m = null;
	private $prefix = "";

	public function get($key)
	{
		$retval = null;

		$real_key = $this->prefix.$key;
		if($this->m) {
			$retval = $this->m->get($real_key);
		}

		return $retval;
	}

	public function set($key, $value, $expire = 0)
	{
		$retval = null;

		$real_key = $this->prefix.$key;
		if($this->m) {
			$retval = $this->m->set($real_key, $value, $expire);
		}

		return $retval;
	}

	private function __construct()
	{
		$config = Core::config("memcached");
		$this->prefix = Util_Array::isset($config, "prefix", "");

		$persistent_id = Util_Array::isset($config, "persistent_id");
		$servers = Util_Array::isset($config, "servers", []);

		if($persistent_id) {
			$this->m = new \Memcached($persistent_id);
		}
		else {
			$this->m = new \Memcached;
		}

		if(0 == count($this->m->getServerList())) {
			foreach($servers as $ss) {
				$this->m->addServer($ss["host"], $ss["port"]);
			}
		}
	}

	static private $_instance = null;

	static public function getInstance()
	{
		if(!self::$_instance) {
			self::$_instance = new Driver_Memcached;
		}

		return self::$_instance;
	}
}
