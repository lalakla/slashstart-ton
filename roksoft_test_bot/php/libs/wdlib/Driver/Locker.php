<?php

namespace WDLIB;

class Driver_Locker {

    private $client = null;
	private $prefix = null;

	public $key = null;

	private function __construct()
	{
		$config = Core::config("clsv");
		if(($config = Util_Array::isset($config, "locker"))) {
			$host = Util_Array::isset($config, "host", "");
			$port = Util_Array::isset($config, "port", 0);
			$sock = Util_Array::isset($config, "sock");
			$this->prefix = Util_Array::isset($config, "prefix");
			
			$this->client = new \CLSV_Client(\CLSV\CoreWrapper::getInstance());
			$this->client->set_timeout(3);

			$this->client->connect($host, $port, $sock);
		}
	}

    public function __destruct()
	{
		if($this->client && $this->client->is_connected()) {
			$this->client->disconnect();
		}
	}

    public function lock(string $key)
	{
		if(!$this->client || !$this->client->is_connected()) {
			// no client or connection
			return null;
		}

		$this->key = $key;
		$params = array(
			"cmd" => "lock",
			"key" => $this->prefix.$this->key
		);

		return $this->client->send($params);
	}
    public function unlock(string $key = null)
	{
		if(!$this->client || !$this->client->is_connected()) {
			// no client or connection
			return null;
		}

		if($key === null) {
			$key = $this->key;
		}
		$params = array(
			"cmd" => "unlock",
			"key" => $this->prefix.$key
		);

		return $this->client->send($params);
	}
	
	public function stats()
	{
		if(!$this->client || !$this->client->is_connected()) {
			// no client or connection
			return null;
		}

		$params = array(
			"cmd" => "stats"
		);

		return $this->client->send($params);
	}

    static private $_instance = null;

	static public function getInstance()
	{
		if(!self::$_instance) {
			self::$_instance = new Driver_Locker;
		}

		return self::$_instance;
	}
}

