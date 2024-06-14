<?php

namespace WDLIB;

class Model_Stats_AppParams extends Model_Base {

	const TABLE = "`wdlib_stats_app_params`";
	const FIELDS = "`date`, `platform`, `referrer`, `params`";

	public $date = 0;
	public $platform = 0;
	public $referrer = "";
	public $params = array();

	public function __construct()
	{
		parent::__construct();
	}

	private function clear()
	{
		$this->date = 0;
		$this->platform = 0;
		$this->referrer = "";
		$this->params = array();
	}

	private function initFromRow(array $row)
	{
		$this->date = (int)$row["date"];
		$this->platform = (int)$row["platform"];
		$this->referrer = $row["referrer"];
		$this->params = clsv_binary_deserialize($row["params"]);
	}
	
	static public function select(int $platform, int $limit = 100) : array
	{
		$items = array();

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, Util_DbStorage::storage(__CLASS__), "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `platform`=? ORDER BY `date` DESC LIMIT $limit", $platform);

		foreach($rows as $row) {
			$item = new Model_Stats_AppParams;
			$item->initFromRow($row);
			$items[] = $item;
		}

		return $items;
	}

	static public function add(int $platform, string $referrer, array $params)
	{
		$date = getdate(Core::curtime());
		$date["seconds"] = 0;
		$date["minutes"] = 0;
		$date = mktime(/*hours*/$date["hours"], /*minutes*/$date["minutes"], /*seconds*/$date["seconds"], /*month*/$date["mon"], /*mday*/$date["mday"], /*year*/$date["year"]);

		Driver_MyDB::getInstance()->query(
			__METHOD__,
			Util_DbStorage::storage(__CLASS__),
			"INSERT INTO ".self::TABLE." (".self::FIELDS.") VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `params`=VALUES(`params`)",
			$date, $platform, $referrer, clsv_binary_serialize($params)
		);
	}

}
