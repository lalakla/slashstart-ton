<?php

class Controller_Page_Base extends Controller_Base {

	protected ?Array $breadcrumbs = array();

	protected $config = null;
	protected $authCookie = null;

	public function __construct()
	{
		parent::__construct();

		$this->set404Method(array($this, "method404"));

		$this->view = new View_Page;
	}
	public function __destruct()
	{
		parent::__destruct();

		$this->breadcrumbs = null;
		$this->config = null;
	}

	protected function preDispatch() : bool
	{
		$r = parent::preDispatch();
		if(!$r) {
			return $r;
		}

		// load common config ...
//		$config = \WDLIB\Model_Config::select();
//		$this->config = new \WDLIB\Util_Mapss(array_reduce($config, function($arr, $item) {
//			$arr[$item->key] = $item->value;
//			return $arr;
//		}, array()));
		$this->config = new \WDLIB\Util_Mapss(Model_Config::json());
		$this->view->config = $this->config;

		$this->authCookie = Service_User_Auth::getAuthCookie($this->request);
		if(!Service_User_Auth::tryAuthorize($this->request, $this->authCookie, $this->api, $this->current_api_user, $this->current_user, $this->current_session)) {
			$this->current_user = null;
			$this->current_api_user = null;
			$this->current_session = null;
		}

		$this->view->current_user = $this->current_user;
		$this->view->current_api_user = $this->current_api_user;

		// add stats : visit
//		$uid = ip2long($_SERVER["X_REAL_IP"]);
//		Service_Stats::_add(0, $uid, "visit");

		// add access log
		if(($logfile = \WDLIB\Core::config("access_logfile"))) {
			$lf = new \WDLIB\Util_File($logfile, "a");
			$log = new \WDLIB\Logger($lf->f);
			echo $this->request->getenv("X_REAL_IP")." ".$this->request->uri." ".$this->request->getenv("HTTP_REFERER")." ".$this->request->getenv("HTTP_USER_AGENT");
			$log->stop();
		}

		return $r;
	}

	protected function postDispatch() : void
	{
		if($this->output == \WDLIB\OUTPUT_HTML) {
			if(!empty($this->breadcrumbs)) {
				$last = end($this->breadcrumbs);
				$last->last = TRUE;

				$this->view->data["breadcrumbs"] = $this->breadcrumbs;
			}

			$this->view->data["appVersion"] = \WDLIB\Core::config("app_version");
			$this->view->data["projectDomain"] = \WDLIB\Core::config("domain");
			$this->view->data["projectName"] = \WDLIB\Core::config("project_name");

			// page title
			$this->view->data["pageTitle"] = $this->view->data["projectName"].$this->view->pageTitle;

			// config
			$this->view->data["Config"] = $this->view->config->getSorted();
		}

		if($this->current_session && $this->current_session->isValid()) {
			// save session data
			$this->current_session->sess->_changed = true;
		}

		parent::postDispatch();
	}

	public function method404()
	{
		\WDLIB\Logger::error("URL NOT FOUND : ".$this->request->debug(true));

		header("HTTP/1.1 404 Not Found");
//		$this->view->css[] = "page/index.css";
//		$this->view->data["content"] = "page/index/404.phtml";
	}
}
