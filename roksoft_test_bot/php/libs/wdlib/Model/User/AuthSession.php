<?php

namespace WDLIB;

class Model_User_AuthSession extends Model_Base {

//	const TABLE = "wdlib_user_auth_session";
//	const FIELDS = "`key`, `user_id`, `fingerprint`, `data`, `date`, `status`, `ip`";

	const KEY_PREFIX = "sess.key.";
	const EXPIRE = 3600;

	const STATUS_INVALID = 0;
	const STATUS_VALID = 1;

	public $key = "";
	public $user_id = "0";
	public $fingerprint = "";
	public $data = array();
	public $date = 0;
	public $status = 0;
	public $ip = 0;

	public $_changed = false;

	public function __construct()
	{
		parent::__construct();
	}

	public function isValid() : bool
	{
		return $this->status == self::STATUS_VALID;
	}

	public function setIp(string $ip) : void
	{
		$this->ip = ip2long($ip);
	}
	public function getIp() : string
	{
		long2ip($this->ip);
	}
	public function dataExists(string $key, $def = null)
	{
		return \WDLIB\Util_Array::isset($this->data, $key, $def);
	}

	public function clear() : void
	{
		$this->key = "";
		$this->user_id = "0";
		$this->fingerprint = "";
		$this->data = array();
		$this->date = 0;
		$this->status = 0;
		$this->ip = 0;
	
		$this->_changed = false;
	}

	private function initFromRow(array $row) : void
	{
		$this->key = (string)$row["key"];
		$this->user_id = (string)$row["user_id"];
		$this->fingerprint = (string)$row["fingerprint"];
		$this->data = clsv_binary_deserialize($row["data"]);
		$this->date = (int)$row["date"];
		$this->status = (int)$row["status"];
		$this->ip = (int)$row["ip"];

		$this->_changed = false;
	}

	static public function selectByKey(string $key, $__item = null) : ?Model_User_AuthSession
	{
		$_item = ($__item) ? $__item : new Model_User_AuthSession;
		$_item->clear();

		$_item_ok = false;

		$mem = Driver_Memcached::getInstance();

		$sess_key = self::KEY_PREFIX.$key;
		if($data = $mem->get($sess_key)) {
			$_item->initFromRow($data);
			$_item_ok = true;
		}

//		$db = Driver_MyDB::getInstance();
//		$rows = $db->query(__METHOD__, Util_DbStorage::storage(__CLASS__), "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `key`=?", $key);

//		if($rows->num_rows) {
//			$retval = new Model_User_AuthSession;
//			$retval->initFromRow($rows[0]);
//		}

		return $_item_ok ? $_item : null;
	}

	static public function update(Model_User_AuthSession $item) : int
	{
		$mem = Driver_Memcached::getInstance();

		$sess_key = self::KEY_PREFIX.$item->key;
		$data = $mem->set($sess_key, array(
			"key" => $item->key,
			"user_id" => $item->user_id,
			"fingerprint" => $item->fingerprint,
			"data" => clsv_binary_serialize($item->data),
			"date" => $item->date,
			"status" => $item->status,
			"ip" => $item->ip
		), Core::curtime() + self::EXPIRE);

//		$db = Driver_MyDB::getInstance();
//		$rows = $db->query(__METHOD__, Util_DbStorage::storage(__CLASS__), "REPLACE INTO ".self::TABLE." (".self::FIELDS.") VALUES (?, ?, ?, ?, ?, ?, ?)",
//			$item->key,
//			$item->user_id,
//			$item->fingerprint,
//			clsv_binary_serialize($item->data),
//			$item->date,
//			$item->status,
//			$item->ip
//		);

//		if($rows->error)  {
//			// some error occured
//			return ERROR_DB;
//		}

		return OK;
	}
}
