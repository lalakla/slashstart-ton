<?php

namespace WDLIB;

class Driver_Lpoll {

	private $client = null;
	private $prefix = "";

	public function __construct()
	{
	}

	public function event(\CLSV\Lpoll_Event $event)
	{
		$res = $this->getClient()->event($event);
		return $res;
	}
	
	public function eventBatch(\CLSV\Lpoll_EventBatch $batch)
	{
		$res = $this->getClient()->eventBatch($batch);
		return $res;
	}

	public function isOnline(string $uid) : bool
	{
		$uids = array($uid);
		$r = $this->checkOnline($uids);
		foreach($r as $id) {
			if($id == $uid) return true;
		}
		return false;
	}

	public function checkOnline(array $uids) : array
	{
		$retval = array();
		
		$res = $this->getClient()->checkOnline($uids);
		if(is_array($res) && array_key_exists("lpoll_uids", $res)) {
			$retval = $res["lpoll_uids"];
		}

		return $retval;
	}

	public function stats() : array
	{
		return $this->getClient()->stats();
	}

	static private $_instance = null;

	static public function getInstance()
	{
		if(!self::$_instance) {
			self::$_instance = new Driver_Lpoll;
		}

		return self::$_instance;
	}

	// PRIVATE ---------------------------------------------------------
	
	private function getClient()
	{
		if(!$this->client) {
			$config = Core::config("clsv");
			$config = Util_Array::isset($config, "lpoll");
			
			$prefix = Util_Array::isset($config, "prefix");
			$host = Util_Array::isset($config, "host", "");
			$port = Util_Array::isset($config, "port", 0);
			$sock = Util_Array::isset($config, "sock");

			$this->client = new \CLSV\Lpoll_Client($prefix);
			$this->client->connect($host, $port, $sock);
		}
		return $this->client;
	}
}

