<?php

namespace WDLIB;

class Logger {

	private $logging = false;
	private $stream = null;

	public function __construct($stream = null)
	{
		if(is_resource($stream)) {
			$this->stream = $stream;
		}

		$this->start();
	}

	public function __destruct()
	{
		$this->stop();
	}

	public function start()
	{
		if($this->logging) {
			return;
		}

		ob_start();

		$this->logging = true;
	}

	public function stop()
	{
		if(!$this->logging) {
			return;
		}

		$log = ob_get_contents();
		ob_end_clean();

		$this->logging = false;

		if(!empty($log)) {
			if($log[strlen($log) - 1] != "\n") $log .= "\n";

			if($this->stream) {
				// we have stream opened
				if(fwrite($this->stream, date("Y-m-d H:i:s")." ".$log) === FALSE) {
					error_log($log);
				}
			}
			else {
				$logfile = Core::config("logfile");
				if(!$logfile || file_put_contents($logfile, date("Y-m-d H:i:s")." ".$log, FILE_APPEND) === false) {
					error_log($log);
				}
			}
		}
	}

	public function flush()
	{
		if(!$this->logging) {
			return;
		}

		$this->stop();
		$this->start();
	}

	static public function log(string $logstr)
	{
		$log = new Logger;
		echo "$logstr\n";
	}
	static public function warning(string $logstr)
	{
		$log = new Logger;
		echo "WARNING : $logstr\n";
	}
	static public function error(string $logstr)
	{
		$log = new Logger;
		echo "ERROR : $logstr\n";
	}
}

