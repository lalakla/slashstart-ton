<?php

class Controller_Base extends \WDLIB\Controller_Base {

	protected $output = \WDLIB\OUTPUT_HTML;

	protected $browscap = null;
	protected $current_user = null;
	protected $current_session = null;
	protected $current_api_user = null;
	protected $api = null;

	public function __construct()
	{
		parent::__construct();

		$this->addMethod("noexist", array($this, "noexist"), true);

		$this->view = new View_Base;
		$this->view->HTTPS = true;
	}

	public function __destruct()
	{
		parent::__destruct();

		$this->browscap = null;
	}

	protected function preDispatch() : bool
	{
		$r = parent::preDispatch();
		if(!$r) {
			return $r;
		}

		// browscap detection
		if(!($ua = \WDLIB\Util_Array::isset($_SERVER, "HTTP_USER_AGENT"))) {
			// user-agent not found
			// нахуй такого хитрожопого!
			return false;
		}
		$this->browscap = get_browser(null, true);
		if($this->browscap === FALSE) {
			// browscap couldn't detect
			// нахуй такого хитрожопого!
			return false;
		}
		$this->view->browscap = $this->browscap;
		$this->view->is_mobile = (is_array($this->browscap) && ($dt = \WDLIB\Util_Array::isset($this->browscap, "device_type")) && (strpos($dt, "Mobile") !== FALSE));

		$this->view->HTTPS = true;
//		$this->view->HTTPS = $this->request->_is_https;

		return $r;
	}

	protected function postDispatch() : void
	{
		switch($this->output) {
			case \WDLIB\OUTPUT_RAW:
				break;
			case \WDLIB\OUTPUT_JSON:
				$this->view->templateJson();
				break;
			case \WDLIB\OUTPUT_HTML:
			default:
				$this->view->template("/index.phtml");
				break;
		}

		/*
		$log = new \WDLIB\Logger;
		$db = \WDLIB\Driver_MyDB::getInstance();
		$total = 0;
		$count = 0;
		foreach($db->profiler as $p) {
			echo "time: ".$p["time"]."\n";
			echo $p["q"]->query."\n";
			$total += $p["time"];
			$count ++;
		}
		echo "total: $total, count: $count\n";
		$log->stop();
		 */
	}

	protected function errorHandler(\Exception $e) : void
	{
		$this->view->data = array(
			"error" => $e->getMessage(),
			"error_code" => $e->getCode()
		);

		if($e instanceof \WDLIB\Model_Exception) {
			$this->view->data["error_data"] = $e->data;
		}

		$this->view->templateJson();

		if($e->getCode() == \WDLIB\ERROR_REDIRECT) {
			// не надо ничего логировать
			// это частое событие может быть!
			return;
		}

		$log = new \WDLIB\Logger;

		echo date("Y-m-d H:i:s")." ERROR[".$e->getCode()."]: ".$e->getMessage()."\n";
		echo "HTTP: ".$this->request->debug(true)."\n";
		echo "COOKIES: ".$this->request->cookies->debug(true)."\n";
		echo "X-REAL-IP : ".$_SERVER["X_REAL_IP"]."\n";
		echo "USER-AGENT: ".$_SERVER["HTTP_USER_AGENT"]."\n";
	}

	public function noexist() : void
	{
		$this->view->redirect($this->view->_url("/"));
	}

	// this checks if current_user is logged and allowed to this portal point
	protected function _private() : void
	{
		if(!$this->_isUserLogged()) {
			$this->view->redirect($this->view->_url("/index/index"));
		}
	}

	protected function _isUserLogged() : bool
	{
		return Service_User_User::isUserLogged($this->current_user);
	}
	protected function _isUserConfirmed() : bool
	{
		return Service_User_User::isUserConfirmed($this->current_user);
	}
	protected function _isUserModer() : bool
	{
		return Service_User_User::isUserModer($this->current_user);
	}
	protected function _isUserAdmin() : bool
	{
		return Service_User_User::isUserAdmin($this->current_user);
	}

	/**
	 * Loads users data
	 * @param Array $uids
	 * @param \WDLIB\Model_User $user1, $user2 ... this for already loaded users
	 * @return Array<\WDLIB\Model_User>
	 */
	protected function _checkUsers(array $uids)
	{
		// remove current user
		if($this->current_user) {
			unset($uids[$this->current_user->id]);
		}

		$users = array();

		$args = func_get_args();
		if(count($args) > 1) {
			// there is one ore more users
			for($i=1; $i<count($args); ++$i) {
				if($this->current_user->id == $args[$i]->id) continue;
				$users[] = $args[$i];
				unset($uids[$args[$i]->id]);
			}
		}

		if(count($uids)) {
			$_users = \WDLIB\Model_User::selectByIds($uids);
			$users = array_merge($users, $_users);
		}

		foreach($users as $user) {
			$this->view->users[$user->id] = $user;
		}

		$this->view->data["Users"] = $users;

		return $users;
	}

}
