<?php

namespace WDLIB;

class Model_Session_Batch extends Model_Base {

	public ?Model_Session_Mem $mem = null;
	public ?Model_Session_Db $db = null;

	public function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();

		$this->close();
	}

	public function isValid() : bool
	{
		return ($this->mem && $this->db) && ($this->mem->isValid() && $this->db->isValid());
	}

	public function close()
	{
		$now = Core::curtime();

		// если сессия испортилась - стала !isValid() - все равно сохраняем её!
		// т.к. это единственное место - где она, сессия, сохраняется

		if($this->mem /*&& $this->mem->isValid()*/ && $this->mem->_changed) {
			$this->mem->date = $now;
			// save session
			if(Model_Session_Mem::update($this->mem) != \WDLIB\OK) {
				// error
				Logger::error(__METHOD__." : ERROR : Model_Session_Mem::update failed");
			}
		}
		if($this->db /*&& $this->db->isValid()*/ && $this->db->_changed) {
			$this->db->date = $now;
			// save session
			if(Model_Session_Db::update($this->db) != \WDLIB\OK) {
				// error
				Logger::error(__METHOD__." : ERROR : Model_Session_Db::update failed");
			}
		}

		$this->mem = null;
		$this->db = null;
	}

}
