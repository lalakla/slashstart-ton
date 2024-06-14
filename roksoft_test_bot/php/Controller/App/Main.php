<?php

class Controller_App_Main extends Controller_App_Base {

	public function __construct()
	{
		parent::__construct();

		$this->addMethod("init", array($this, "init"));
		$this->addMethod("run", array($this, "run"));
		$this->addMethod("poll_echo", array($this, "pollEcho"));
		$this->addMethod("poll_broadcast", array($this, "pollBroadcast"));
	}

	public function init()
	{
		\WDLIB\Logger::log("app.main.init: ".$this->request->debug(true));

		$var = $this->request->getstr("name");
		if($var) {
			// limit name length
			$var = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $var);
//			$this->current_user->name = mb_substr($var, 0, 64);
		}
		$var = $this->request->getint("sex");
		if($var == \WDLIB\MALE || $var == \WDLIB\FEMALE) {
//			$this->current_user->sex = $var;
		}
		$var = $this->request->getint("age");
		if($var > 0 && $var <= 127) {
//			$this->current_user->age = $var;
		}

		// pic
		$var = \WDLIB\Util_Validate_Param::getImgUrl($this->request->getstr("pic"));
		if($var) {
//			$this->current_user->pic = $var;
		}

//		\WDLIB\Model_User::update($this->current_user);

//		Service_Stats::add($this->current_user, "init");

		$this->_checkCurrentUser();
	}
	public function run()
	{
		\WDLIB\Logger::log("app.main.check: ".$this->request->debug(true));

		// check UserInfo
//		$info = Model_UserInfo::select($this->current_user->id,
//			Model_UserInfo::LAST_TASK_PUB
//		);
//		$this->view->data["UserInfo"] = $info->values;

//		Service_Stats::add($this->current_user, "run");

		$this->_checkCurrentUser();
	}

	public function pollEcho()
	{
//		$log = new \WDLIB\Logger;
//		echo "HTTP: ".$this->request->debug()."\n";

		$event = new Model_LpollEvent(Model_LpollEvent::TYPE_ECHO);
		$event->addUid($this->current_user->id);
		$event->user_id = $this->current_user->id;
		$event->data = array("lalakla" => "ololo");

		$lpoll = \WDLIB\Driver_Lpoll::getInstance();
		$lpoll->event($event);
	}
	
	public function pollBroadcast()
	{
		$event = new \CLSV\Lpoll_Event;
		$event->addUid($this->current_user->id);
		$event->broadcast = true;
		$event->data = array(
			"type" => "PollBroadcastEvent",
			"data" => array("some event data", "lalakla", "user_id"=>$this->current_user->id)
		);

		$lpoll = \WDLIB\Driver_Lpoll::getInstance();
		$lpoll->event($event);
	}

}
