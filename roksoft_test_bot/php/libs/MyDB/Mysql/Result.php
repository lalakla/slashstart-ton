<?php

namespace MyDB;

class Mysql_Result extends Result {

	public $result = null;

	private $pos = 0;
	private $row = null;

	public function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		if($this->result) {
			$this->result->free();
		}
	}

	public function rewind()
	{
		if($this->result) {
			$this->result->data_seek(0);

			$this->pos = 0;
			$this->row = $this->result->fetch_assoc();
		}
	}
	public function current()
	{
		return $this->row;
	}
	public function key()
	{
		return $this->pos;
	}
	public function next()
	{
		if($this->result) {
			$this->pos++;
			$this->row = $this->result->fetch_assoc();
		}
	}
	public function valid()
	{
		return $this->result ? ($this->row) : FALSE;
	}

	public function offsetExists($offset) : bool
	{
		return (is_int($offset) && $offset < $this->num_rows) ? TRUE : FALSE;
	}
	public function offsetGet($offset)
	{
		$retval = null;

		if(is_int($offset) && $this->result && $offset < $this->num_rows) {
			$this->result->data_seek($offset);
			$this->pos = $offset;
			$this->row = $this->result->fetch_assoc();
			$retval = $this->row;
		}
	
		return $retval;
	}

}
