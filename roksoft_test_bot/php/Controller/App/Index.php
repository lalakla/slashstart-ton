<?php

class Controller_App_Index extends Controller_Base {

	public function __construct()
	{
		parent::__construct();

		$this->addMethod("simple", array($this, "simple"), true);
		$this->addMethod("telegram", array($this, "telegram"));
		$this->addMethod("init-session", array($this, "initSession"));

		$this->view = new View_Page;
		$this->output = \WDLIB\OUTPUT_RAW;
	}

	protected function errorHandler(Exception $e) : void
	{
		$this->view->data = array(
			"error" => $e->getMessage(),
			"error_code" => $e->getCode()
		);
		$this->view->templateJson();

		$log = new \WDLIB\Logger;

		echo "ERROR[".$e->getCode()."]: ".$e->getMessage()."\n";
		print_r($this->request);
	}

	public function simple() : void
	{
		$this->view->template("simple.phtml");
	}
	
	public function telegram()
	{
		// CHECK FOR API -----------------------------------------
		$this->api = \WDLIB\Api_Storage::getInstance()->getApi(\WDLIB\API_TELEGRAM);
		if(!$this->api) {
			throw new Exception("TELEGRAM API NOT FOUND", \WDLIB\ERROR);
		}
		// в Телеге здесь никаких данных на проверку не передается!
		// все проверки будут в Main.init
		// -------------------------------------------------------
		
		// никакого User ID тут тоже не передается

		// ставим режим мобилки в любом случае
		$this->is_mobile = true;

		$this->addCommonInfo();

		$this->view->template("app/app.phtml");
	}

	public function initSession()
	{
		\WDLIB\Logger::log("INIT-SESSION HTTP: ".$this->request->debug(true));

		$log = null;
		//$log = new \WDLIB\Logger;

		$this->is_mobile = $this->request->getint("is_mobile");

		// CHECK FOR API -----------------------------------------
		$api = $this->request->getint("api");

		$this->api = \WDLIB\Api_Storage::getInstance()->getApi($api);
		if(!$this->api) {
			throw new Exception("API NOT FOUND : api=$api", \WDLIB\ERROR);
		}

		$vars = $this->request->getjson("vars");

		if($this->api->platform == \WDLIB\API_VK) {

			// это для vk-mini-app
			// т.к. параметры отличаются от тех, что для игр приходят !!!
			// самостоятельно формируем sign_keys
			$sign_params = array();
			foreach($vars as $name => $value) {
				if (strpos($name, 'vk_') !== 0) { // Получаем только vk параметры из query
					continue;
				}
				$sign_params[$name] = $value;
			}
			ksort($sign_params);
			$sign_keys = join(',', array_keys($sign_params));
			$vars["sign_keys"] = $sign_keys;

			$api_user_id = \WDLIB\Util_Validate_Param::getNum(\WDLIB\Util_Array::isset($vars, "vk_user_id"), 64);
		}
		else {
			$api_user_id = \WDLIB\Util_Validate_Param::getNum($this->request->getstr("api_user_id"), 64);
		}

		if($this->api->platform == \WDLIB\API_TELEGRAM) {
			$vars = $this->request->getjson("auth_params");
			$this->is_mobile = true;
		}

		if(!$this->api->checkVisit($vars)) {
			throw new Exception("AUTH ERROR", \WDLIB\ERROR_AUTH);
		}
		
		// -------------------------------------------------------

		if(!$api_user_id) {
			throw new Exception("API USER ID INCORRECT", \WDLIB\ERROR_AUTH);
		}

		$this->addCommonInfo();

		$_is_app_user = $this->request->getint("is_app_user");
		$this->checkUserInfo($api_user_id, $_is_app_user);

		if($log) {
			$log->stop();
		}

		$this->view->templateJson();
	}
	
	private function checkUserInfo($api_user_id, $_is_app_user)
	{
//		$log = new \WDLIB\Logger;
//		echo "CHECK USER INFO\n";

		$now = \WDLIB\Core::curtime();
		$ip = ip2long($this->request->getenv("X_REAL_IP"));

		$user = null;
		$api_user = \WDLIB\Model_User_ApiAuth::selectOne($this->api->platform, $api_user_id);

		if(!$api_user || !($user = \WDLIB\Model_User::selectById($api_user->user_id))) {
			// пользователь зашел через приложение ВК/ОК/...
			// создаем нового сразу !!!
			// подтверждение будет после запроса инит от js клиента

			$api_user = new \WDLIB\Model_User_ApiAuth;
			$api_user->platform = $this->api->platform;
			$api_user->api_user_id = $api_user_id;
			$api_user->is_app_user = $_is_app_user;
			$api_user->reg_date = $now;
			$api_user->last_date = $now;
			$api_user->reg_ip = $ip;
			$api_user->last_ip = $ip;

			$errors = array();
			$user_data = array();
			if(!($user = Service_User_User::registerByApi($this->api, $api_user, $user_data, $errors))) {
				throw new Exception("AUTH ERROR", \WDLIB\ERROR_AUTH);
			}
		}

		// check & update api_user & user
		if($api_user->is_app_user != $_is_app_user) {
			$api_user->is_app_user = $_is_app_user;
		}

		$user->last_date = $now;
		$user->last_ip = $ip;
		$user->last_api_platform = $api_user->platform;
		$user->last_api_user_id = $api_user->api_user_id;
		\WDLIB\Model_User::update($user);

		$api_user->last_date = $now;
		$api_user->last_ip = $ip;
		\WDLIB\Model_User_ApiAuth::update($api_user);

		$this->current_user = $user;
		$this->current_api_user = $api_user;

		// пользователь есть, подпись проверена - создаем сессию
		if(!($token = Service_User_Auth::login($this->request, $this->api, $this->current_api_user, $this->current_session)) || !$this->current_session->isValid()) {
			// ошибка какая-то ...
			$this->current_user = null;
			$this->current_api_user = null;
			$this->current_session = null;
			throw new Exception("AUTH ERROR", \WDLIB\ERROR_AUTH);
		}

		$this->view->data["sess_token"] = $token;

		$this->view->api = $this->api;
		$this->view->api_user_id = $api_user_id;

		// прописываем платформу в UserInfo
		Model_UserInfo::set($this->current_user->id, Model_UserInfo::USER_LAST_PLATFORM, $this->current_api_user->platform);
		
//		if($this->api->platform == \WDLIB\API_TELEGRAM) {
//			// загружаем данные юзера из телеги
//			$tt = \WDLIB\Driver_Transmitter::getInstance();
//			$tt->add(\WDLIB\Core::config("local_url")."/local.telegram/photos?api-user-id={$this->current_api_user->api_user_id}");
//		}

		Service_Stats::add($this->current_user, "visit");
	}
	
	private function addCommonInfo()
	{
		$this->view->data["appVersion"] = \WDLIB\Core::config("app_version");
		$this->view->data["projectDomain"] = \WDLIB\Core::config("domain");
		$this->view->data["projectName"] = \WDLIB\Core::config("project_name");
		$this->view->data["APP_URL"] = $this->view->_appurl("");
		$this->view->data["HEAVY_IMAGE_URL"] = $this->view->_appurl("");

		$this->view->api = $this->api;

		if($this->api->platform == \WDLIB\API_TELEGRAM) {
			$this->view->data["TELEGRAM_WEB_APP_ID"] = $this->api->app_id . "/" . $this->api->web_app_id;
		}

		if(\WDLIB\Core::isTest()) {
			$this->view->data["isTest"] = 1;
		}

		$https = 1;

		if($https) {
			$this->view->data["HTTPS"] = 1;
			$this->view->HTTPS = true;
		}

		if($this->is_mobile) {
			$this->view->data["isMobile"] = 1;

			if(!$this->browscap) {
				$this->browscap = get_browser(null, true);
			}

			if(is_array($this->browscap)){
				
				if(($pp = \WDLIB\Util_Array::isset($this->browscap, "platform")) && $pp == "iOS") {
					$this->view->data["isIos"] = 1;
				}
				if(($pp = \WDLIB\Util_Array::isset($this->browscap, "browser")) && (strpos($pp, "WebView") !== FALSE)) {
					$this->view->data["isWebView"] = 1;
				}
			}
			
		}
	}
	
}
