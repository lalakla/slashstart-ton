<?php 

namespace WDLIB;

class Model_User_ApiAuth extends Model_Base {

	const TABLE = "wdlib_user_api_auth";
	const FIELDS = "`platform`, `api_user_id`, `user_id`, `is_app_user`, `reg_date`, `last_date`, `reg_ip`, `last_ip`";

	const __DB_STORAGE = "default";

	public $platform = \WDLIB\API_LOCAL;
	public $api_user_id = "";
	public $user_id = "";
	public $is_app_user = 0;
	public $reg_date = 0;
	public $last_date = 0;
	public $reg_ip = 0;
	public $last_ip = 0;

	public function __construct()
	{
		parent::__construct();
	}

	public function jsonSerialize() : array
	{
		$retval = array(
			"platform" => $this->platform,
			"api_user_id" => $this->api_user_id,
			"user_id" => $this->user_id,
			"is_app_user" => $this->is_app_user,
			"reg_date" => $this->reg_date,
			"last_date" => $this->last_date
		);
		
		return $retval;
	}

	public function clear() : void
	{
		$this->platform = \WDLIB\API_LOCAL;
		$this->api_user_id = "0";
		$this->user_id = "0";
		$this->is_app_user = 0;
		$this->reg_date = 0;
		$this->last_date = 0;
		$this->reg_ip = 0;
		$this->last_ip = 0;
	}

	private function initFromRow(array $row) : void
	{
		$this->platform = (int)$row["platform"];
		$this->api_user_id = (string)$row["api_user_id"];
		$this->user_id = (string)$row["user_id"];
		$this->is_app_user = (int)$row["is_app_user"];
		$this->reg_date = (int)$row["reg_date"];
		$this->last_date = (int)$row["last_date"];
		$this->reg_ip = (int)$row["reg_ip"];
		$this->last_ip = (int)$row["last_ip"];
	}

	static public function selectLastAdded(int $platform, int $is_app_user, int $limit, int $offset = 0) : array
	{
		$users = array();

		$query = new \MyDB\Query;
		$query->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." FORCE INDEX(`Index_1`)";

		$where = "";

		if($platform != -1) {
			$where .= (!empty($where)?" AND ":"")."`platform`=?";
			$query->values[] = $platform;
		}
		if($is_app_user != -1) {
			$where .= (!empty($where)?" AND ":"")."`is_app_user`=?";
			$query->values[] = $is_app_user;
		}

		if(!empty($where)) {
			$query->query .= " WHERE ".$where;
		}

		$query->query .= " ORDER BY `user_id` DESC LIMIT $offset,$limit";

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, $query);

		foreach($rows as $row) {
			$item = new Model_User_ApiAuth;
			$item->initFromRow($row);

			$users[] = $item;
		}

		return $users;
	}

	static public function selectOne(int $platform, string $api_user_id) : ?Model_User_ApiAuth
	{
		$_item = null;

		$q = new \MyDB\Query;
		$q->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `platform`=? AND `api_user_id`=?";
		$q->values[] = $platform;
		$q->values[] = $api_user_id;

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, $q);

		if($rows->num_rows) {
			$_item = new Model_User_ApiAuth;
			$_item->initFromRow($rows[0]);
		}

		return $_item;
	}

	static public function selectForUser(string $user_id) : array
	{
		$retval = array();

		$q = new \MyDB\Query;
		$q->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `user_id`=?";
		$q->values[] = $user_id;

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, $q);

		foreach($rows as $row) {
			$item = new Model_User_ApiAuth;
			$item->initFromRow($row);

			$retval[$item->platform] = $item;
		}

		return $retval;
	}

	static public function update(Model_User_ApiAuth $item) : int
	{
		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, "REPLACE INTO ".self::TABLE." (".self::FIELDS.") VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
			$item->platform,
			$item->api_user_id,
			$item->user_id,
			$item->is_app_user,
			$item->reg_date,
			$item->last_date,
			$item->reg_ip,
			$item->last_ip,
		);

		if($rows->error)  {
			// some error occured
			return ERROR_DB;
		}

		return OK;
	}
}
