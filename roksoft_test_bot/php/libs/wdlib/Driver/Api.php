<?php

namespace WDLIB;

class Driver_Api {

	const METHOD_GET_PROFILE = "get_profile";
	const METHOD_SEND_NOTIFY = "send_notify";
	const METHOD_SET_LEVEL = "set_level";
	const METHOD_SET_COUNTER = "set_counter";
	const METHOD_UPLOAD_IMAGE = "upload_image";
	const METHOD_CHECK_LIKE = "check_like";
	const METHOD_CHECK_REPOST = "check_repost";
	const METHOD_SEND_ACHIEV = "send_achiev";
	const METHOD_CALL_METHOD = "call_method";

	private $client = null;

	public function __construct()
	{
		$config = Core::config("clsv");
		if(($config = Util_Array::isset($config, "api"))) {
			$host = Util_Array::isset($config, "host", "");
			$port = Util_Array::isset($config, "port", 0);
			$sock = Util_Array::isset($config, "sock");
			
			$this->client = new \CLSV_Client(\CLSV\CoreWrapper::getInstance());

			$this->client->connect($host, $port, $sock);
			$this->client->set_timeout(10);
		}
	}

	public function __destruct()
	{
		if($this->client && $this->client->is_connected()) {
			$this->client->disconnect();
		}
	}

	public function test() : int
	{
		$retval = \WDLIB\ERROR;

		$data = array(
			"cmd" => "test",
			"prefix" => $this->prefix
		);

		if($this->client) {
			$result = $this->client->send($data);
			if(is_array($result)) {
				$retval = Util_Array::isset($result, "r", $retval);
			}
		}

		return $retval;
	}

	public function getProfile(int $platform, string $user_id, /*mixed*/ $udata = NULL) : int
	{
		$retval = \WDLIB\ERROR;

		$api = Api_Storage::getInstance()->getApi($platform);
		if($api) {
			$data = array(
				"cmd" => self::METHOD_GET_PROFILE,
				"prefix" => $api->name,
				"user_id" => $user_id
			);

			$udata = is_array($udata) ? $udata : array();

			if($udata) {
				$data["udata"] = $udata;
			}

			if($this->client) {
				$result = $this->client->send($data);

				if(is_array($result)) {
					$retval = Util_Array::isset($result, "r", $retval);
				}
			}
		}

		return $retval;
	}

	public function setLevel(int $platform, string $user_id, int $level, /*mixed*/ $udata = NULL) : int
	{
		$retval = \WDLIB\ERROR;

		$api = Api_Storage::getInstance()->getApi($platform);
		if($api && $api->platform != \WDLIB\API_LOCAL) {
			$data = array(
				"cmd" => self::METHOD_SET_LEVEL,
				"prefix" => $api->name,
				"user_id" => $user_id,
				"level" => $level
			);
			
			$udata = is_array($udata) ? $udata : array();

			if($udata) {
				$data["udata"] = $udata;
			}

			if($this->client) {
				$result = $this->client->send($data);

				if(is_array($result)) {
					$retval = Util_Array::isset($result, "r", $retval);
				}
			}
		}

		return $retval;
	}

	public function setCounter(int $platform, string $user_id, int $counter, bool $increment, /*mixed*/ $udata = NULL) : int
	{
		$retval = \WDLIB\ERROR;

		$api = Api_Storage::getInstance()->getApi($platform);
		if($api && $api->platform != \WDLIB\API_LOCAL) {
			$data = array(
				"cmd" => self::METHOD_SET_COUNTER,
				"prefix" => $api->name,
				"user_id" => $user_id,
				"counter" => $counter,
				"increment" => ($increment) ? 1 : 0
			);
			
			$udata = is_array($udata) ? $udata : array();

			if($udata) {
				$data["udata"] = $udata;
			}

			if($this->client) {
				$result = $this->client->send($data);

				if(is_array($result)) {
					$retval = Util_Array::isset($result, "r", $retval);
				}
			}
		}

		return $retval;
	}

	public function sendNotify(int $platform, string $user_id, string $message, int $prioritet = 0, int $delay = 0, /*mixed*/ $udata = NULL) : int
	{
		$retval = \WDLIB\ERROR;

		$api = Api_Storage::getInstance()->getApi($platform);
		if($api && $api->platform != \WDLIB\API_LOCAL) {
			$data = array(
				"cmd" => self::METHOD_SEND_NOTIFY,
				"prefix" => $api->name,
				"user_id" => $user_id,
				"message" => $message,
				"delay" => $delay
			);

			if($prioritet) {
				$data["prioritet"] = $prioritet;
			}
			
			$udata = is_array($udata) ? $udata : array();

			if($udata) {
				$data["udata"] = $udata;
			}

			if($this->client) {
				$result = $this->client->send($data);

				if(is_array($result)) {
					$retval = Util_Array::isset($result, "r", $retval);
				}
			}
		}

		return $retval;
	}

	public function uploadImage(int $platform, string $user_id, String $upload_url, String $image, array $udata = null) : int
	{
		$retval = \WDLIB\ERROR;

		$api = Api_Storage::getInstance()->getApi($platform);
		if($api && $api->platform != \WDLIB\API_LOCAL) {
			$data = array(
				"cmd" => self::METHOD_UPLOAD_IMAGE,
				"prefix" => $api->name,
				"user_id" => $user_id,
				"upload_url" => $upload_url,
				"image" => $image,
				"mime_type" => "image/jpeg"
			);

			$udata = is_array($udata) ? $udata : array();

			if($udata) {
				$data["udata"] = $udata;
			}

			if($this->client) {
				$result = $this->client->send($data);

				if(is_array($result)) {
					$retval = Util_Array::isset($result, "r", $retval);
				}
			}
		}

		return $retval;
	}

	public function checkLike(int $platform, string $user_id, array $data, array $udata = NULL) : int
	{
		$retval = \WDLIB\ERROR;

		$api = Api_Storage::getInstance()->getApi($platform);
		if($api && $api->platform != \WDLIB\API_LOCAL) {
			$data = array(
				"cmd" => self::METHOD_CHECK_LIKE,
				"prefix" => $api->name,
				"user_id" => $user_id,
				"data" => $data
			);

			$udata = is_array($udata) ? $udata : array();

			if($udata) {
				$data["udata"] = $udata;
			}

			if($this->client) {	
				$result = $this->client->send($data);

				if(is_array($result)) {
					$retval = Util_Array::isset($result, "r", $retval);
				}
			}
		}

		return $retval;
	}
	public function checkRepost(int $platform, string $user_id, array $data, array $udata = NULL) : int
	{
		$retval = \WDLIB\ERROR;

		$api = Api_Storage::getInstance()->getApi($platform);
		if($api && $api->platform != \WDLIB\API_LOCAL) {
			$data = array(
				"cmd" => self::METHOD_CHECK_REPOST,
				"prefix" => $api->name,
				"user_id" => $user_id,
				"data" => $data
			);

			$udata = is_array($udata) ? $udata : array();

			if($udata) {
				$data["udata"] = $udata;
			}

			if($this->client) {	
				$result = $this->client->send($data);

				if(is_array($result)) {
					$retval = \Framework\Util_Array::isset($result, "r", $retval);
				}
			}
		}

		return $retval;
	}

	public function sendAchiev(int $platform, string $user, int $achiev_id, int $count, /*mixed*/ $udata = NULL) : int
	{
		$retval = \WDLIB\ERROR;

		$api = Api_Storage::getInstance()->getApi($platform);
		if($api && $api->platform != \WDLIB\API_LOCAL) {
			$data = array(
				"cmd" => self::METHOD_SEND_ACHIEV,
				"prefix" => $api->name,
				"user_id" => $user_id,
				"achiev_id" => $achiev_id,
				"count" => $count
			);
			
			$udata = is_array($udata) ? $udata : array();

			if($udata) {
				$data["udata"] = $udata;
			}

			if($this->client) {	
				$result = $this->client->send($data);

				if(is_array($result)) {
					$retval = Util_Array::isset($result, "r", $retval);
				}
			}
		}

		return $retval;
	}

	public function callMethod(Api_Base $api, string $method, array $params, /*mixed*/ $udata = NULL) : int
	{
		$retval = \WDLIB\ERROR;

		if($api) {
			$data = array(
				"cmd" => self::METHOD_CALL_METHOD,
				"prefix" => $api->name,
				"method" => $method,
				"params" => $params
			);
			
			$udata = is_array($udata) ? $udata : array();
			if($udata) {
				$data["udata"] = $udata;
			}

			if($this->client) {	
				$result = $this->client->send($data);

				if(is_array($result)) {
					$retval = Util_Array::isset($result, "r", $retval);
				}
			}
		}

		return $retval;
	}

	static private $_instance = null;

	static public function getInstance() : Driver_Api
	{
		if(!self::$_instance) {
			self::$_instance = new Driver_Api;
		}

		return self::$_instance;
	}
}
