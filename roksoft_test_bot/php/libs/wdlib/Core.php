<?php

namespace WDLIB;

class Core {

	public $config = null;
	public $request = null;

	private $controllers = null;
	private $default_controller = null;

	public function __construct(array $config)
	{
		$this->config = $config;
		$this->controllers = array();
	}

	public function __destruct()
	{
		$this->default_controller = null;
		$this->controllers = null;
		
		$this->config = null;
		$this->request = null;
	}

	public function run(Model_Request $request = null)
	{
		$this->request = ($request) ? $request : new \WDLIB\Model_Request;

		$controllerClass = $this->default_controller;
		if(array_key_exists($this->request->controller, $this->controllers)) {
			$controllerClass = $this->controllers[$this->request->controller];
		}
		else {
			if(!empty($this->request->controller)) {
				$this->request->method = "404-page-not-found";
			}
		}

		$wrapper = new Controller_Wrapper(new $controllerClass);
		$wrapper->get()->dispatch($this->request);
	}

	public function addController($path, $controller, $default = false)
	{
		$this->controllers[$path] = $controller;
		if(!$this->default_controller || $default) {
			$this->default_controller = $controller;
		}
	}
	
	static public function curtime()
	{
		return time();
	}

	static public function config(string $var = null)
	{
		$core = self::getInstance();

		if($var != null) {

			$args = func_get_args();
			$val = $core->config;

			foreach($args as $ar) {
				$val = Util_Array::isset($val, $ar);
				if(!is_array($val)) {
					break;
				}
			}

			return $val;
		}

		return $core->config;
	}

	static public function isTest() : bool
	{
		$core = self::getInstance();
		return Util_Array::isset($core->config, "test", FALSE);
	}

	static public function getFrameworkPath()
	{
		return __DIR__;
	}

	static private $_instance = null;

	static public function init($config)
	{
		if(!self::$_instance) {
			self::$_instance = new Core($config);
		}
		return self::getInstance();
	}

	static public function getInstance()
	{
		return self::$_instance;
	}
}

