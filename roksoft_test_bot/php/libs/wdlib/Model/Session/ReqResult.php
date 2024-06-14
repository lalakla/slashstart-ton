<?php

namespace WDLIB;

class Model_Session_ReqResult extends Model_Base {

	const DEFAULT_LIMIT = 10;

	public $data = array();
	public $limit = self::DEFAULT_LIMIT;

	public function __construct(array $data, int $limit = self::DEFAULT_LIMIT)
	{
		$this->limit = $limit;

		if(!empty($data)) {
			$this->init($data);
		}
	}

	public function init(array $data)
	{
		$this->data = $data;
		$this->_checkLimit();
	}

	public function add(string $req_id, array $result)
	{
		$this->data[] = [$req_id, $result];
		$this->_checkLimit();
	}

	public function get(string $req_id) : ?array
	{
		$result = null;

		foreach($this->data as $v) {
			if($v[0] == $req_id) {
				$result = $v[1];
				break;
			}
		}

		return $result;
	}

	private function _checkLimit()
	{
		if(!empty($this->data) && count($this->data) > $this->limit) {
			array_splice($this->data, 0, count($this->data) - $this->limit);
		}
	}
}
