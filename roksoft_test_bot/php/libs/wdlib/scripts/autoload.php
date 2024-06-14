<?php

$__basedir = realpath(__DIR__."/../");
$__config = $__basedir."/config.php";

// check for ARGV options
if(!isset($__getopt)) $__getopt = "";
$__getopt = "c:v$__getopt";
if(!isset($__getopt_long)) $__getopt_long = array();
$__getopt_long[] = "log:";
$__options = getopt($__getopt, $__getopt_long);
if(is_array($__options) && !empty($__options)) {
	// it looks like some options in ARGV
	if(array_key_exists("c", $__options) && !empty($__options["c"])) {
		// use other config.php
		$__config = $__options["c"];
	}
}

echo "Using config: $__config\n";

require_once $__config;
require_once "$wdlib/wdlib.php";

$loader = \WDLIB\Autoload::init($__basedir, $autoload);

// check verbose mode
$GLOBALS["VERBOSE"] = FALSE;
if(\WDLIB\Util_Array::isset($__options, "v", null) !== null) {
	$GLOBALS["VERBOSE"] = TRUE;
}

// check logfile
if(($p = \WDLIB\Util_Array::isset($__options, "log"))) {
	$config["logfile"] = $p;
}

$core = \WDLIB\Core::init($config);
