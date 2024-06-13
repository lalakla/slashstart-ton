<?php namespace Services\Controllers;



use CodeIgniter\Controller;




require_once(dirname(__FILE__)) . '/../Libraries/service.php';

class Service extends \App\Controllers\AppController
{

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		$this->serviceApp = New \Services\Models\ServiceModel();


	}





	public function process($serviceIdSigned, $method = 'process', $param = '')
	{


		list($serviceId, $serviceIdKey) = explode('-', $serviceIdSigned);

		if (md5($serviceId . ':' . SERVICES_QUERY_KEY . ':' . $method) !== $serviceIdKey)
		{
			// $serviceId = $this->serviceApp->number2alpha($serviceId);
			// echo $serviceId  . '-' . md5($serviceId . ':' . SERVICES_QUERY_KEY . ':' . $method) . '/' . $method;

			// https://rplto.net/services/Cw2222E-fedaf2b6fdbe4e8cd91df8a550be792f/process - 1005 Invoice

			die(' NSS' );
		}




		$serviceConfig = $this->serviceApp->get($serviceId);

		if (!$serviceConfig)
		{
			die('NSC');
		}


		$service = loadLibrary($serviceConfig['name'], 'flow');

		if (!$service)
		{
			die('NS');
		}


		$service->setApp($this->serviceApp, $serviceConfig);


		if ($service->isAllowedMethod($method))
		{
			$service->setRequest( $this->request );

			$service->{$method}($param);
		}
		else
		{
			die('DM');
		}









	}






}
