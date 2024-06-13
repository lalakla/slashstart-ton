<?php namespace Services\Controllers;



use CodeIgniter\Controller;


// 


require_once(dirname(__FILE__)) . '/../Libraries/service.php';


class Webhook extends \App\Controllers\AppController
{

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		$this->serviceApp = New \Services\Models\ServiceModel();
	}



	public function processWH($serviceConfig, $method = 'process')
	{
		// l($serviceConfig, 'pwh');

		$serviceName = strtolower( preg_replace('#[^0-9a-z_]#si', '', $serviceConfig['name']) );

		$file = dirname(dirname(__FILE__)) . '/Libraries/' . $serviceName . '/' . $serviceName . '_webhook.php';

		if (!file_exists($file))
		{
			die('NSF');
		}

		require_once($file);

		$class = ucfirst($serviceName) . 'Webhook';

		$object = new $class;

		$object->setApp($this->serviceApp, $serviceConfig);

		if ($object->isAllowedMethod($method))
		{
			$object->{$method}();
		}
		else
		{
			die('DM');
		}

	}




	public function proxy($service = '')
	{
		// l(file_get_contents('php://input'), 'fbwh-proxy', true);
		

		if (!empty($_GET['hub_verify_token']))
		{
			die($_GET['hub_challenge']);
		}

		$updates = json_decode(file_get_contents('php://input'), true);

		l($updates, 'fbwh-proxy', true);



		// Instagram, Page = Facebook
		if (!empty($updates['object']) && !empty($updates['entry']) && ($updates['object'] == 'instagram' || $updates['object'] == 'page'))
		{
			$oPSG = new \Clients\Models\ServiceGroup();

			foreach ($updates['entry'] as $item)
			{
				if (empty($item['id']))
					continue;

				$externalId = $item['id'];

				$projectId = $oPSG->getByTypeAndGroup(CHANNEL_TYPE_INSTAGRAM, $externalId);



				if (!$projectId)
				{
					l('Instagram WH. n/a: ' . $externalId, 'instagram-proxy-errors');
					l($updates, 'instagram-proxy-errors');

					continue;
				}


				try
				{
					if (!$this->serviceApp->setDatabase(MYSQL_BASE_NAME_TPL . $projectId))
						return false;

					$project = \Platform::setProject( $projectId );

				}
				catch (\Throwable $e)
				{
				    continue;
				}

				$oCh = new \App\Models\Channel();

				$type = $updates['object'] == 'instagram' ? CHANNEL_TYPE_INSTAGRAM : CHANNEL_TYPE_FACEBOOK;

				$channel = $oCh->getByTypeAndId($type, $externalId);


				// log_message('critical', print_r($channel, 1));


				if (!$channel)
				{
					if (empty($item['messaging'][0]['message']['is_echo']))
					{
						l($externalId . ' - instagram not found', 'instagram-proxy-errors');
						l($type, 'instagram-proxy-errors');
						l($projectId, 'instagram-proxy-errors');
						l($updates, 'instagram-proxy-errors');
					}

					continue;
				}

				$serviceConfig = $this->serviceApp->getById( $channel['service_id'] );


				if (!$serviceConfig)
				{
					log_message('critical', 'instagram service not found ' . print_r($channel, 1));
					continue;
				}


				$this->processWH( $serviceConfig );

			}


			// log_message('critical', print_r($updates, 1));
		}

	}



	public function process($serviceIdSigned, $method = 'process')
	{

		list($serviceId, $serviceIdKey) = explode('-', $serviceIdSigned);

		if (md5($serviceId . ':' . SERVICES_WEBHOOK_QUERY_KEY . ':' . $method) !== $serviceIdKey)
		{
			// $serviceId = $this->serviceApp->number2alpha($serviceId);
			// echo $serviceId  . '-' . md5($serviceId . ':' . SERVICES_WEBHOOK_QUERY_KEY . ':' . $method) . '/' . $method;
			// https://rplto.net/services/9w2222E-8112b1c84f4df5c7101a55ae3aacd3cf/webhook/report - 1004

			// https://rplto.net/services/Cw2222E-f62fd9ca402b2f0f07a8fd6e04bda9e4/webhook/yoomoney - 1005

			die(' NSS');
		}


		$pr = \Platform::getProject();


		try
		{
			$serviceConfig = $this->serviceApp->get($serviceId);

		}
		catch (\Throwable $e)
		{
		    die('NSCB');
		}

		

		if (!$serviceConfig)
		{
			die('NSC');
		}



		$this->processWH( $serviceConfig, $method );





	}










}
