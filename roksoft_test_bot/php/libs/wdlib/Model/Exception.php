<?php

namespace WDLIB;

class Model_Exception extends \Exception {

	public $data = null;

	public function __construct($message, $code = \WDLIB\ERROR, $data = null, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->data = $data;
	}
}
