<?php

class Service_User_Auth {
	
	const COOKIE_KEY = "--chat-bot-test-key";
	const SALT = "34242;---234205kkG|||343----sdfsdfs0))(((s0450wsdHsdfESDc2===-f-sdfsfd''353435";

	const STATUS_UNLOGGED = 1;
	const STATUS_AUTHORIZED = 10;

	static public function setAuthCookie(string $token, int $expires = 0)
	{
		self::_cookie(self::COOKIE_KEY, $token, $expires);
	}

	static public function getAuthCookie(\WDLIB\Model_Request $http) : ?array
	{
		$payload = array();
		
		$token = $http->cookies->getstr(self::COOKIE_KEY);
		$auid = self::checkToken($http, $token, $payload);

//		print_r($payload);

		return $payload;
	}
	
	static public function tryAuthorize(\WDLIB\Model_Request $http, /* array */ $auth, /* \WDLIB\Api_Base */ &$api, /* \WDLIB\Model_User_ApiAuth */ &$api_user, /* \WDLIB\Model_User */ &$user, /* \WDLIB\Model_Session_Wrapper */ &$sess) : bool
	{
//		$log = new \WDLIB\Logger;

		if(!is_array($auth)) {
			return false;
		}

		if(!($status = \WDLIB\Util_Array::isset($auth, "status")) || ($status != self::STATUS_AUTHORIZED)) {
			return false;
		}

		$api_platform = \WDLIB\Util_Array::isset($auth, "api");
		if($api_platform === NULL || !($api = \WDLIB\Api_Storage::getInstance()->getApi($api_platform))) {
			return false;
		}

		if(!($api_user_id = \WDLIB\Util_Array::isset($auth, "api_user_id")) || !($api_user = \WDLIB\Model_User_ApiAuth::selectOne($api->platform, $api_user_id))) {
			return false;
		}

		if(!($user = \WDLIB\Model_User::selectById($api_user->user_id))) {
			return false;
		}
		
		$sess = new \WDLIB\Model_Session_Wrapper;
		if(!($sess->sess = \WDLIB\Model_Session_Mem::selectByKey($user->id))) {
			return false;
		}

//		print_r($api_user);
//		print_r($user);
//		print_r($sess);

		return true;
	}

	static public function loginCookie(\WDLIB\Model_Request $http, \WDLIB\Api_Base $api, \WDLIB\Model_User_ApiAuth $api_user, &$sess) : string
	{
		if(($token = self::login($http, $api, $api_user, $sess))) {
			self::setAuthCookie($token);
		}
		return $token;
	}

	static public function login(\WDLIB\Model_Request $http, \WDLIB\Api_Base $api, \WDLIB\Model_User_ApiAuth $api_user, &$sess) : string
	{
		$sess = new \WDLIB\Model_Session_Wrapper(new \WDLIB\Model_Session_Mem);

		// make cookie key
		$sess->sess->key = $api_user->user_id;

		$sess->sess->user_id = $api_user->user_id;
		$sess->sess->date = \WDLIB\Core::curtime();
		$sess->sess->status = \WDLIB\Model_Session_Base::STATUS_VALID;
		$sess->sess->setIp($http->getenv("X_REAL_IP"));

		// browser fingerprint
		$sess->sess->fingerprint = self::_makeFingerprint($http);

		$sess->sess->_changed = true;

		$token = self::makeToken($http, $api, $api_user->api_user_id, array("status" => Service_User_Auth::STATUS_AUTHORIZED));
		return $token;
	}

	static public function logoutCookie(\WDLIB\Model_Session_Wrapper $sess = null) : int
	{
		// clear cookie
		self::setAuthCookie("");

		return self::logout($sess);
	}
	static public function logout(\WDLIB\Model_Session_Wrapper $sess = null) : int
	{
		// clear auth session
		if($sess) {
			$sess->sess->status = \WDLIB\Model_Session_Base::STATUS_INVALID;
			$sess->sess->_changed = true;
			$sess->close();
		}

		return \WDLIB\OK;
	}

	static public function checkToken(\WDLIB\Model_Request $http, string $token, &$payload = null)
	{
//		$log = new \WDLIB\Logger;

		if(empty($token)) {
			return null;
		}

		// split the jwt
		$tokenParts = explode('.', $token);

		if(!is_array($tokenParts) || count($tokenParts) < 3) {
			return null;
		}

		$header = base64_decode($tokenParts[0]);
		$payload = base64_decode($tokenParts[1]);
		$signature_provided = $tokenParts[2];

		$fingerprint = self::_makeFingerprint($http);

		// build a signature based on the header and payload using the secret
		$headers_encoded = \WDLIB\Util_String::base64URLencode($header);
		$payload_encoded = \WDLIB\Util_String::base64URLencode($payload);
		$signature_string = $headers_encoded . $payload_encoded . $fingerprint . self::SALT;
		$signature = hash_hmac("SHA256", $signature_string, \WDLIB\Core::config("secret"), true);
		$signature_encoded = \WDLIB\Util_String::base64URLencode($signature);
		
//		echo "CHECK TOKEN : '$signature_string'\n";
//		echo "SIGNATURE 1 : '$signature_encoded'\n";
//		echo "SIGNATURE 2 : '$signature_provided'\n";
//		echo "SECRET : '$api->server_key'\n";

		// verify it matches the signature provided in the jwt
		$is_signature_valid = ($signature_encoded === $signature_provided);

		if(!$is_signature_valid) {
			$payload = null;
			return null;
		}

		if(!($payload = json_decode($payload, true))) {
			$payload = null;
			return null;
		}

//		print_r($payload);

		return (string) \WDLIB\Util_Array::isset($payload, "api_user_id");
	}

	static public function makeToken(\WDLIB\Model_Request $http, \WDLIB\Api_Base $api, string $api_user_id, array $payload = null) : string
	{
		$token = "";

//		$log = new \WDLIB\Logger;

		$headers = array('alg'=>'HS256','typ'=>'JWT');
		$headers_encoded = \WDLIB\Util_String::base64URLencode(json_encode($headers));

		$payload = array_merge(array("api_user_id" => $api_user_id, "api" => $api->platform), ($payload !== null) ? $payload : array());
		$payload_encoded = \WDLIB\Util_String::base64URLencode(json_encode($payload));
		
		$fingerprint = self::_makeFingerprint($http);

		$signature_string = $headers_encoded . $payload_encoded . $fingerprint . self::SALT;
		$signature = hash_hmac("SHA256", $signature_string, \WDLIB\Core::config("secret"), true);
		$signature_encoded = \WDLIB\Util_String::base64URLencode($signature);

//		echo "MAKE TOKEN : '$signature_string'\n";
//		echo "SIGNATURE : '$signature_encoded'\n";
//		echo "SECRET : '$api->server_key'\n";

		$token = "$headers_encoded.$payload_encoded.$signature_encoded";

		return $token;
	}

	static private function _makeFingerprint(\WDLIB\Model_Request $http) : string
	{
		$fingerprint = $http->getenv("X_REAL_IP");
//			."---" . $http->getenv("HTTP_USER_AGENT")
//			."---" . $http->getenv("HTTP_ACCEPT")
//			."---" . $http->getenv("HTTP_ACCEPT_LANGUAGE")
//			."---" . $http->getenv("HTTP_ACCEPT_ENCONDIG");

//		echo "FINGERPRINT : '$fingerprint'\n";

		$fingerprint = md5($fingerprint);
		return $fingerprint;
	}

	static private function _cookie(string $key, string $value, int $expires = 0) : void
	{
//		setcookie($key, $value, \WDLIB\Core::curtime() + 86400 * 30, "/", "", true, true);

		$expires = ($expires) ? $expires : 86400 * 30;
		$expires += \WDLIB\Core::curtime();
		
		setcookie($key, $value, array(
			"expires" => $expires,
			"path" => "/",
			"domain" => "",
			"secure" => true,
			"httponly" => true,
			"samesite" => "None"
		));
	}

}
