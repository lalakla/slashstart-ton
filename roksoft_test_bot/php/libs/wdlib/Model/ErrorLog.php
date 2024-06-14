<?php

namespace WDLIB;

class Model_ErrorLog extends Model_Json {

	const TABLE = "wdlib_error_log";
	const FIELDS = "`errno`, `platform`, `user_id`, `method`, `comment`, `ip`, `date`";

	public $errno = 0;
	public $platform = 0;
	public $user_id = "";
	public $method = "";
	public $string = "";
	public $ip = "";
	public $date = 0;
	public $count = 0;

	public function __construct()
	{
		parent::__construct();
	}

	public function jsonSerialize() : array
	{
		return array (
			"errno" => $this->errno,
			"platform" => $this->platform,
			"user_id" => $this->user_id,
			"method" => $this->method,
			"comment" => $this->comment,
			"ip" => $this->ip,
			"date" => date("Y-m-d H:i:s", $this->date),
			"count" => $this->count
		);
	}

	public function clear()
	{
		$this->errno = 0;
		$this->platform = 0;
		$this->user_id = "";
		$this->method = "";
		$this->comment = "";
		$this->ip = "";
		$this->date = 0;
		$this->count = 0;
	}

	public function initFromRow(array $row)
	{
		$this->errno = (int) $row["errno"];
		$this->platform = (int) $row["platform"];
		$this->user_id = (string) $row["user_id"];
		$this->method = (string) $row["method"];
		$this->comment = (string) $row["comment"];
		$this->ip = long2ip((int)$row["ip"]);
		$this->date = (int) $row["date"];
		$this->count = (int) $row["count"];
	}

	static public function add(int $errno, int $platform, string $user_id, string $method, string $comment, int $ip)
	{
		$now = Core::curtime();
		$table = self::getMonTable($now);

		$db = Driver_MyDB::getInstance();

		$q = new \MyDB\Query("INSERT INTO $table (".self::FIELDS.") VALUES(?, ?, ?, ?, ?, ?, ?)", $errno, $platform, $user_id, $method, $comment, $ip, $now);
		$q->hideErrors = true;

		$res = $db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), $q);
		if($res->error && $res->error->code == \MyDB\Error::ERROR_TABLE_UNEXISTS) {
			// create table
			$db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), self::createTable($table));
			$db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), $q);
		}
	}

	static public function select(int $platform=-1, int $limit = 100) : array
	{
		$table = self::getMonTable();

		$query = "SELECT ".self::FIELDS.", 1 as `count` FROM $table";
		if($platform != -1) {
			$query .= " WHERE `platform`=?";
		}
		$query .= " ORDER BY `date` DESC LIMIT $limit";

		$args = array(
			$query,
			$platform
		);

		return self::_select($args);
	}
	static public function selectGroupErrno(int $limit = 100) : array
	{
		$table = self::getMonTable();

		$args = array(
			"SELECT `errno`, ANY_VALUE(`platform`) as `platform`, ANY_VALUE(`user_id`) as `user_id`, ANY_VALUE(`comment`) as `comment`, '' as `ip`, ANY_VALUE(`date`) as `date`, COUNT(`errno`) as `count` FROM $table GROUP BY `errno` ORDER BY `date` DESC LIMIT $limit"
		);
		return self::_select($args);
	}
	static public function selectGroupMethod(int $limit = 100) : array
	{
		$table = self::getMonTable();
		$args = array(
			"SELECT ".self::FIELDS.", COUNT(`method`) as `count` FROM $table GROUP BY `method` DESC ORDER BY `date` DESC LIMIT $limit"
		);
		return self::_select($args);
	}
	static public function selectByErr(int $platform, int $err, int $limit = 100) : array
	{
		$table = self::getMonTable();
	
		$query = "SELECT ".self::FIELDS.", 1 as `count` FROM $table WHERE `errno`=?";
		if($platform != -1) {
			$query .= " AND `platform`=?";
		}
		$query .= " ORDER BY `date` DESC LIMIT $limit";
	
		$args = array(
			$query,
			$err,
			$platform
		);
		return self::_select($args);
	}
	static public function selectByUser(int $platform, string $user_id, int $limit = 100) : array
	{
		$table = self::getMonTable();

		$query = "SELECT ".self::FIELDS.", 1 as `count` FROM $table WHERE `user_id`=?";
		if($platform != -1) {
			$query .= " AND `platform`=?";
		}
		$query .= " ORDER BY `date` DESC LIMIT $limit";

		$args = array(
			$query,
			$user_id,
			$platform
		);
		return self::_select($args);
	}

	static private function _select(array $args) : array
	{
		$retval = array();

		$db = Driver_MyDB::getInstance();
		$query = new \MyDB\Query;
		$query->query = $args[0];
		for($i=1; $i<count($args); ++$i) {
			$query->values[] = $args[$i];
		}

		$rows = $db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), $query);

		foreach($rows as $row) {
			$item = new Model_ErrorLog;
			$item->initFromRow($row);
			$retval[] = $item;
		}

		return $retval;
	}

	static public function getMonTable(int $now = 0)
	{
		if(!$now) $now = Core::curtime();
		return self::TABLE."_".date("Ym", $now);
	}

	static public function createTable($table)
	{
		return <<<EOD
CREATE TABLE IF NOT EXISTS $table (
	`errno` INT NOT NULL DEFAULT 0,
	`platform` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`user_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
	`method` text NOT NULL,
	`comment` text NOT NULL,
	`date` int unsigned NOT NULL DEFAULT '0',
	`ip` int unsigned not null default 0,
	KEY `Index_3` (`date`),
	KEY `Index_1` (`errno`,`date`),
	KEY `Index_2` (`user_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOD;
	}
}
