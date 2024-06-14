<?php

class View_Page extends View_Base {

	public $current_user = null;
	public $current_api_user = null;
	public $users = array();
	public $config = null;
	public $css = array();
	public $pageTitle = "";

	public function __construct()
	{
		parent::__construct();
	}

	// HELPER FUNCTIONS ----------------------------------------------------------

	public function _isUserLogged() : bool
	{
		return Service_User_User::isUserLogged($this->current_user);
	}
	public function _isUserConfirmed() : bool
	{
		return Service_User_User::isUserConfirmed($this->current_user);
	}
	public function _isUserAssistant() : bool
	{
		return Service_User_User::isUserAssistant($this->current_user);
	}
	public function _isUserAdmin() : bool
	{
		return Service_User_User::isUserAdmin($this->current_user);
	}
	public function _isUserModer() : bool
	{
		return Service_User_User::isUserModer($this->current_user);
	}

	public function _user(string $id) : ?\WDLIB\Model_User
	{
		if($this->current_user && $this->current_user->id == $id) {
			return $this->current_user;
		}
		return \WDLIB\Util_Array::isset($this->users, $id);
	}

}
