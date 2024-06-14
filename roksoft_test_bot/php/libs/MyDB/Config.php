<?php

namespace MyDB;

class Config {

	public $host = "localhost";
	public $port = 3306;
	public $sock = "/tmp/mysql.sock";

	public $user = "root";
	public $pass = "";

	public $db_name = "";

	public $charset = "utf8";

	public function __construct()
	{
		if(($args = func_get_args()) && count($args) && is_array($args[0])) {
			$this->init($args[0]);
		}
	}

	public function get_key() : string
	{
		return $this->host.$this->user.$this->db_name;
	}

	public function init(array $data)
	{
		$this->host = array_key_exists("host", $data) ? $data["host"] : $this->host;
		$this->port = array_key_exists("port", $data) ? $data["port"] : $this->port;
		$this->sock = array_key_exists("sock", $data) ? $data["sock"] : $this->sock;


		$this->user = array_key_exists("user", $data) ? $data["user"] : $this->user;
		$this->pass = array_key_exists("pass", $data) ? $data["pass"] : $this->pass;

		$this->db_name = array_key_exists("db_name", $data) ? $data["db_name"] : $this->db_name;

		$this->charset = array_key_exists("charset", $data) ? $data["charset"] : $this->charset;
	}
}
