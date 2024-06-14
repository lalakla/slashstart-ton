<?php

namespace WDLIB;

class Model_User_Confirm extends Model_Base {

	const TABLE = "`wdlib_user_confirm`";
	const FIELDS = "`key`, `id`, `type`, `status`, `date`";

	const __DB_STORAGE = "default";

	const STATUS_WAIT = 0;
	const STATUS_CONFIRMED = 1;

	public $key = "";
	public $id = "";
	public $type = 0;
	public $status = 0;
	public $date = 0;

	public function __construct()
	{
		parent::__construct();
	}

	public function clear() : void
	{
		$this->key = "";
		$this->id = "";
		$this->type = 0;
		$this->status = 0;
		$this->date = 0;
	}

	private function initFromRow(array $row) : void
	{
		$this->key = (string)$row["key"];
		$this->id = (string)$row["id"];
		$this->type = (int)$row["type"];
		$this->status = (int)$row["status"];
		$this->date = (int)$row["date"];
	}

	static public function selectByKey(string $key) : ?Model_User_Confirm
	{
		$retval = null;

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `key`=?", $key);

		if($rows->num_rows) {
			$retval = new Model_User_Confirm;
			$retval->initFromRow($rows[0]);
		}

		return $retval;
	}

	static public function update(Model_User_Confirm $item) : int
	{
		$db = Driver_MyDB::getInstance();
		$db->query(__METHOD__, self::__DB_STORAGE, "REPLACE INTO ".self::TABLE." (".self::FIELDS.") VALUES(?, ?, ?, ?, ?)", $item->key, $item->id, $item->type, $item->status, $item->date);

		return OK;
	}
}
