<?php

namespace WDLIB;

class Driver_Stats {

	private $client = null;
	private $prefix = "";

	public function __construct()
	{
		$this->client = new \CLSV_Client(\CLSV\CoreWrapper::getInstance());

		$config = Core::config("clsv");
		$config = Util_Array::isset($config, "stats");
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

	public function add(int $platform, string $id, string $stat, int $amount = 1, int $traffic_id = 0, int $flags = 0)
	{
		$data = array(
			"prefix" => $this->prefix,
			"cmd" => "add",
			"platform" => $platform,
			"user_id" => $id,
			"stat" => $stat,
			"amount" => $amount,
			"traffic_id" => $traffic_id,
			"flags" => $flags
		);

		return $this->client->send($data);
	}

	static private $_instance = null;

	static public function getInstance()
	{
		if(!self::$_instance) {
			self::$_instance = new Driver_Stats;
		}

		return self::$_instance;
	}

}
