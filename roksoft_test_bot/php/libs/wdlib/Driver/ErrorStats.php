<?php

namespace WDLIB;

class Driver_ErrorStats {

	private $client = null;
	private $prefix = "";

	public function __construct()
	{
		$this->client = new \CLSV_Client(\CLSV\CoreWrapper::getInstance());

		$config = Core::config("clsv");
		$config = Util_Array::isset($config, "error");
		$host = Util_Array::isset($config, "host", "");
		$port = Util_Array::isset($config, "port", 0);
		$sock = Util_Array::isset($config, "sock");
		$this->prefix = Util_Array::isset($config, "prefix", $this->prefix);

		$this->client->connect($host, $port, $sock);
		$this->client->set_timeout(1);
	}

	public function __destruct()
	{
		if($this->client && $this->client->is_connected()) {
			$this->client->disconnect();
		}
	}

	public function add(int $errno, int $platform, string $user_id, string $method, string $comment, int $ip)
	{
		$data = array(
			"prefix" => $this->prefix,
			"cmd" => "add",
			"errno" => $errno,
			"platform" => $platform,
			"user_id" => $user_id,
			"method" => $method,
			"comment" => $comment,
			"ip" => $ip,
			"date" => Core::curtime()
		);

		return $this->client->send($data);
	}

	static private $_instance = null;

	static public function getInstance()
	{
		if(!self::$_instance) {
			self::$_instance = new Driver_ErrorStats;
		}

		return self::$_instance;
	}

}
