<?php

namespace WDLIB;

class Model_Session_Wrapper extends \WDLIB\Model_Base {

	public ?Model_Session_Base $sess = null;

	public function __construct(?Model_Session_Base $sess = null)
	{
		parent::__construct();

		if($sess) {
			$this->sess = $sess;
		}
	}

	public function __destruct()
	{
		parent::__destruct();

		$this->close();
	}

	public function isValid() : bool
	{
		return ($this->sess) && ($this->sess->isValid());
	}

	public function close()
	{
		$now = \WDLIB\Core::curtime();

		if($this->sess && $this->sess->_changed) {
			$this->sess->date = $now;
			// save session
			if($this->sess->updateSelf() != \WDLIB\OK) {
				// error
				\WDLIB\Logger::error(__METHOD__." : ERROR : Model_Session_*::update failed");
			}
		}

		$this->sess = null;
	}
}
