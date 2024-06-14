<?php

namespace MyDB;

class Mysql {

	public $error = null;
	public $query = "";

	private $link = null;
	private $driver = null;
	private $error_handler = null;

	private $config = null;
	private $_is_conncted = false;

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function set_error_handler(callable $handler)
	{
		$this->error_handler = $handler;
	}

	public function change_db(string $db) : bool
	{
		if(!$this->_is_conncted) {
			$this->connect();
		}

		if(!$this->link) {
			// no connection
			$this->error = new Error(Error::ERROR_NO_CONNECTION, "No connection");
			if($this->error_handler) call_user_func_array($this->error_handler, array($this->error));
			return FALSE;
		}
		if(!$this->link->select_db($db)) {
			// select db error
			$this->error = new Error($this->link->errno, $this->link->error);
			if($this->error_handler) call_user_func_array($this->error_handler, array($this->error));
			return FALSE;
		}
		return TRUE;
	}

	public function change_charset(string $charset) : bool
	{
		if(!$this->_is_conncted) {
			$this->connect();
		}

		if(!$this->link) {
			// no connection
			$this->error = new Error(Error::ERROR_NO_CONNECTION, "No connection");
			if($this->error_handler) call_user_func_array($this->error_handler, array($this->error));
			return FALSE;
		}
		if(!$this->link->set_charset($charset)) {
			// set_charset error
			$this->error = new Error($this->link->errno, $this->link->error);
			if($this->error_handler) call_user_func_array($this->error_handler, array($this->error));
			return FALSE;
		}
		return TRUE;
	}

	public function query() : Result
	{
		if(!$this->_is_conncted) {
			$this->connect();
		}

		$retval = new Mysql_Result;

		if(!$this->link) {
			// no connection
			$this->error = new Error(Error::ERROR_NO_CONNECTION, "No connection");
			$retval->error = $this->error;
			if($this->error_handler) call_user_func_array($this->error_handler, array($this->error));
			return $retval;
		}

		$args = func_get_args();
		if($args[0] instanceof Query) {
			$this->query = $args[0];
		}
		else {
			$this->query = new Query;
			$this->query->query = $args[0];
			for($i = 1; $i<count($args); ++$i) {
				$this->query->values[] = $args[$i];
			}
		}

		$this->prepare($this->query);
		$result = $this->link->query($this->query->query);

		if($result === FALSE) {
			// some error occured
			$this->error = new Error($this->link->errno, $this->link->error);
			$this->error->last_query = $this->query;
			$retval->error = $this->error;
			if($this->error_handler) call_user_func_array($this->error_handler, array($this->error));
		}
		else {
			$retval->inserted_id = $this->link->insert_id;
			$retval->affected_rows = $this->link->affected_rows;
			if(is_object($result)) {
				// mysqli_result returned
				$retval->result = $result;
				$retval->num_rows = $retval->result->num_rows;
			}
		}

		return $retval;
	}
	
	private function connect() : bool
	{
		$retval = TRUE;

		$this->driver = new \mysqli_driver();
//		$this->driver->report_mode = MYSQLI_REPORT_ERROR;
		$this->driver->report_mode = MYSQLI_REPORT_OFF;

//		if(!$port) $port = ini_get("mysqli.default_port");
//		if(!$sock) $sock = ini_get("mysqli.default_socket");
//		$this->link = new \mysqli($host, $user, $pass, $db, $port, $sock);
		$this->link = new \mysqli(
			$this->config->host, $this->config->user, $this->config->pass,
			$this->config->db_name, $this->config->port, $this->config->sock
		);
		if($this->link->connect_error) {
			// connection error
			$this->error = new Error($this->link->connect_errno, $this->link->connect_error." host: ".$this->config->host.", db: ".$this->config->db_name);
			if($this->error_handler) call_user_func_array($this->error_handler, array($this->error));
			$retval = FALSE;
		}

		$this->_is_conncted = $retval;

		$this->change_charset($this->config->charset);

		return $retval;
	}

	private function prepare(Query $query)
	{
		if($query->cached) {
			return;
		}

		$it = 0;
		$_query = "";

		$prev = 0;
		while($it < count($query->values)) {
			$pos = strpos($query->query, '?', $prev);
			if($pos === FALSE) {
				break;
			}

			$_query .= substr($query->query, $prev, $pos - $prev);
			$_query .= "'".$this->link->escape_string($query->values[$it])."'";

			$it++;
			$prev = $pos + 1;
		}

		$_query .= substr($query->query, $prev);
		$query->query = $_query;
		$query->cached = true;
	}
}
