<?php

namespace WDLIB;

class Util_File {

	public $f = null;
	public $filename = "";

	public function __construct($filename = null, string $mode = null)
	{
		if($filename) $this->open($filename, $mode);
	}
	public function __destruct()
	{
		$this->close();
	}

	public function open(string $filename, string $mode)
	{
		$this->f = fopen($filename, $mode);
		if($this->f) $this->filename = $filename;
	}

	public function close()
	{
		if($this->f) {
			fclose($this->f);
			$this->f = null;
			$this->filename = null;
		}
	}

	public function rewind()
	{
		if($this->f) rewind($this->f);
	}

	public function truncate(int $size = 0)
	{
		if($this->f) ftruncate($this->f, $size);
	}

	// ===========================================================================
	// STATIC METHODS

	static public function dupleRunNot(string $pidfile)
	{
		$fw = new Util_File($pidfile, "a+");
		if(!$fw->f) {
			fprintf(STDERR, "Failed to access pid file, file does not exist, trying to create\n");
			return false;
		}

		$fw->rewind();
		if(fscanf($fw->f, "%s", $pid) == 1) {
			$cmd = "ps -p $pid 1>/dev/null";
			system($cmd, $sy);
			if(!$sy) {
				// process already running
				fprintf(STDERR, "Process is already running, exiting...\n");
				return false;
			}
		}
//		else {
//			fprintf(STDERR, "fscanf error file : %s\n", $pidfile);
//		}
		
		$fw->rewind();
		$fw->truncate();
		
		if($fw->f) {
			fprintf($fw->f, "%u",  getmypid());
		}
		else {
			fprintf(STDERR, "Failed to create pid file, exiting...\n");
			return false;
		}

		return true;
	}
}
