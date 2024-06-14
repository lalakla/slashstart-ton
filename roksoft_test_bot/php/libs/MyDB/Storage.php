<?php

namespace MyDB;

class Storage {

	private $sock_map = array();
	private $key_map = array();

	public function __construct()
	{
	}

	public function empty() : bool
	{
		return empty($this->key_map);
	}

	public function get(string $key)
	{
		return array_key_exists($key, $this->key_map) ? $this->key_map[$key] : NULL;
	}

	public function add(string $key, Config $config) : Mysql
	{
		$sock = NULL;

		$config_key = $config->get_key();
		if(!array_key_exists($config_key, $this->sock_map)){
			$sock = new Mysql($config);
			$this->sock_map[$config_key] = $sock;
		}
		else {
			$sock = $this->sock_map[$config_key];
		}

		if(!array_key_exists($key, $this->key_map)) {
			$this->key_map[$key] = $sock;
		}

		return $sock;
	}
}
