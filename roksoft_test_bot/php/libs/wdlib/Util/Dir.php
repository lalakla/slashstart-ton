<?php

namespace WDLIB;

class Util_Dir {

	public $f = null;
	public $dirname = "";

	public function __construct($dirname = null)
	{
		if($dirname) $this->open($dirname);
	}
	public function __destruct()
	{
		$this->close();
	}

	public function open(string $dirname)
	{
		$this->f = opendir($dirname);
		if($this->f) $this->dirname = $dirname;
	}

	public function close()
	{
		if($this->f) {
			closedir($this->f);
			$this->f = null;
			$this->dirname = null;
		}
	}

	public function rewind()
	{
		if($this->f) rewinddir($this->f);
	}

	// ===========================================================================
	// STATIC METHODS

	static public function copy(string $src, string $dst) : bool
	{
		$s = new Util_Dir($src);
		if(!$s->f) {
			return FALSE;
		}

		// create DST directory
		@mkdir($dst);

		while(($file = readdir($s->f))) {
			if(($file != '.') && ($file != '..')) {
				if(is_dir($src . '/' . $file) ) {
					if(!self::copy($src .'/'. $file, $dst .'/'. $file)) {
						return FALSE;
					}
				}
				else {
					if(!copy($src .'/'. $file,$dst .'/'. $file)) {
						return FALSE;
					}
				}
			}
		}

		return TRUE;
	}
}
