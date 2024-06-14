<?php

namespace WDLIB;

final class Controller_Wrapper {

	private ?Controller_Base $controller;

	public function __construct(Controller_Base $controller)
	{
		$this->controller = $controller;
	}

	public function __destruct()
	{
		if($this->controller) {
			$this->controller->clearMethods();
		}

		$this->controller = null;
	}

	public function get() : Controller_Base
	{
		return $this->controller;
	}
}
