<?php

namespace WDLIB;

class Model_Session_Db extends Model_Session_Base {

	const TABLE = "wdlib_user_auth_session";
	const FIELDS = "`key`, `user_id`, `fingerprint`, `data`, `date`, `status`, `ip`";

	public function __construct()
	{
		parent::__construct();
	}

	static public function selectByKey(string $key, $__item = null) : ?Model_Session_Db
	{
		$_item = ($__item) ? $__item : new Model_Session_Db;
		$_item->clear();

		$_item_ok = false;

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, Util_DbStorage::storage(__CLASS__), "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `key`=?", $key);

		if($rows->num_rows) {
			$_item->initFromRow($rows[0]);
			$_item_ok = true;
		}

		return $_item_ok ? $_item : null;
	}

	static public function update(Model_Session_Db $item) : int
	{
		$db = Driver_MyDB::getInstance();

		$rows = $db->query(__METHOD__, Util_DbStorage::storage(__CLASS__), "REPLACE INTO ".self::TABLE." (".self::FIELDS.") VALUES (?, ?, ?, ?, ?, ?, ?)",
			$item->key,
			$item->user_id,
			$item->fingerprint,
			clsv_binary_serialize($item->data),
			$item->date,
			$item->status,
			$item->ip
		);

		if($rows->error)  {
			// some error occured
			return ERROR_DB;
		}

		return OK;
	}
}
