<?php

namespace WDLIB;

class Driver_Transmitter {

	private $client = null;

	public function __construct()
	{
		$config = Core::config("clsv");
		if(($config = Util_Array::isset($config, "transmitter"))) {
			$host = Util_Array::isset($config, "host", "");
			$port = Util_Array::isset($config, "port", 0);
			$sock = Util_Array::isset($config, "sock");
			
			$this->client = new \CLSV_Client(\CLSV\CoreWrapper::getInstance());

			$this->client->connect($host, $port, $sock);
			$this->client->set_timeout(10);
		}
	}

	public function __destruct()
	{
		if($this->client && $this->client->is_connected()) {
			$this->client->disconnect();
		}
	}

	public function add(string $url, array $headers = null)
	{
		$data = array(
			"cmd" => "add",
			"url" => $url
		);
		if(!empty($headers)) {
			$data["headers"] = $headers;
		}

		return $this->client->send($data);
	}

	static private $_instance = null;

	static public function getInstance()
	{
		if(!self::$_instance) {
			self::$_instance = new Driver_Transmitter;
		}

		return self::$_instance;
	}
}
