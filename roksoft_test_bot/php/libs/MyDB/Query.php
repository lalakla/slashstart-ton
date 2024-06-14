<?php

namespace MyDB;

class Query {

	public $query = "";
	public $values = array();

	public $table = "";

	public $cached = FALSE;
	public $hideErrors = FALSE;

	public function __construct()
	{
		$args = func_get_args();
		call_user_func_array(array($this, "assign"), $args);
	}

	public function assign()
	{
		$this->clear();
		
		$args = func_get_args();
		if(count($args)) {
			$this->query = $args[0];
			for($i=1; $i<count($args); ++$i) {
				$this->values[] = $args[$i];
			}
		}
	}

	public function clear()
	{
		$this->query = "";
		$this->values = array();

		$this->cached = FALSE;
	}
};
