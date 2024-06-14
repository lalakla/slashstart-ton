<?php

namespace WDLIB;;

class Model_Email_Queue extends Model_Base {

	const FIELDS = "`id`, `to`, `type`, `status`, `subject`, `body`, `data`, `date`";
	const TABLE = "`wdlib_email_queue`";

	const STATUS_WAIT = 0;
	const STATUS_SENDING = 1;
	const STATUS_SENDED = 5;
	const STATUS_ERROR = 10;

	const __DB_STORAGE = "default";

	public $id = 0;
	public $to = "";
	public $type = 0;
	public $status = 0;
	public $subject = "";
	public $body = "";
	public $data = array();
	public $date = 0;

	public function __construct()
	{
		parent::__construct();
	}

	public function clear() : void
	{
		$this->id = 0;
		$this->to = "";
		$this->type = 0;
		$this->status = 0;
		$this->subject = "";
		$this->body = "";
		$this->data = array();
		$this->date = 0;
	}

	private function initFromRow(array $row) : void
	{
		$this->id = (int)$row["id"];
		$this->to = (string)$row["to"];
		$this->type = (int)$row["type"];
		$this->status = (int)$row["status"];
		$this->subject = (string)$row["subject"];
		$this->body = (string)$row["body"];
		$this->data = clsv_binary_deserialize($row["data"]);
		$this->date = (int)$row["date"];
	}
	
	static public function select(int $status, int $limit) : array
	{
		$items = array();

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, self::__DB_STORAGE, "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `status`=? ORDER BY `date` ASC LIMIT $limit", $status);

		foreach($rows as $row) {
			$item = new Model_Email_Queue;
			$item->initFromRow($row);
			$items[] = $item;
		}

		return $items;
	}

	static public function update(Model_Email_Queue $item) : int
	{
		$db = Driver_MyDB::getInstance();
		$res = $db->query(__METHOD__, self::__DB_STORAGE, "REPLACE INTO ".self::TABLE." (".self::FIELDS.") VALUES(?, ?, ?, ?, ?, ?, ?, ?)", $item->id, $item->to, $item->type, $item->status, $item->subject, $item->body, clsv_binary_serialize($item->data), $item->date);

		if(!$item->id) {
			$item->id = $res->inserted_id;
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
			
			$q->query .= "(?, ?, ?, ?, ?, ?, ?, ?)";
			$q->values = array_merge($q->values, array($item->id, $item->to, $item->type, $item->status, $item->subject, $item->body, clsv_binary_serialize($item->data), $item->date));
		}

		$q->query .= " ON DUPLICATE KEY UPDATE `status`=VALUES(`status`)";
		
		$db = Driver_MyDB::getInstance();
		$res = $db->query(__METHOD__, self::__DB_STORAGE, $q);

		if($res->error) {
			return ERROR_DB;
		}

		return OK;
	}

}
