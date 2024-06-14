<?php

namespace WDLIB;

class Model_User_Info extends Model_Base {

	const ALL = 0;

	const TABLE = "`wdlib_user_info`";
	const FIELDS = "`user_id`, `key`, `val`, `date`";

	const __DB_STORAGE = "default";

	public $user_id = "";
	public $values = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function get(int $key) : int
	{
		$val = 0;
		if(($_val = Util_Array::isset($this->values, $key))) {
			if(is_array($_val)) {
				$val = (int)Util_Array::isset($_val, "val", $val);
			}
			else {
				$val = (int)$_val;
			}
		}
		return $val;
	}

	public function getA(int $key) : array
	{
		$val = array();
		$val = Util_Array::isset($this->values, $key, $val);
		return $val;
	}

	public function setA(int $key, array $val)
	{
		$this->values[$key] = $val;
	}

	public function clear()
	{
		$this->user_id = "";
		$this->values = array();
	}

	static private $cache = array();

	static public function select(string $user_id) : Model_User_Info
	{
		$info = new Model_User_Info;
		$info->user_id = $user_id;

		$query = new \MyDB\Query;
		$query->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `user_id`=?";
		$query->values[] = $user_id;

		$keys = array();

		// get or create internal cache
		if(!($_icache =& Util_Array::isset(self::$cache, $user_id))) {
			$_icache = array();
			self::$cache[$user_id] =& $_icache;
		}

		$args = func_get_args();
		if(count($args) > 1) {
			for($i=1; $i<count($args); ++$i) {
				$keys[] = $args[$i];
			}

			$first = true;

			// check internal cache first
			if(!empty($_icache)) {
				$_keys = array();
				foreach($keys as $key) {
					if(($p = Util_Array::isset($_icache, $key))) {
						$info->values[$key] = is_array($p) ? $p : array("val" => (int)$p);
						continue;
					}

					$_keys[] = $key;
				}
				$keys = $_keys;
			}

			if(!empty($keys)) {
				$query->query .= " AND `key` IN (";
				foreach($keys as $key) {
					if($first) {$first=false;} else {$query->query .= ",";}
					$query->query .= "?";
					$query->values[] = $key;
				}
				$query->query .= ")";
			}

			if($first) {
				// ALL DATA FOUND IN CACHE
				return $info;
			}
		}

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, $query);

		if($rows->num_rows) {
			foreach($rows as $row) {
				$key = (int) $row["key"];
				$val = array(
					"val" => (int)$row["val"],
					"date" => (int)$row["date"]
				);
				$info->values[$key] = $val;

				$_icache[$key] = $val;
			}
		}

		return $info;
	}
	static public function selectByUids(array $uids) : array
	{
		$retval = array();
		if(empty($uids)) return $retval;

		$args = func_get_args();

		$query = new \MyDB\Query;
		$query->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `user_id` IN (";

		$first = true;
		foreach($uids as $k=>$v) {
			if($first) {$first=false;} else {$query->query .= ",";}
			$query->query .= "?";
			$query->values[] = $k;
		}
		$query->query .= ")";

		if(count($args) > 1) {
			$first = true;
			$query->query .= " AND `key` IN (";
			for($i=1; $i<count($args); ++$i) {
				if($first) {$first=false;} else {$query->query .= ",";}
				$query->query .= "?";
				$query->values[] = $args[$i];
			}
			$query->query .= ")";
		}

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, $query);

		if($rows->num_rows) {
			foreach($rows as $row) {
				$user_id = (string)$row["user_id"];
				if(!($info = Util_Array::isset($retval, $user_id))) {
					$info = new Model_User_Info;
					$info->user_id = $user_id;
					$retval[$info->user_id] = $info;
				}

				$key = (int) $row["key"];
				$val = array(
					"val" => (int)$row["val"],
					"date" => (int)$row["date"]
				);
				$info->values[$key] = $val;
			}
		}

		return $retval;
	}
	
	static public function increase(string $user_id, int $key, int $val)
	{
		$now = Core::curtime();

		$db = Driver_MyDB::getInstance();
		$db->query(
			__METHOD__,
			self::__DB_STORAGE,
			"INSERT INTO ".self::TABLE." (".self::FIELDS.") VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE `val`=`val` + VALUES(`val`), `date`=VALUES(`date`)",
			$user_id, $key, $val, $now
		);
	}

	static public function set(string $user_id, int $key, int $val)
	{
		$now = Core::curtime();

		$db = Driver_MyDB::getInstance();
		$db->query(
			__METHOD__,
			self::__DB_STORAGE,
			"INSERT INTO ".self::TABLE." (".self::FIELDS.") VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE `val`=VALUES(`val`), `date`=VALUES(`date`)",
			$user_id, $key, $val, $now
		);

		// set internal cache
		if(!($_icache =& Util_Array::isset(self::$cache, $user_id))) {
			$_icache = array();
			self::$cache[$user_id] =& $_icache;
		}
		$_icache[$key] = array("val" => $val, "date" => $now);
	}

	static public function drop(string $user_id)
	{
		$db = Driver_MyDB::getInstance();
		$db->query(__METHOD__, self::__DB_STORAGE, "DELETE FROM ".self::TABLE." WHERE `user_id`=?", $user_id);
	}
}
