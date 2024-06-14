<?php

namespace WDLIB;

class Api_Vk extends Api_Base {

	const OAUTH_URL = "https://oauth.vk.com";
	const API_URL = "https://api.vk.com";
	const API_VERSION = "5.199";

	const USER_FIELDS = "photo_100,photo_200_orig,sex,bdate,city,has_mobile,verified,online";

	const SCOPE_ACCESS_NOTIFY = 1;
	const SCOPE_ACCESS_EMAIL = 4194304;

	const AUTH_DISPLAY_PAGE = "page";
	const AUTH_DISPLAY_POPUP = "popup";
	const AUTH_DISPLAY_MOBILE = "mobile";

	public $access_token = "";
	public $service_key = "";

	public function __construct($data)
	{
		$data["platform"] = Util_Array::isset($data, "platform", \WDLIB\API_VK);
		
		parent::__construct($data);

		$this->access_token = Util_Array::isset($this->data, "access-token", "");
		$this->service_key = Util_Array::isset($this->data, "service_key", "");
	}

	public function getAppUrl() : string
	{
		return "https://vk.com/app{$this->app_id}";
	}
	public function getProfileUrl(string $api_user_id) : string
	{
		return "https://vk.com/id{$api_user_id}";
	}

	public function authUrl(string $redirect_uri, int $scope = 0, string $display = self::AUTH_DISPLAY_PAGE) : string
	{
		$url = self::OAUTH_URL . "/authorize?" . urldecode(http_build_query(array(
			"client_id" => $this->app_id,
			"redirect_uri" => $redirect_uri,
			"response_type" => "code",
			"display" => $display,
			"scope" => $scope
		)));

		return $url;
	}
	public function getAccessToken(string $code, string $redirect_uri)
	{
		$url = self::OAUTH_URL . "/access_token";
		$params = array(
			"client_id" => $this->app_id,
			"client_secret" => $this->server_key,
			"redirect_uri" => $redirect_uri,
			"code" => $code
		);

		$res = $this->_send($url, $params);

		if(is_array($res)) {
			$this->access_token = Util_Array::isset($res, "access_token");
		}

		return $res;
	}

	public function getGroup(int $group_id, string $group_ids = "")
	{
		$url = self::API_URL . "/method/groups.getById";
		$params = array(
			"access_token" => $this->service_key,
			"v" => self::API_VERSION
		);

		if($group_id) {
			$params["group_id"] = $group_id;
		}
		else {
			$params["group_ids"] = $group_ids;
		}

		$res = $this->_send($url, $params);
		if(is_array($res) && $res = Util_Array::isset($res, "response")) {
//			$res = $res[0];
		}

		return $res;
	}

	public function getProfile(string $user_id)
	{
		$url = self::API_URL . "/method/users.get";
		$params = array(
			"access_token" => $this->access_token,
			"user_ids" => $user_id,
			"fields" => self::USER_FIELDS,
			"v" => self::API_VERSION
		);

		$res = $this->_send($url, $params);
		if(is_array($res) && $res = Util_Array::isset($res, "response")) {
			$res = $res[0];
		}

		return $res;
	}
	
	public function sendMessage(string $peer_id, string $message, array $params = null)
	{
		$url = self::API_URL . "/method/messages.send";
		$request = array(
			"access_token" => $this->access_token,
			"peer_id" => $peer_id,
			"message" => $message,
			"v" => self::API_VERSION,
			"random_id" => rand()
		);

		if($params !== null) {
			if($p = Util_Array::isset($params, "keyboard")) {
				$request["keyboard"] = json_encode($p);
			}
		}

		return $this->_send($url, $request);
	}

	public function checkVisit(array $params)
	{
		$res = parent::checkVisit($params);

		$sign = Util_Array::isset($params, "sign");
		if(!$sign) {
			$res = false;
		}
		$sign_keys = Util_Array::isset($params, "sign_keys");
		if(!$sign_keys) {
			$res = false;
		}
		$sign_keys = explode(',', $sign_keys);

		if($res) {
			$sign_params = array();

			foreach($sign_keys as $k) {
				$sign_params[$k] = Util_Array::isset($params, $k);
			}

			// Формируем строку вида "param_name1=value&param_name2=value"
			$query = http_build_query($sign_params);

			// Получаем хеш-код от строки, используя защищеный ключ приложения. Генерация на основе метода HMAC.
			$query_sign = rtrim(strtr(base64_encode(hash_hmac('sha256', $query, $this->server_key, true)), '+/', '-_'), '=');

			$res = ($query_sign === $sign);
		}

		return $res;
	}

	public function checkAuth(string $viewer_id, string $authParams) : bool
	{
		$res = parent::checkAuth($viewer_id, $authParams);

		if($res) {
			// CHECK AUTH VK
			$str = $this->app_id."_".$viewer_id."_".$this->server_key;
			$res = ($authParams && $authParams == md5($str));
		}

		return $res;
	}

	private function _send(string $url, array $params, &$resp = null)
	{
		$query = http_build_query($params);

		$ch = curl_init($url . "?" . $query);

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
//		curl_setopt($ch, CURLOPT_URL, self::API_URL);
//		curl_setopt($ch, CURLOPT_POST, true);
//		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result_raw = curl_exec($ch);
		curl_close($ch);

//		Logger::log("VK API _send: '$result_raw'");

		// echo "RESULT:\n $result_raw\n";
		
		if($resp !== null) {
			$resp = $result_raw;
		}

		$result = json_decode($result_raw, true);

		return $result;
	}
}
