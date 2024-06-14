<?php

class Model_Config extends \WDLIB\Model_Json {

	const TEST_VALUE = 1;

	public function __construct()
	{
	}

	public function jsonSerialize() : array
	{
		return array(
			"test-value" => self::TEST_VALUE,
		);
	}

	static public function json() : array
	{
		$c = new Model_Config;
		return $c->jsonSerialize();
	}
}
