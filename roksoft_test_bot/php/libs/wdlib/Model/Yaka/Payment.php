<?php

namespace WDLIB;

class Model_Yaka_Payment extends Model_Base {

	const TABLE = "`wdlib_yaka_payment`";
	const FIELDS = "`seq_id`, `yaka_id`, `status`, `user_id`, `data`, `date`, `amount`";

	const __DB_STORAGE = "default";

	const AMOUNT_MULTIPLY = 100;

	const STATUS_NEW = 0;
	const STATUS_PENDING = 1;
	const STATUS_CHECKING = 5;
	const STATUS_CANCELED = 10;
	const STATUS_ERROR = 15;
	const STATUS_SUCCEEDED = 20;

	public $seq_id = 0;
	public $yaka_id = "";
	public $status = 0;
	public $user_id = "";
	public $data = array();
	public $date = 0;
	public $amount = 0;

	public $yp = null;

	public function __construct()
	{
		parent::__construct();
	}

	public function clear()
	{
		$this->seq_id = 0;
		$this->yaka_id = "";
		$this->status = 0;
		$this->user_id = "";
		$this->data = array();
		$this->date = 0;
		$this->amount = 0;
	}

	private function initFromRow(array $row) : void
	{
		$this->seq_id = (int)$row["seq_id"];
		$this->yaka_id = (string)$row["yaka_id"];
		$this->status = (int)$row["status"];
		$this->user_id = (string)$row["user_id"];
		$this->data = clsv_binary_deserialize($row["data"]);
		$this->date = (int)$row["date"];
		$this->amount = (int)$row["amount"];
	}

	static public function selectBySeq(int $seq_id) : ?Model_Yaka_Payment
	{
		$item = null;

		$q = new \MyDB\Query;
		$q->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `seq_id`=?";
		$q->values[] = $seq_id;

		$item = self::_selectOne($q);

		return $item;
	}
	static public function selectByYaka(string $yaka_id) : ?Model_Yaka_Payment
	{
		$item = null;

		$q = new \MyDB\Query;
		$q->query = "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `yaka_id`=?";
		$q->values[] = $yaka_id;

		$item = self::_selectOne($q);

		return $item;
	}

	static private function _selectOne(\MyDB\Query $q) : ?Model_Yaka_Payment
	{
		$item = null;

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, $q);

		if($rows->num_rows) {
			$item = new Model_Yaka_Payment;
			$item->initFromRow($rows[0]);
		}

		return $item;
	}

	static public function select(int $status, int $limit) : array
	{
		$items = array();

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `status`=? ORDER BY `date` ASC LIMIT $limit", $status);

		foreach($rows as $row) {
			$item = new Model_Yaka_Payment;
			$item->initFromRow($row);
			$items[] = $item;
		}

		return $items;
	}

	static public function update(Model_Yaka_Payment $item) : int
	{
		$db = Driver_MyDB::getInstance();
		$res = $db->query(__METHOD__, self::__DB_STORAGE, "REPLACE INTO ".self::TABLE." (".self::FIELDS.") VALUES(?, ?, ?, ?, ?, ?, ?)", $item->seq_id, $item->yaka_id, $item->status, $item->user_id, clsv_binary_serialize($item->data), $item->date, $item->amount);

		if(!$item->seq_id) {
			$item->seq_id = $res->inserted_id;
		}

		return OK;
	}
	
	static public function updateList(array $items) : int
	{
		if(empty($items)) {
			return OK;
		}

		$q = new \MyDB\Query;
		$q->query = "INSERT INTO ".self::TABLE." (".self::FIELDS.") VALUES ";

		$first = true;
		foreach($items as $item) {
			if($first) {$first=false;} else {$q->query .= ",";}
	
			$q->query .= "(?, ?, ?, ?, ?, ?, ?)";
			$q->values = array_merge($q->values, array($item->seq_id, $item->yaka_id, $item->status, $item->user_id, clsv_binary_serialize($item->data), $item->date, $item->amount));
		}

		$q->query .= " ON DUPLICATE KEY UPDATE `status`=VALUES(`status`), `data`=VALUES(`data`), `date`=VALUES(`date`)";
		
		$db = Driver_MyDB::getInstance();
		$res = $db->query(__METHOD__, self::__DB_STORAGE, $q);

		if($res->error) {
			return ERROR_DB;
		}

		return OK;
	}
}
