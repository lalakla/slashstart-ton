<?php 

namespace WDLIB;

class Model_User extends Model_Json {

	const TABLE = "wdlib_user_main";
	const FIELDS = "`id`, `name`, `age`, `sex`, `pic`, `flags`, `level`, `reg_date`, `last_date`, `reg_ip`, `last_ip`, `last_api_platform`, `last_api_user_id`, `big_pic`, `city`, `anketa_link`, `birthday`, `birthday_date`, `zodiac`";

	const __DB_STORAGE = "default";

	public $id = "0";
	public $name = "";
	public $sex = 0;
	public $age = 0;
	public $pic = "";
	public $flags = 0;
	public $level = 0;
	public $reg_date = 0;
	public $last_date = 0;
	public $reg_ip = 0;
	public $last_ip = 0;
	public $last_api_platform = 0;
	public $last_api_user_id = "0";
	public $big_pic = "";
	public $city = "";
	public $anketa_link = "";
	public $birthday = "";
	public $birthday_date = 0;
	public $zodiac = 0;

	public function __construct()
	{
		parent::__construct();
	}

	public function jsonSerialize() : array
	{
		$retval = array(
			"id" => $this->id,
			"name" => $this->name,
			"sex" => $this->sex,
			"age" => $this->age,
			"pic" => $this->pic,
			"big_pic" => $this->big_pic,
			"city" => $this->city,
			"flags" => $this->flags,
			"level" => $this->level,
			"reg_date" => $this->reg_date,
			"last_date" => $this->last_date,
			"birthday" => $this->birthday,
			"birthday_date" => $this->birthday_date,
			"zodiac" => $this->zodiac,
		);
		
		return $retval;
	}
	public function clear() : void
	{
		$this->id = "0";
		$this->name = "";
		$this->sex = 0;
		$this->age = 0;
		$this->pic = "";
		$this->flags = 0;
		$this->level = 0;
		$this->reg_date = 0;
		$this->last_date = 0;
		$this->reg_ip = 0;
		$this->last_ip = 0;
		$this->last_api_platform = 0;
		$this->last_api_user_id = "0";
		$this->big_pic = "";
		$this->city = "";
		$this->anketa_link = "";
		$this->birthday = "";
		$this->birthday_date = 0;
		$this->zodiac = 0;
	}

	protected function _initFrom(Model_Base $user) : void
	{
		parent::_initFrom($user);

		if($user instanceof Model_User) {

			$this->id = $user->id;
			$this->name = $user->name;
			$this->sex = $user->sex;
			$this->age = $user->age;
			$this->pic = $user->pic;
			$this->flags = $user->flags;
			$this->level = $user->level;
			$this->reg_date = $user->reg_date;
			$this->last_date = $user->last_date;
			$this->reg_ip = $user->reg_ip;
			$this->last_ip = $user->last_ip;
			$this->last_api_platform = $user->last_api_platform;
			$this->last_api_user_id = $user->last_api_user_id;
			$this->big_pic = $user->big_pic;
			$this->city = $user->city;
			$this->anketa_link = $user->anketa_link;
			$this->birthday = $user->birthday;
			$this->birthday_date = $user->birthday_date;
			$this->zodiac = $user->zodiac;
		}
	}

	private function initFromRow(array $row) : void
	{
		$this->id = (string)Util_Array::isset($row, "id", "0");
		$this->name = (string)Util_Array::isset($row, "name", "");
		$this->sex = (int)Util_Array::isset($row, "sex", 0);
		$this->age = (int)Util_Array::isset($row, "age", 0);
		$this->pic = (string)Util_Array::isset($row, "pic", "");
		$this->flags = (int)Util_Array::isset($row, "flags", 0);
		$this->level = (int)Util_Array::isset($row, "level", 0);
		$this->reg_date = (int)Util_Array::isset($row, "reg_date", 0);;
		$this->last_date = (int)Util_Array::isset($row, "last_date", 0);
		$this->reg_ip = (int)Util_Array::isset($row, "reg_ip", 0);
		$this->last_ip = (int)Util_Array::isset($row, "last_ip", 0);;
		$this->last_api_platform = (int)Util_Array::isset($row, "last_api_platform", 0);
		$this->last_api_user_id = (string)Util_Array::isset($row, "last_api_user_id", "0");
		$this->big_pic = (string)Util_Array::isset($row, "big_pic", "");
		$this->city = (string)Util_Array::isset($row, "city", "");
		$this->anketa_link = (string)Util_Array::isset($row, "anketa_link", "");
		$this->birthday = (string)Util_Array::isset($row, "birthday", "");
		$this->birthday_date = (int)Util_Array::isset($row, "birthday_date", 0);
		$this->zodiac = (int)Util_Array::isset($row, "zodiac", 0);
	}

	static private $cache = array();

	static public function selectById(string $id, /*Model_User*/$__item = null, $_no_cache = false) : ?Model_User
	{
		$_item = ($__item) ? $__item : new Model_User;
		$_item->clear();

		// check internal cache first
		if(!$_no_cache && ($_data =& Util_Array::isset(self::$cache, $id))) {
			$_item->_initFrom($_data);
			return $_item;
		}

		$_item_ok = false;

		// @TODO check Memcache here

		if(!$_item_ok) {
			$_item->clear();

			$db = Driver_MyDB::getInstance();
			$rows = $db->query(__METHOD__, self::__DB_STORAGE, "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `id`=?", $id);

			if($rows->num_rows) {
				$_item->initFromRow($rows[0]);

				// @TODO save to Memcache here

				$_item_ok = true;
			}
		}

		if($_item_ok) {
			// set internal cache
			self::$cache[$_item->id] = $_item;
		}

		return $_item_ok ? $_item : null;
	}

	static public function selectByIds(array $uids) : array
	{
		$users = array();
		$_uids = array();

		// try internal cache
		if($uids && count($uids)) {
			// check cache first
			foreach($uids as $id => $v) {
				if(!$id || $id === "0" || empty($id)) {
					continue;
				}

				if(array_key_exists($id, self::$cache)) {
					$users[] = self::$cache[$id];
					continue;
				}
				$_uids[$id] = 1;
			}
		}

		// @TODO check Memcache here

		if(count($_uids)) {
			// check db

			$query = new \MyDB\Query;
			$query->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `id` IN (";
			$first = true;
			foreach($_uids as $id => $v) {
				if($first) {$first=false;} else {$query->query .= ",";}
				$query->query .= "?";
				$query->values[] = $id;
			}
			$query->query .= ")";

			$db = Driver_MyDB::getInstance();
			$rows = $db->query(__METHOD__, self::__DB_STORAGE, $query);

			if($rows->num_rows) {
				
				$cacher_values = array();
				$cacher_remote_values = array();

				foreach($rows as $row) {
					$item = new Model_User;
					$item->initFromRow($row);

					$users[] = $item;

					// set cache
					self::$cache[$item->id] = $item;
				}

				// @TODO save to Memcache here
			}
		}

		return $users;
	}

	static public function update(Model_User $item) : int
	{
		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, "REPLACE INTO ".self::TABLE." (".self::FIELDS.") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
			$item->id,
			$item->name,
			$item->age,
			$item->sex,
			$item->pic,
			$item->flags,
			$item->level,
			$item->reg_date,
			$item->last_date,
			$item->reg_ip,
			$item->last_ip,
			$item->last_api_platform,
			$item->last_api_user_id,
			$item->big_pic,
			$item->city,
			$item->anketa_link,
			$item->birthday,
			$item->birthday_date,
			$item->zodiac,
		);

		if($rows->error)  {
			// some error occured
			return ERROR_DB;
		}

		if(!$item->id || $item->id == "0") {
			$item->id = (string)$rows->inserted_id;
		}

		// @TODO save to Memcache here

		// set internal cache
		self::$cache[$item->id] = $item;

		return OK;
	}
}
