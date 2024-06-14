<?php

class Controller_Api_Base extends \WDLIB\Controller_Base {
	
	protected $api = null;

	public function __construct()
	{
		parent::__construct();

		$this->view = new \WDLIB\View_Base;
		$this->view->HTTPS = true;
		$this->output = \WDLIB\OUTPUT_RAW;
	}

	protected function errorHandler(\Exception $e) : void
	{
		$this->view->data = array(
			"error" => $e->getMessage(),
			"error_code" => $e->getCode()
		);

		if($e instanceof \WDLIB\Model_Exception) {
			$this->view->data["error_data"] = $e->data;
		}

		$this->view->templateJson();

		$log = new \WDLIB\Logger;

		echo date("Y-m-d H:i:s")." ERROR[".$e->getCode()."]: ".$e->getMessage()."\n";
		echo "HTTP: ".$this->request->debug(true)."\n";
		echo "X-REAL-IP : ".$_SERVER["X_REAL_IP"]."\n";
	}

}
