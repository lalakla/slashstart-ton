<?php

namespace WDLIB;

class Autoload {

	private static $loader;

	private $libs = array(
		"WDLIB" => __DIR__ . "/",
	);
	
	private $framework_path = "";
	private $project_path = "";

	public static function init(string $path, array $libs = null)
	{
		if (self::$loader == NULL) {
			self::$loader = new self($path, $libs);
		}

		return self::$loader;
	}

	private function __construct(string $path, array $libs = null)
	{
		$this->project_path = $path;

		if(!empty($libs)) {
			foreach($libs as $key => $lib) {
//				if(array_key_exists($key, $this->libs)) {
//					continue;
//				}

				$this->libs[$key] = $lib;
			}
		}

		spl_autoload_register(array($this, 'controller'));
	}

	private function controller(string $className)
	{
		$className = str_replace(array('_','\\'), '/', $className);
		
		if(preg_match("/^([A-Za-z0-9]+)\//", $className, $matches)) {
			$lib = $matches[1];
			if(array_key_exists($lib, $this->libs)) {
				$className = str_replace($lib."/", '', $className);
				$className = $this->libs[$lib].$className;
			}
			else {
				$className = $this->project_path."/".$className;
			}
		}

		require_once $className.".php";
	}
}

