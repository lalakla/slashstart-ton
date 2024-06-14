<?php

namespace WDLIB;

abstract class Api_Base {

	public $app_id;
	public $server_key;
	public $client_key;
	public $platform = \WDLIB\API_LOCAL;
	public $name = "local";
	public $data = array();
	
	public function __construct($data)
	{
		$this->app_id = Util_Array::isset($data, "app_id", "");
		$this->server_key = Util_Array::isset($data, "server_key", "");
		$this->client_key = Util_Array::isset($data, "client_key", "");
		$this->platform = Util_Array::isset($data, "platform", "");
		$this->name = Util_Array::isset($data, "name", "");
		$this->data = $data;
	}

	public function getAppUrl() : string
	{
		return "";
	}

	public function getProfileUrl(string $api_user_id) : string
	{
		return "";
	}

	public function checkPaymentSig(\WDLIB\Model_Request $request) : bool
	{
		$str = "";
		foreach($request->getParamsSorted() as $k => $v) {
			if($k == "sig") continue;
			$str .= $k."=".$v;
		}
		$str .= $this->server_key;
		if($request->getstr("sig") != md5($str)) {
			return false;
		}
		return true;
	}

	public function checkVisit(array $params)
	{
		return true;
	}

	public function checkAuth(string $viewer_id, string $authParams) : bool
	{
		return true;
	}
};
