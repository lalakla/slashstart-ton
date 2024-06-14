<?php

namespace WDLIB;

class Controller_Base {

	private $methods = null;
	private $default_method = null;
	private $method_404 = null;

	protected $view = null;
	protected $request = null;

	public function __construct()
	{
		$this->methods = array();
	}

	public function __destruct()
	{
		$this->view = null;
		$this->request = null;

		$this->clearMethods();
	}

	final public function dispatch(Model_Request $request)
	{
		$this->request = $request;

		try {
			if(!$this->preDispatch()) {
				return;
			}

			$func = $this->default_method;
			if(array_key_exists($this->request->method, $this->methods)) {
				$func = $this->methods[$this->request->method];
			}
			else {
				if(!empty($this->request->method) && $this->method_404 !== null) {
					$func = $this->method_404;
				}
			}
			call_user_func_array($func, array());

			$this->postDispatch();
		}
		catch(\Exception $e) {
			$this->errorHandler($e);
		}
	}

	final public function clearMethods()
	{
		$this->methods = null;
		$this->method_404 = null;
		$this->default_method = null;
	}

	protected function preDispatch()
	{
		return true;
	}

	protected function postDispatch()
	{
	}

	protected function errorHandler(\Exception $e)
	{
	}

	protected function addMethod(string $method, $func, $default = false)
	{
		$this->methods[$method] = $func;
		if(!$this->default_method || $default) {
			$this->default_method = $func;
		}
	}
	protected function set404Method(callable $func)
	{
		$this->method_404 = $func;
	}

	protected function log(string $logstr, ...$args)
	{
		$log = new Logger;
		echo $logstr;

		foreach($args as $arg) {
			echo " $arg";
		}

		echo "\n";
	}

	protected function logError(string $method, string $logstr, ...$args)
	{
		$log = new Logger;
		echo "ERROR : $method : $logstr";

		foreach($args as $arg) {
			echo " $arg";
		}

		echo "\n";
	}
}


