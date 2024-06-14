<?php

namespace WDLIB;

class Model_Session_Base extends Model_Base {

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

	public function initFrom(Model_Session_Base $s)
	{
		$this->key = $s->key;
		$this->user_id = $s->user_id;
		$this->fingerprint = $s->fingerprint;
		$this->data = $s->data;
		$this->date = $s->date;
		$this->status = $s->status;
		$this->ip = $s->ip;
	}

	public function updateSelf() : int
	{
		return \WDLIB\ERROR;
	}

	protected function initFromRow(array $row) : void
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

	protected function dataSerialize() : array
	{
		return $this->data;
	}
}
