<?php

namespace WDLIB;

class Model_Config extends Model_Json {

	const TABLE = "`wdlib_config`";
	const FIELDS = "`key`, `value`, `is_server`, `comment`";

	const __DB_STORAGE = "default";

	public $key = "";
	public $value = 0;
	public $is_server = false;
	public $comment = "";

	public function __construct()
	{
		parent::__construct();
	}

	public function jsonSerialize() : array
	{
		return array(
			"key" => $this->key,
			"value" => $this->value
		);
	}

	public function crc() : int
	{
		$str = clsv_binary_serialize($this->value);
		return crc32($str);
	}

	public function getint() : int
	{
		return (int) $this->value;
	}
	public function getfloat() : float
	{
		return (float) $this->value;
	}
	public function getvector() : array
	{
		return (array)$this->value;
	}

	private function clear()
	{
		$this->key = "";
		$this->value = 0;
		$this->is_server = 0;
		$this->comment = "";
	}

	private function initFromRow(array $row)
	{
		$this->key = $row["key"];
		$this->value = clsv_binary_deserialize($row["value"]);
		$this->is_server = (int)$row["is_server"];
		$this->comment = $row["comment"];
	}
	
	static private $cache = array();

	static public function select() : array
	{
		$items = array();

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, "SELECT ".self::FIELDS." FROM ".self::TABLE);

		foreach($rows as $row) {
			$item = new Model_Config;
			$item->initFromRow($row);
			$items[] = $item;
		}

		return $items;
	}

	static public function selectOne(string $key, bool $create = false)
	{
		$item = null;

		// check cache first
		if(($item = Util_Array::isset(self::$cache, $key))) {
			return $item;
		}

		if($create) {
			$item = new Model_Config;
			$item->key = $key;
		}

		if($key) {

			$db = Driver_MyDB::getInstance();
			$rows = $db->query(__METHOD__, self::__DB_STORAGE, "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `key`=?", $key);

			if($rows->num_rows) {
				$item = ($item) ? $item : new Model_Config;
				$item->initFromRow($rows[0]);
			}
		}
			
		// set internal cache
		if($item && $item->key) {
			self::$cache[$item->key] = $item;
		}

		return $item;
	}

	static public function update(Model_Config $item) : int
	{
		$db = Driver_MyDB::getInstance();
		$res = $db->query(__METHOD__, self::__DB_STORAGE, "INSERT INTO ".self::TABLE." (".self::FIELDS.") VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), `is_server`=VALUES(`is_server`), `comment`=VALUES(`comment`)", $item->key, clsv_binary_serialize($item->value), $item->is_server, $item->comment);

		// set cache
		self::$cache[$item->key] = $item;
		
		return OK;
	}

	static public function remove(string $key)
	{
		$db = Driver_MyDB::getInstance();
		$res = $db->query(__METHOD__, self::__DB_STORAGE, "DELETE FROM ".self::TABLE." WHERE `key`=?", $key);

		// unset cache
		unset(self::$cache[$key]);
	}

}
