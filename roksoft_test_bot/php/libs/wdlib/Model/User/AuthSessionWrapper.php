<?php

namespace WDLIB;

class Model_User_AuthSessionWrapper extends Model_Base {

	public ?Model_User_AuthSession $sess;

	public function __construct(?Model_User_AuthSession $sess)
	{
		$this->sess = $sess;
	}

	public function __destruct()
	{
		$this->close();
	}

	public function close()
	{
		if($this->sess && $this->sess->isValid() && $this->sess->_changed) {
			$this->sess->date = \WDLIB\Core::curtime();
			// save session
			if(\WDLIB\Model_User_AuthSession::update($this->sess) != \WDLIB\OK) {
				// error
				\WDLIB\Logger::error(__METHOD__." : ERROR : Model_User_AuthSession::update failed");
			}
		}

		$this->sess = null;
	}
}
