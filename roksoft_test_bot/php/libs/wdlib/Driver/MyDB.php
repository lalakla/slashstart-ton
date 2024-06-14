<?php

namespace WDLIB;

class Driver_MyDB {

	private $store = null;

	public $last_query = null;
	public $profiler = array();

	public function send(string $key, /*\MyDB\Query*/ $query) : \MyDB\Mysql_Result
	{
		$args = func_get_args();
		if($args < 2) {
			trigger_error("\\WDLIB\\Driver_MyDB::send : arguments error", E_USER_WARNING);

			$retval = new \MyDB\Mysql_Result;
			$retval->error = new \MyDB\Error(\MyDB\Error::ERROR_ARGUMENTS, "\\WDLIB\\Driver_MyDB::send : arguments error");
			return $retval;
		}

		$key = $args[0];
		$store = $this->getStore();

		$q = null;
		if($args[1] instanceof \MyDB\Query) {
			$q = $args[1];
		}
		else {
			$q = new \MyDB\Query;
			$q->query = $args[1];
			for($i=2; $i<count($args); ++$i) {
				$q->values[] = $args[$i];
			}
		}
		
		$this->last_query = $q;

		$_time = microtime(true);

		$result = $store->get($key)->query($q);

		$_time = microtime(true) - $_time;
		$this->profiler[] = array(
			"q" => $q,
			"time" => $_time
		);

		return $result;
	}

	public function query(string $method, string $key, /*\MyDB\Query*/$query) : \MyDB\Mysql_Result
	{
		$table = $key;
		if(($query instanceof \MyDB\Query) && $query->table) {
			$table = $query->table;
		}

//		$profiler = new Driver_Cstat_Db($method, $table);

		$args = func_get_args();
		array_shift($args);
		$result = call_user_func_array(array($this, "send"), $args);

//		if($result->affected_rows) {
//			$profiler->affected($result->affected_rows);
//		}
//		if($result->error) {
//			$profiler->error(1);
//		}

		return $result;
	}
	public function errorHandler(\MyDB\Error $error)
	{
		// check of hideErrors
		if($error->last_query && $error->last_query->hideErrors) {
			return;
		}

		$log = new Logger;
		echo "MyDB_ERROR: ".$error->mess."\n";
		
		//if($error->code != \MyDB\Error::ERROR_TABLE_UNEXISTS) {
			print_r($error);
		//}
	}

	private function getStore()
	{
		if(!$this->store) {
			$this->store = new \MyDB\Storage;

			$_config = Core::config("mydb_storage");
			foreach($_config as $key => $val) {
				$config = new \MyDB\Config($val);
				$this->store->add($key, $config)->set_error_handler(array($this, "errorHandler"));
			}
		}

		return $this->store;
	}

	static private $_instance = null;

	static public function getInstance()
	{
		if(!self::$_instance) {
			self::$_instance = new Driver_MyDB;
		}

		return self::$_instance;
	}
}
