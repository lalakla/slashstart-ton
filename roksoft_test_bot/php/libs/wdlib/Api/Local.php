<?php

namespace WDLIB;

class Api_Local extends Api_Base {

	public function __construct($data)
	{
		$data["platform"] = Util_Array::isset($data, "platform", \WDLIB\API_LOCAL);

		parent::__construct($data);
	}
}
