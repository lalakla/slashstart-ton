<?php

class Controller_Error extends Controller_Base {

	public function __construct()
	{
		parent::__construct();

		$this->addMethod("error", array($this, "error"));
		$this->addMethod("trace", array($this, "trace"));

		$this->output = \WDLIB\OUTPUT_JSON;
	}

	public function error()
	{
		$log = new \WDLIB\Logger;

		$method = substr(strip_tags($this->request->get("method")), 0, 512);
		$comment = substr(strip_tags($this->request->get("comment")), 0, 1024);
		$ua = \WDLIB\Util_Array::isset($_SERVER, "HTTP_USER_AGENT");
		if($ua) {
			$comment .= "\nUser-Agent: $ua";
		}

		echo "=========================================================================\n";
		echo "ERROR:\n";
		echo "method: $method\n";
		echo "comment: $comment]n";

		/*
		$es = \WDLIB\Driver_ErrorStats::getInstance();
		$es->add(
			$this->request->getint("errno"),
			$this->request->getint("viewer_platform"),
			Framework\Util_String::matchNumbers($this->request->getstr("viewer_id")),
			$method,
			$comment,
			ip2long($_SERVER["X_REAL_IP"])
		);
		 */

		$this->view->data["error_code"] = \WDLIB\OK;
	}
	
	public function trace()
	{
		$log = new \WDLIB\Logger;

		$str = substr(strip_tags($this->request->get("log")), 0, 1024);

		echo "=========================================================================\n";
		echo "LOG:\n";
		echo "$str\n";
		
		$ua = \WDLIB\Util_Array::isset($_SERVER, "HTTP_USER_AGENT");
		echo "UA: $ua\n";

		$this->view->data["error_code"] = \WDLIB\OK;
	}
}
