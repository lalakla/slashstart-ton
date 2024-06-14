<?php

namespace WDLIB;

abstract class Model_Json extends Model_Base implements \JsonSerializable {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * save object properties to array, which convert to json then
	 * @return array
	 */
	abstract public function jsonSerialize();
}


