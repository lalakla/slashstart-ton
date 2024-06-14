<?php

namespace WDLIB;

class Api_Telegram extends Api_Base {

	const WEB_APP_DATA_CONST = 'WebAppData';

	public $api = null;
	public $web_app_id = "";

	public function __construct($data)
	{
		$data["platform"] = Util_Array::isset($data, "platform", \WDLIB\API_TELEGRAM);
		
		parent::__construct($data);

		$this->web_app_id = Util_Array::isset($data, "web_app_id", "app");

		$this->client = new \TelegramBot\Api\Client($this->server_key);
	}
	
	public function checkVisit(array $params)
	{
		$data_check = (array) $params;

		$hash = Util_Array::isset($data_check, "hash");
		unset($data_check["hash"]);

		ksort($data_check, SORT_NATURAL);

		$data_check_string = urldecode(http_build_query($data_check, arg_separator: "\n"));

		$secret_key = $this->sha256($this->server_key, self::WEB_APP_DATA_CONST);
		$hash_data = $this->sha256($data_check_string, $secret_key);

		$query_hash = bin2hex($hash_data);

		return $query_hash === $hash;
	}

	public function checkAuth(string $viewer_id, string $authParams) : bool
	{
//		$log = new \WDLIB\Logger;
//		echo "TELEGRAM CHECK AUTH : $viewer_id, $authParams\n";
		
		$params = json_decode($authParams, true);

		if(!is_array($params) || empty($params)) {
			return false;
		}

		return $this->checkVisit($params);
	}

	private function sha256(string $data, string $key)
	{
		return hash_hmac('sha256', $data, $key, true);
	}
}
