<?php

namespace WDLIB;

class Api_Storage {

	private $apis = null;

	public function getApi($idx)
	{
		$api = null;
		if($this->apis && array_key_exists($idx, $this->apis)) {
			$api = $this->apis[$idx];
		}
		return $api;
	}

	public function getApis()
	{
		return $this->apis;
	}

	private function __construct()
	{
		$config = Core::config("apis");

		$this->apis = array();
		foreach($config as $k => &$v) {
			switch($k) {
				case "local":
					$v["platform"] = \WDLIB\API_LOCAL;
					$this->apis[\WDLIB\API_LOCAL] = new Api_Local($v);
					break;
				case "vk":
					$v["platform"] = \WDLIB\API_VK;
					$this->apis[\WDLIB\API_VK] = new Api_Vk($v);
					break;
				case "vk-auth":
					$v["platform"] = \WDLIB\API_VK_AUTH;
					$this->apis[\WDLIB\API_VK_AUTH] = new Api_Vk($v);
					break;
				case "telegram":
					$v["platform"] = \WDLIB\API_TELEGRAM;
					$this->apis[\WDLIB\API_TELEGRAM] = new Api_Telegram($v);
					break;
				/*
				case "fs":
					$v["platform"] = \Framework::API_FOTOSTRANA;
					$this->apis[\Framework::API_FOTOSTRANA] = new Api_Fs($v);
					break;
				case "fs_mobile":
					$v["platform"] = \Framework::API_FS_MOBILE;
					$this->apis[\Framework::API_FS_MOBILE] = new Api_Fs($v);
					break;
				case "ok":
					$v["platform"] = \Framework::API_ODNOKLASSNIKI;
					$this->apis[\Framework::API_ODNOKLASSNIKI] = new Api_Ok($v);
					break;
				case "mm":
					$v["platform"] = \Framework::API_MM;
					$this->apis[\Framework::API_MM] = new Api_Mm($v);
					break;
				case "mamba":
					$v["platform"] = \Framework::API_MAMBA;
					$this->apis[\Framework::API_MAMBA] = new Api_Mamba($v);
					break;
				case "autobot":
					$v["platform"] = \Framework::API_AUTOBOT;
					$this->apis[\Framework::API_AUTOBOT] = new Api_Autobot($v);
					break;
				case "standalone":
					$v["platform"] = \Framework::API_STANDALONE;
					$this->apis[\Framework::API_STANDALONE] = new Api_Standalone($v);
					break;
				case "total":
					$v["platform"] = \Framework::API_TOTAL;
					$this->apis[\Framework::API_TOTAL] = new Api_Total($v);
					break;
				*/
			}
		}
	}

	static private $_instance = null;

	static public function getInstance()
	{
		if(!self::$_instance) {
			self::$_instance = new Api_Storage;
		}

		return self::$_instance;
	}

}
