<?php namespace Services\Controllers;



use CodeIgniter\Controller;


require_once(dirname(__FILE__)) . '/../Libraries/service.php';


class Oauth extends \App\Controllers\AppController
{

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		$this->serviceApp = New \Services\Models\ServiceModel();

	}




	public function process($serviceIdSigned, $method = 'process')
	{


		list($serviceId, $serviceIdKey) = explode('-', $serviceIdSigned);


		if (md5($serviceId . ':' . SERVICES_OAUTH_QUERY_KEY . ':' . $method) !== $serviceIdKey)
		{
			//
			// echo md5($serviceId . ':' . SERVICES_OAUTH_QUERY_KEY . ':' . $method);
			die('NSS');
		}



		require_once(dirname(dirname(__FILE__)) . '/Libraries/amocrm/crm.php');



		$app = $this->serviceApp->get($serviceId);


		$service = loadLibrary($app['name'], 'oauth');

		$service->setApp($this->serviceApp, $app);


		switch ($method)
		{
			case 'token':
				// https://rplto.net/services/Pw2222E-e720f5e262275531d99fd1643a02fe4a/oauth/token



				$token = $service->getToken($_REQUEST);

				if (!empty($token['error']))
				{
					print_r( $token['error'] );

					return;
				}

				$service->app->updateData($serviceId, $token);


				header('Location: /');
				die();



			break;

			case 'process':

				// https://rplto.net/services/Pw2222E-7df2529e164bfc57178e305187e2ca14/oauth

				$service->authorize();

			break;
		}







	}







}
