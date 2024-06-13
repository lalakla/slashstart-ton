<?php


require_once(dirname(__FILE__)) . '/service.php';
require_once(dirname(__FILE__)) . '/webhook.php';


class ServiceInstaller extends Service
{

	protected $Service;

	protected $info = [];


	function __construct($settings = array())
	{

		parent::__construct($settings);


		$this->service = new \Services\Models\ServiceModel();

		$this->webhook = new ServiceWebhook();


		$this->info = $this->getInfo();

	}




	function registerChannel($data = [])
	{
		$data['user_id'] = USER_ID;

		$oCH = new \App\Models\Channel();

		return $oCH->register( $data );

	}




	public function isUnique($config)
	{
		$token = $config['token']['token'];

		$where = ['uniq' => substr(md5($token . ':' .  $this->info['id']), 0, 16)];

		if (!empty($config['id']))
			$where['id <>'] = $config['id'];

		return $this->service->select('id')->where($where)->first() ? false : true;
	}




	function beforeUpdate($service, $channel = [])
	{
		$status = ['status' => 'success', 'message' => ''];

		return $status;
	}



	function afterUpdate($service)
	{
		$status = ['status' => 'success', 'message' => ''];

		return $status;
	}




	function beforeDelete($service)
	{
		$status = ['status' => 'success', 'message' => ''];

		return $status;
	}


}



