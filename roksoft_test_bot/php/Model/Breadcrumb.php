<?php

class Model_Breadcrumb extends \WDLIB\Model_Base {

	const TYPE_DEFAULT = 0;

	public $uri = "";
	public $name = "";
	public $type = 0;

	public $last = FALSE;

	public function __construct(array $data = null)
	{
		parent::__construct();

		if(!empty($data)) {
			$this->init($data);
		}
	}

	public function init(array $data)
	{
		$this->uri = \WDLIB\Util_Array::isset($data, "uri", "");
		$this->name = \WDLIB\Util_Array::isset($data, "name", "");
		$this->type = \WDLIB\Util_Array::isset($data, "type", "");
	}
}
