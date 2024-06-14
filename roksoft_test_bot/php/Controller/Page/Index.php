<?php

class Controller_Page_Index extends Controller_Page_Base {

	public function __construct()
	{
		parent::__construct();

		$this->addMethod("index", array($this, "index"), true);
		$this->addMethod("login", array($this, "login"));
		$this->addMethod("logout", array($this, "logout"));

		$this->view->css[] = "page/index.css";
	}

	public function index()
	{
		\WDLIB\Logger::log("INDEX : ".$this->request->debug(true));
		
		$this->view->data["content"] = "page/index/index.phtml";
	}

	public function login()
	{
		if($this->_isUserLogged()) {
			$this->view->redirect($this->view->_url("/"));
			return;
		}
		
		// CHECK FOR API -----------------------------------------
		// кривая система у ВК - для авторизации на сайте нужно отдельное приложение создавать!
		// соответственно - разные ключи доступа у миниаппа и авториации на сайте
		$this->api = \WDLIB\Api_Storage::getInstance()->getApi(\WDLIB\API_VK_AUTH);
		// -------------------------------------------------------

		if(isset($this->request->request[2]) && $this->request->request[2] == "vk-auth") {
			// try login
			$this->loginOrRegister($this->view->_url("/index/login/vk-auth"));
			$this->view->redirect($this->view->_url("/"));
			return;
		}

		if($this->api) {
			$vk = array(
				"link" => $this->api->authUrl($this->view->_url("/index/login/vk-auth"))
			);
			$this->view->data["vk"] = $vk;
		}


		$this->view->pageTitle .= " | Войти";
		$this->view->data["content"] = "page/index/login.phtml";
	}

	public function logout()
	{
		Service_User_Auth::logoutCookie($this->current_session);
		$this->view->redirect($this->view->_url("/"));
	}

	private function loginOrRegister($url)
	{
		$res = $this->api->getAccessToken($this->request->getstr("code"), $url);

		if(!$res || !is_array($res)) {
			// какая-то ошибка
			// throw new Exception("AUTH ERROR", \WDLIB\ERROR_AUTH);
			$this->view->redirect($this->view->_url("/"));
		}

// [access_token] => vk1.a.doENWicEtjNa3qRv8FZDK-rD-nIXfRyspTkKjLceVRGOFPZdNXjXneXT0fy5jy69kNOc2C2EwmVAzFHPLhhrSqnOCvbqvAt7r7cmhOKje9sheznrs5OY3rk35aJ3h3UYiIAM57WZSLg6PqOeU6BmOIeRfB5Q41P8vr0ivKoC4rEus5Ku5oRaJgDuJTk6Zqe9
// [expires_in] => 86262
// [user_id] => 348875069

		$user = null;
		$vk_user_id = \WDLIB\Util_Array::isset($res, "user_id");
	
		if(!$vk_user_id || \WDLIB\Util_Array::isset($res, "error")) {
			// какая-то ошибка
			throw new Exception("AUTH ERROR", \WDLIB\ERROR_AUTH);
		}

		$now = \WDLIB\Core::curtime();
		$ip = ip2long($this->request->getenv("X_REAL_IP"));

		$errors = array();
		$user_data = array();

		// получаем тут данные про пользователя
		// @TODO в некоторой перспективе - вынести это через очередь вообще в демона
		// но пока нагрузки особой не будет - так что пусть будет здесь
		$res = $this->api->getProfile($vk_user_id);

		// validate user data
		if(Util_Form_User_Register::validateApi($res, $user_data, $errors) != \WDLIB\OK) {
			// @TODO какую-то ошибку вывести поприличнее
			throw new Exception("AUTH ERROR", \WDLIB\ERROR_AUTH);
		}

		// проверяем наличие такового
		$api_user = \WDLIB\Model_User_ApiAuth::selectOne($this->api->platform, $vk_user_id);
		if(!$api_user || !($user = \WDLIB\Model_User::selectById($api_user->user_id))) {
			// пользователь зашел через приложение ВК/ОК/...
			// создаем нового сразу !!!
			// подтверждение будет после запроса инит от js клиента
			
			$api_user = new \WDLIB\Model_User_ApiAuth;
			$api_user->platform = $this->api->platform;
			$api_user->api_user_id = $vk_user_id;
			$api_user->is_app_user = 1; // для авторизации на сайте требуется установка !
			$api_user->reg_date = $now;
			$api_user->last_date = $now;
			$api_user->reg_ip = $ip;
			$api_user->last_ip = $ip;

			if(!($user = Service_User_User::registerByApi($this->api, $api_user, $user_data, $errors))) {
				throw new Exception("AUTH ERROR", \WDLIB\ERROR_AUTH);
			}
		}

		$user->name = \WDLIB\Util_Array::isset($user_data, "user-name");
		$user->sex = \WDLIB\Util_Array::isset($user_data, "user-sex");
		$user->pic = \WDLIB\Util_Array::isset($user_data, "user-pic");

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
		if(!(Service_User_Auth::loginCookie($this->request, $this->api, $this->current_api_user, $this->current_session)) || !$this->current_session->isValid()) {
			// ошибка какая-то ...
			$this->current_user = null;
			$this->current_api_user = null;
			$this->current_session = null;
			throw new Exception("AUTH ERROR", \WDLIB\ERROR_AUTH);
		}

		// прописываем платформу в UserInfo
		Model_UserInfo::set($this->current_user->id, Model_UserInfo::USER_LAST_PLATFORM, $this->current_api_user->platform);
		
		Service_Stats::add($this->current_user, "visit");
		Service_Stats::add($this->current_user, "init");
		Service_Stats::add($this->current_user, "run");
	}

}
