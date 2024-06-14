<?php

namespace WDLIB;

class Stats {

	const __DB_STORAGE = "stats";

	// PUBLIC ==========================================================

	static public function add(int $platform, string $id, string $stat, int $amount = 1, int $traffic_id = 0, int $flags = 0)
	{
//		Driver_Stats::getInstance()->add($platform, $id, $stat, $amount, $traffic_id, $flags);

		$hash = self::getHash($stat);
		$hashes_update = false;
		
		$s = self::getInstance();

		if(!array_key_exists($hash, $s->hashes)) {
			$s->hashes[$hash] = $stat;
			$hashes_update = true;
		}

		$hit = new Stats_Hit($platform, $id, $hash);
		$hit->traffic_id = $traffic_id;

		if($s->hits->contains($hit)) {
			// item exists
			$hit = $s->hits->get($hit);
			$hit->value += $amount;
			$hit->traffic_id = $traffic_id ? $traffic_id : $hit->traffic_id;
		}
		else {
			$hit->value = $amount;
			$s->hits->add($hit);
		}

		if($hashes_update) {
			$db = Driver_MyDB::getInstance();
			$db->query(__METHOD__, self::__DB_STORAGE, "REPLACE INTO ".self::HASH_TABLE." (".self::HASH_FIELDS.") VALUES(?, ?)", $hash, $stat);
		}
	}

	static public function querySig(array $args) : string
	{
		$str = "";
		foreach($args as $ar) {
			$str .= (string)$ar;
		}
		return md5($str);
	}
	static public function _querySig(\MyDB\Query $query) : string
	{
		$str = $query->query;
		foreach($query->values as $ar) {
			$str .= (string)$ar;
		}
		return md5($str);
	}
	static public function fillHashes(array &$columns, string $re = null)
	{
		$s = self::getInstance();
		$hashes = $s->_fillHashes($columns, $re);
		return $hashes;
	}

	static public function selectHourStats(int $platform, int $hours, int $day, array &$columns, string $re = null, int $traffic_id = 0, array $flags = null) : array
	{
		$retval = array();

		$s = self::getInstance();
		$hashes = $s->_fillHashes($columns, $re);

		$now = Core::curtime() - self::STATS_DAY_TIMEOUT * $day;
		$date = getdate($now);
		$db = Driver_MyDB::getInstance();

//		$use_cache = (bool) $day;
//		$cacher = Driver_Clsv_Cacher::getInstance();
		
//		$log = new \Framework\Logger;

		for($i=0; $i<$hours; ++$i) {
			$_date = getdate($now);
			if($_date["mday"] != $date["mday"]) break;
			$date_str = date("Y-m-d H", $now);
			$table = self::getHourTable($now);
			$now -= self::STATS_HOUR_TIMEOUT;

			$query = new \MyDB\Query;
			$query->hideErrors = true;
			if($traffic_id) {
				$query->query = "SELECT hash, COUNT(*) as `uniqs`, SUM(hits) as `hits` FROM $table FORCE INDEX(`Index_2`) WHERE platform=? AND `traffic_id`=?";
				$query->values[] = $platform;
				$query->values[] = $traffic_id;
			}
			else {
				$query->query = "SELECT hash, COUNT(*) as `uniqs`, SUM(hits) as `hits` FROM $table FORCE INDEX(`Index_1`) WHERE platform=?";
				$query->values[] = $platform;
			}

			if(!empty($flags)) {
				foreach($flags as $f) {
					if(($op = Util_Array::isset($f, "operand"))) {
						$query->query .= " AND `flags` {$op} {$f["value"]} ";
					}
					else {
						$query->query .= " AND `flags` ";
						foreach($f as $ff) {
							$query->query .= "{$ff["operand"]} {$ff["value"]} ";
						}
						$query->query .= " ";
					}
				}
			}

			$query->query .= " AND `hash` IN (";
			
			$first = true;
			foreach($hashes as $hash) {
				if($first) {$first=false;} else {$query->query .= ",";}
				$query->query .= "?";
				$query->values[] = $hash;
			}

			$query->query .= ") GROUP BY `hash`";

//			$query_sig = self::_querySig($query);
//			if($use_cache) {
//				$data = $cacher->get(self::__CACHER_STORAGE, $query_sig);
//				if($data) {
//					$retval[] = $data;
//					continue;
//				}
//			}

			$query->hideErrors = true;
			$rows = $db->query(__METHOD__, self::__DB_STORAGE, $query);

//			echo "QUERY : {$query->query}\n";

			if($rows->error) {
				if($rows->error->code == \MyDB\Error::ERROR_TABLE_UNEXISTS) {
					continue;
				}
				break;
			}

			if($rows->num_rows) {
				$retval[] = array(
					"date" => $date_str,
					"values" => self::prepareValues($rows, $hashes)
				);

//				if($use_cache) {
//					$cacher->setOne(self::__CACHER_STORAGE, $query_sig, end($retval));
//				}
			}

//			$use_cache = true;
		}

		self::prepareRetval($retval);

		return $retval;
	}

	static public function selectDayStats(int $platform, int $days, array &$columns, string $re = null, int $traffic_id = 0, array $flags = null, bool $force_cache = false) : array
	{
		$retval = array();

		$s = self::getInstance();
		$hashes = $s->_fillHashes($columns, $re);

		$now = Core::curtime();
		$db = Driver_MyDB::getInstance();

//		$use_cache = false;
//		$cacher = Driver_Clsv_Cacher::getInstance();

		for($i=0; $i<$days; ++$i) {
			$date = getdate($now);
			$date_str = date("Y-m-d", $now);
			$table = self::getDayTable($now);
			$date_num = intval(date("Ymd", $now));
			$now -= self::STATS_DAY_TIMEOUT;
			
			$query = new \MyDB\Query;
			$query->hideErrors = true;
			if($traffic_id) {
				$query->query = "SELECT hash, COUNT(*) as `uniqs`, SUM(hits) as `hits` FROM $table FORCE INDEX(`Index_2`) WHERE platform=? AND `traffic_id`=? ";
				$query->values[] = $platform;
				$query->values[] = $traffic_id;
			}
			else {
				$query->query = "SELECT hash, COUNT(*) as `uniqs`, SUM(hits) as `hits` FROM $table FORCE INDEX(`Index_1`) WHERE platform=? ";
				$query->values[] = $platform;
			}

			if(!empty($flags)) {
				foreach($flags as $f) {
					if(($op = Util_Array::isset($f, "operand"))) {
						$query->query .= " AND `flags` {$op} {$f["value"]} ";
					}
					else {
						$query->query .= " AND `flags` ";
						foreach($f as $ff) {
							$query->query .= "{$ff["operand"]} {$ff["value"]} ";
						}
						$query->query .= " ";
					}
				}
			}

			$query->query .= " AND `hash` IN (";
			
			$first = true;
			foreach($hashes as $hash) {
				if($first) {$first=false;} else {$query->query .= ",";}
				$query->query .= "?";
				$query->values[] = $hash;
			}

			$query->query .= ") GROUP BY `hash`";

//			$query_sig = self::_querySig($query);
//			if($use_cache) {
//				$data = $cacher->get(self::__CACHER_STORAGE, $query_sig);
//				if($data) {
//					$retval[] = $data;
//					continue;
//				}
//			}

			$rows = new \MyDB\Mysql_Result;
	
			// check stats_cache_day table first
			if(/*$use_cache &&*/ empty($flags)) {
				$_query = new \MyDB\Query;
				$_query->query = "SELECT hash, uniqs, hits FROM ".self::STATS_CACHE_DAY_TABLE." WHERE platform=? AND `traffic_id`=? AND `date`=? AND `hash` IN (";
				$_query->values[] = $platform;
				$_query->values[] = $traffic_id;
				$_query->values[] = $date_num;
				
				$first = true;
				foreach($hashes as $hash) {
					if($first) {$first=false;} else {$_query->query .= ",";}
					$_query->query .= "?";
					$_query->values[] = $hash;
				}
				$_query->query .= ")";
				
				$query->hideErrors = true;
				$rows = $db->query(__METHOD__, self::__DB_STORAGE, $_query);

				if($rows->error && $rows->error->code == \MyDB\Error::ERROR_TABLE_UNEXISTS) {
					// create table
					$db->query(__METHOD__, self::__DB_STORAGE, self::createStatsDayCacheTable(self::STATS_CACHE_DAY_TABLE));
				}
			}

			if(!$rows->num_rows) {

//				if($use_cache && $force_cache) {
//					// load cached only !!!
//					continue;
//				}

				$query->hideErrors = true;
				$rows = $db->query(__METHOD__, self::__DB_STORAGE, $query);
			}

			if($rows->error) {
				if($rows->error->code == \MyDB\Error::ERROR_TABLE_UNEXISTS) {
					continue;
				}
				break;
			}

			if($rows->num_rows) {
				$retval[] = array(
					"date" => $date_str,
					"values" => self::prepareValues($rows, $hashes)
				);

//				if($use_cache) {
//					$cacher->setOne(self::__CACHER_STORAGE, $query_sig, end($retval));
//				}
			}

//			$use_cache = true;
		}

		self::prepareRetval($retval);

		return $retval;
	}

	static public function getHash(string $comment)
	{
		return clsv_crc32($comment);
	}

	// PRIVATE =========================================================

	const HASH_TABLE = "stats_hash";
	const HASH_FIELDS = "`hash`, `comment`";
	const STATS_DAY_TABLE_MASK = "stats_day_";
	const STATS_HOUR_TABLE_MASK = "stats_hour_";
	const STATS_MONTH_TABLE_MASK = "stats_month_";
	const STATS_FIELDS = "`platform`, `id`, `hash`, `hits`, `traffic_id`";
	const STATS_DAY_TIMEOUT = 86400;
	const STATS_HOUR_TIMEOUT = 3600;

	const STATS_CACHE_DAY_TABLE = "`stats_cache_day`";
	
	static private $_instance = null;

	static private function getInstance()
	{
		if(self::$_instance == null) {
			self::$_instance = new Stats;
		}
		return self::$_instance;
	}

	static private function prepareRetval(array &$retval)
	{
		foreach($retval as &$row) {
			$values =& Util_Array::isset($row, "values");
			foreach($values as &$val) {
				$c = new Stats_Value($val);
				$val = $c;
			}
		}
	}

	static private function prepareValues(/*array|\MyDB\Result*/ $rows, array $hashes) : array
	{
		$values = array();
		$j = 0;
		foreach($rows as $row) {
			$hash = (int)$row["hash"];

			while($j < count($hashes) && $hashes[$j] < $hash) {
				// fill by empty values
				$values[$hashes[$j]] = array(
					"uniqs" => 0,
					"hits" => 0
				);
				++$j;
			}

			if($j == count($hashes)) break;

			if($hash == $hashes[$j]) {
				$values[$hash] = array(
					"uniqs" => (int) $row["uniqs"],
					"hits" => (int) $row["hits"]
				);
				++$j;
			}
		}
		if($j < count($hashes)) {
			for(; $j<count($hashes); ++$j) {
				$values[$hashes[$j]] = array(
					"uniqs" => 0,
					"hits" => 0
				);
			}
		}
		return $values;
	}

	static public function getHourTable($now = 0)
	{
		if(!$now) $now = Core::curtime();
		return self::STATS_HOUR_TABLE_MASK.date("YmdH", $now);
	}
	static public function getDayTable($now)
	{
		if(!$now) $now = Core::curtime();
		return self::STATS_DAY_TABLE_MASK.date("Ymd", $now);
	}
	static public function getMonTable($now)
	{
		if(!$now) $now = Core::curtime();
		return self::STATS_MONTH_TABLE_MASK.date("Ym", $now);
	}
	
	static public function createHashTable($table)
	{
		return <<<EOD
CREATE TABLE IF NOT EXISTS $table (
	`hash` INT UNSIGNED NOT NULL DEFAULT '0',
	`comment` VARCHAR(256) NOT NULL DEFAULT '',
	PRIMARY KEY (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOD;
	}

	static public function createStatsTable($table)
	{
		return <<<EOD
CREATE TABLE IF NOT EXISTS $table (
	`platform` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
	`hash` INT UNSIGNED NOT NULL DEFAULT 0,
	`hits` INT UNSIGNED NOT NULL DEFAULT 0,
	`traffic_id` INT NOT NULL DEFAULT 0,
	PRIMARY KEY(`platform`, `id`, `hash`),
	KEY `Index_1`  (`platform`, `hash`),
	KEY `Index_2`  (`platform`, `traffic_id`, `hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOD;
	}

	static public function createStatsDayCacheTable($table)
	{
		return <<<EOD
CREATE TABLE IF NOT EXISTS $table (
	`platform` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`date` INT UNSIGNED NOT NULL DEFAULT 0,
	`hash` INT UNSIGNED NOT NULL DEFAULT 0,
	`traffic_id` INT UNSIGNED NOT NULL DEFAULT 0,
	`hits` INT UNSIGNED NOT NULL DEFAULT 0,
	`uniqs` INT UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY(`platform`, `traffic_id`, `date`, `hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOD;
	}

	private $hashes = array();
	private $hits = null;

	private function __construct()
	{
		$this->hits = new \Ardent\Collection\AvlTree(array("\WDLIB\Stats_Hit", "compare"));


		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, "SELECT ".self::HASH_FIELDS." FROM ".self::HASH_TABLE);

		// check error
		if($rows->error) {
			if($rows->error->code == \MyDB\Error::ERROR_TABLE_UNEXISTS) {
				// HASH_TABLE doesn't exists yet
				$db->query(__METHOD__, self::__DB_STORAGE, self::createHashTable(self::HASH_TABLE));
			}
		}
		else if($rows->num_rows) {
			foreach($rows as $row) {
				$this->hashes[$row["hash"]] = $row["comment"];
			}
		}
	}

	public function __destruct()
	{
		$this->flush();
	}

	private function flush()
	{
		if(!$this->hits || $this->hits->isEmpty()) return;

		$stats_update = "";
		$stats_values = array();
		$first = true;
		foreach($this->hits as $hit) {
			if($first) {$first=false;} else {$stats_update .= ",";}
			$stats_update .= "(?, ?, ?, ?, ?)";
			$stats_values[] = $hit->platform;
			$stats_values[] = $hit->id;
			$stats_values[] = $hit->hash;
			$stats_values[] = $hit->value;
			$stats_values[] = $hit->traffic_id;
		}

		$this->hits->clear();
		$this->update_db($stats_update, $stats_values);
	}

	private function update_db(string $stats_update, array $stats_values)
	{
		$now = Core::curtime();
		$htable = self::getHourTable($now);
		$dtable = self::getDayTable($now);
		$mtable = self::getMonTable($now);

		$db = Driver_MyDB::getInstance();
		$db->query(__METHOD__, self::__DB_STORAGE, self::createStatsTable($htable));
		$db->query(__METHOD__, self::__DB_STORAGE, self::createStatsTable($dtable));
		$db->query(__METHOD__, self::__DB_STORAGE, self::createStatsTable($mtable));

		
		$args = $stats_values;
		array_unshift($args, "");
		array_unshift($args, self::__DB_STORAGE);
		array_unshift($args, __METHOD__);

		$args[2] = "INSERT INTO $htable (".self::STATS_FIELDS.") VALUES $stats_update ON DUPLICATE KEY UPDATE `hits` = `hits` + VALUES(`hits`)";
		call_user_func_array(array($db, "query"), $args);
		$args[2] = "INSERT INTO $dtable (".self::STATS_FIELDS.") VALUES $stats_update ON DUPLICATE KEY UPDATE `hits` = `hits` + VALUES(`hits`)";
		call_user_func_array(array($db, "query"), $args);
		$args[2] = "INSERT INTO $mtable (".self::STATS_FIELDS.") VALUES $stats_update ON DUPLICATE KEY UPDATE `hits` = `hits` + VALUES(`hits`)";
		call_user_func_array(array($db, "query"), $args);
	}

	private function _fillHashes(array &$columns, string $re = null)
	{
		$hashes = array();
		if($columns && count($columns)) {
			foreach($columns as &$col) {

				$c = new Stats_Column($col);
				$col = $c;
				$hashes[] = $c->hash;
			}
		}
		else {
			$columns = array();
			foreach($this->hashes as $hash => $comment) {
				// check regexp
				if($re && !preg_match($re, $comment)) {
					continue;
				}
				$hashes[] = $hash;
				$columns[] = new Stats_Column(array(
					"name" => (string) $comment,
					"hash" => $hash
				));
			}
		}

		sort($hashes);

		return $hashes;
	}
}

class Stats_Hit {

	public $platform = 0;
	public $id = "";
	public $hash = 0;

	public $value = 0;
	public $traffic_id = 0;

	public function __construct($platform, $id, $hash)
	{
		$this->platform = $platform;
		$this->id = $id;
		$this->hash = $hash;
	}

	static public function compare(Stats_Hit $a, Stats_Hit $b)
	{
		$r = $a->platform - $b->platform;
		if(!$r) $r = strcmp($a->id, $b->id);
		if(!$r) $r = $a->hash - $b->hash;

		return $r;
	}
}

class Stats_Column {

	public $name = "";
	public $_name = "";
	public $hash = 0;
	public $hits = 0;

	public $_class = "";

	public function __construct(/*string|array|Stats_Column*/ $val)
	{
		if($val instanceof Stats_Column) {
			$this->name = $val->name;
			$this->_name = $val->_name;
			$this->hash = $val->hash;
			$this->hits = $val->hits;
			$this->_class = $val->_class;
		}
		else if(is_array($val)) {
			$this->name = Util_Array::isset($val, "name", $this->name);
			$this->_name = Util_Array::isset($val, "_name", $this->_name);
			$this->_class = Util_Array::isset($val, "_class", $this->_class);
			$this->hash = Util_Array::isset($val, "hash", 0);
		}
		else {
			$this->name = (string) $val;
		}

		if(!$this->hash) {
			$this->hash = Stats::getHash($this->name);
		}
	}

	public function getName()
	{
		return $this->_name ? $this->_name : $this->name;
	}
}

class Stats_Value {

	public $uniqs = 0;
	public $hits = 0;

	public $delta_uniqs = 0;
	public $delta_hits = 0;

	public function __construct($val = null)
	{
		if($val instanceof Stats_Value) {
			$this->uniqs = $val->uniqs;
			$this->hits = $val->hits;

			$this->delta_uniqs = $val->delta_uniqs;
			$this->delta_hits = $val->delta_hits;
		}
		else if(is_array($val)) {
			$this->uniqs = Util_Array::isset($val, "uniqs", $this->uniqs);
			$this->hits = Util_Array::isset($val, "hits", $this->hits);
		}
	}
}
