<?php 

namespace WDLIB;

class Model_User_Auth extends Model_Base {

	const TABLE = "wdlib_user_auth";
	const FIELDS = "`id`, `login`, `email`, `password`";

	const __DB_STORAGE = "default";

	public $id = "0";
	public $login = "";
	public $email = "";
	public $password = "";

	public function __construct()
	{
		parent::__construct();
	}

	public function clear() : void
	{
		$this->id = "0";
		$this->login = "";
		$this->email = "";
		$this->password = "";
	}

	private function initFromRow(array $row) : void
	{
		$this->id = (string)$row["id"];
		$this->login = (string)$row["login"];
		$this->email = (string)$row["email"];
		$this->password = (string)$row["password"];
	}

	static public function selectById(string $id) : ?Model_User_Auth
	{
		$q = new \MyDB\Query;
		$q->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `id`=?";
		$q->values[] = $id;

		return self::_selectOne($q);
	}

	static public function selectByLogin(string $login) : ?Model_User_Auth
	{
		$q = new \MyDB\Query;
		$q->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `login`=?";
		$q->values[] = $login;

		return self::_selectOne($q);
	}

	static private function _selectOne(\MyDB\Query $q) : ?Model_User_Auth
	{
		$_item = null;

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, $q);

		if($rows->num_rows) {
			$_item = new Model_User_Auth;
			$_item->initFromRow($rows[0]);
		}

		return $_item;
	}

	static public function update(Model_User_Auth $item) : int
	{
		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, "REPLACE INTO ".self::TABLE." (".self::FIELDS.") VALUES (?, ?, ?, ?)",
			$item->id,
			$item->login,
			$item->email,
			$item->password
		);

		if($rows->error)  {
			// some error occured
			return ERROR_DB;
		}

		return OK;
	}
}
