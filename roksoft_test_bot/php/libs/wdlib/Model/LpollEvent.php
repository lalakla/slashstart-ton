<?php

namespace WDLIB;

class Model_LpollEvent extends \CLSV\Lpoll_Event {

	const TYPE_ECHO = "PollEchoEvent";
	const TYPE_BROADCAST = "PollBroadcastEvent";

	public $user_id = "";
	public $data = null;

	public function __construct(string $type)
	{
		parent::__construct();

		$this->_data["type"] = $type;
	}

	public function makeData() : array
	{
		$this->_data["user_id"] = $this->user_id;
		if($this->data) {
			$this->_data["data"] = $this->data;
		}
		return parent::makeData();
	}
}
