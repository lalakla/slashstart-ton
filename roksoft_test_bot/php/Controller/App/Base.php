<?php

class Controller_App_Base extends Controller_Base {

	protected $api_user_id = "";

	public function __construct()
	{
		parent::__construct();

		$this->output = \WDLIB\OUTPUT_JSON;
		
		$this->addMethod("test", array($this, "test"), true);
	}

	public function test()
	{
		$this->view->data["test"] = "test";
		//throw new Exception("ERROR", \WDLIB\ERROR);
	}

	protected function preDispatch() : bool
	{
//		\WDLIB\Logger::log("HTTP: ".$this->request->debug(true));
//		\WDLIB\Logger::log("User-Agent : ".\WDLIB\Util_Array::isset($_SERVER, "HTTP_USER_AGENT"));

		$r = parent::preDispatch();
		if(!$r) {
			return $r;
		}
		
		// set ACCESS-CONTROL-ALLOW-ORIGIN
		// не помню, для чего это точно надо - но что-то помнится было, чтобы долбанный CORS работал ...
//		$origin = \WDLIB\Util_Array::isset($_SERVER, "HTTP_ORIGIN", "null");
//		header("Access-Control-Allow-Origin: $origin");
		// а вот ФФ ругнулся на тесте, мол multiple access-control-allow-origin ...
		// потому как в nginx'е выставлено это тоже в *
		// поэтому закоментировал - но оставляю, т.к. мало ли какая хуета будет, потребуется ещё ...

		$api_user_id = \WDLIB\Util_Validate_Param::getNum($this->request->getstr("viewer_id"), 64);
		$api_platform = $this->request->getint("viewer_platform");

		// check api existance by viewer_platform
		$this->api = \WDLIB\Api_Storage::getInstance()->getApi($api_platform);
		if(!$this->api) {
			throw new Exception("API NOT FOUND, viewer_platform=$viewer_platform", \WDLIB\ERROR);
		}

		$payload = array();

		if(
			!($this->api_user_id = Service_User_Auth::checkToken($this->request, $this->request->getstr("sess_token"), $payload))
			|| ($this->api_user_id !== $api_user_id)
		) {
			throw new Exception("SESSION ERROR : sess_token incorrect", \WDLIB\ERROR_AUTH);
		}

		if(!Service_User_Auth::tryAuthorize($this->request, $payload, $this->api, $this->current_api_user, $this->current_user, $this->current_session)) {
			$this->current_user = null;
			$this->current_api_user = null;
			$this->current_session = null;
			throw new Exception("SESSION ERROR: authorize failed", \WDLIB\ERROR_AUTH);
		}

		return true;
	}
	
	protected function postDispatch() : void
	{
		ob_start();

		$this->view->templateJson();

		$content = ob_get_contents();
		ob_end_clean();

		$now = \WDLIB\Core::curtime();

		$this->view->data = array(
			"data" => $content,
			"server_timestamp" => $now
		);
		$this->view->templateJson();
	}

	protected function _getRequestUserId() : string
	{
		$user_id = $this->request->getstr("user_id", $this->api_user_id);
		return $user_id;
	}

	protected function _checkCurrentUser()
	{
		if(!$this->current_user) {
			return;
		}
		
		$this->view->data["User"] = $this->current_user->jsonSerialize();
	}

}
