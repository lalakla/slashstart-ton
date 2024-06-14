<?php

namespace WDLIB;

class Model_Payment extends Model_Json {

	const TABLE = "`wdlib_user_payments`";
	const FIELDS = "`seq_id`, `user_id`, `platform`, `api_user_id`, `platform_order_id`, `status`, `amount`, `wallet_type`, `wallet_count`, `date`, `data`";

	const STATUS_OPEN = 0;
	const STATUS_OK = 1;
	const STATUS_ERROR = -1;

	public $seq_id = 0;
	public $user_id = "";
	public $api_user_id = "";
	public $platform = 0;
	public $platform_order_id = "";
	public $status = 0;
	public $amount = 0;
	public $wallet_type = 0;
	public $wallet_count = 0;
	public $date = 0;
	public $data = 0;

	public function __construct()
	{
		parent::__construct();
	}

	public function init(string $user_id, int $platform, string $api_user_id, int $status, string $order_id, int $amount, int $wallet_type, int $wallet_count, array $data)
	{
		$this->user_id = $user_id;
		$this->platform = $platform;
		$this->api_user_id = $api_user_id;
		$this->platform_order_id = $order_id;
		$this->status = $status;
		$this->amount = $amount;
		$this->wallet_type = $wallet_type;
		$this->wallet_count = $wallet_count;
		$this->data = $data;
	}

	public function clear()
	{
		$this->seq_id = 0;
		$this->user_id = "";
		$this->platform = 0;
		$this->api_user_id = "";
		$this->platform_order_id = "";
		$this->status = 0;
		$this->amount = 0;
		$this->wallet_type = 0;
		$this->wallet_count = 0;
		$this->date = 0;
		$this->data = 0;
	}

	public function initFromRow(array $row)
	{
		$this->seq_id = (int)$row["seq_id"];
		$this->user_id = (string)$row["user_id"];
		$this->platform = (int)$row["platform"];
		$this->api_user_id = (string)$row["api_user_id"];
		$this->platform_order_id = (string)$row["platform_order_id"];
		$this->status = (int)$row["status"];
		$this->amount = (int)$row["amount"];
		$this->wallet_type = (int)$row["wallet_type"];
		$this->wallet_count = (int)$row["wallet_count"];
		$this->date = (int)$row["date"];
		$this->data = clsv_binary_deserialize($row["data"]);
	}

	public function jsonSerialize() : array
	{
		return array(
			"seq_id" => $this->seq_id,
			"user_id" => $this->user_id,
			"platform" => $this->platform,
			"api_user_id" => $this->api_user_id,
			"platform_order_id" => $this->platform_order_id,
			"status" => $this->status,
			"amount" => $this->amount,
			"wallet_type" => $this->wallet_type,
			"wallet_count" => $this->wallet_count,
			"date" => $this->date,
			"data" => json_encode($this->data)
		);
	}

	static public function selectByUserId(string $user_id, int $limit = 0, int $offset = 0) : array
	{
		$retval = array();
		
		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `user_id`=? ORDER BY `date` DESC ".($limit ? ("LIMIT $offset,$limit") : ""), $user_id);
		if($rows->num_rows) {
			foreach($rows as $row) {
				$payment = new Model_Payment;
				$payment->initFromRow($row);
				$retval[] = $payment;
			}
		}

		return $retval;
	}
	static public function selectByUserIdAndDate(string $user_id, int $date)
	{
		$retval = array();
		
		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `user_id`=? AND `date`>? ORDER BY `date` DESC", $user_id, $date);
		if($rows->num_rows) {
			foreach($rows as $row) {
				$payment = new Model_Payment;
				$payment->initFromRow($row);
				$retval[] = $payment;
			}
		}

		return $retval;
	}

	static public function select(int $platform, string $platform_order_id)
	{
		$payment = null;

		$db = Driver_MyDB::getInstance();
		$rows = $db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `platform`=? AND `platform_order_id`=? LIMIT 1", $platform, $platform_order_id);
		if($rows->num_rows) {
			$payment = new Model_Payment;
			$payment->initFromRow($rows[0]);
		}
		return $payment;
	}

	static public function add(Model_Payment $payment, bool $check_duplicate = true) : int
	{
		$db = Driver_MyDB::getInstance();

		$nd = getdate();

		if($check_duplicate) {
			$found = false;
			$rows = $db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), "SELECT ".self::FIELDS." FROM ".self::TABLE." WHERE `platform`=? AND `platform_order_id`=?", $payment->platform, $payment->platform_order_id);
			foreach($rows as $row) {
				$p = new Model_Payment;
				$p->init($row);

				// check year
				$d = getdate($p->date);
				if($d["year"] == $nd["year"]) {
					$found = true;
					break;
				}
			}
			if($found) {
				return \WDLIB\ERROR_ALREADY;
			}
		}

		$payment->date = \WDLIB\Core::curtime();

		// add payment
		$rows = $db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), "INSERT INTO ".self::TABLE." (".self::FIELDS.") VALUES (0,?,?,?,?,?,?,?,?,?,?)", $payment->user_id, $payment->platform, $payment->api_user_id, $payment->platform_order_id, $payment->status, $payment->amount, $payment->wallet_type, $payment->wallet_count, $payment->date, clsv_binary_serialize($payment->data));

		$payment->seq_id = $rows->inserted_id;

		return \WDLIB\OK;
	}
	
	static public function update(Model_Payment $payment) : int
	{
		$db = Driver_MyDB::getInstance();

		$payment->date = \WDLIB\Core::curtime();

		// update payment
		$rows = $db->query(__METHOD__, \WDLIB\Util_DbStorage::storage(__CLASS__), "UPDATE INTO ".self::TABLE." SET `status`=?, `platform_order_id`=?, `wallet_type`=?, `wallet_count`=?, `date`=? WHERE `seq_id`=?", $payment->status, $payment->platform_order_id, $payment->wallet_type, $payment->wallet_count, $payment->date, $payment->seq_id);

		return \WDLIB\OK;
	}
}
