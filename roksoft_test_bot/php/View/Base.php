<?php

class View_Base extends \WDLIB\View_Base {

	public $api = null;
	public $api_user_id = null;

	public $browscap = null;
	public $is_mobile = false;

	public function __construct()
	{
		parent::__construct();
	}
}
