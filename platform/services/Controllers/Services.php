<?php namespace Services\Controllers;





use CodeIgniter\Controller;



class Services extends \App\Controllers\AppController
{

	protected $services = [];

	protected $installers = [];

	protected $serviceApp;



	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		$this->serviceApp = new \Services\Models\ServiceModel();


		$ld = dirname(__FILE__) . '/../Libraries/';

		$files = scandir($ld);

		$services = [];

		foreach ($files as $file)
		{
			if ($file[0] == '.')
				continue;

			$installFile = $ld . $file . '/' . $file . '_installer.php';



			if (file_exists($installFile))
			{
				require_once($installFile);

				$className = ucfirst($file) . 'Installer';

				$oClass = new $className;

				$service = $oClass->getInfo();


				$services[$service['id']] = $service;

				$this->installers[ $service['id'] ] = $oClass;


			}
		}

		$this->services = $services;

		$this->appendBC(__('app.menu_services', 'Integrations'), '/services');
		$this->setTplData('header_nav', ['file' => 'Services\Views\services-nav', 'position' => 'right']);
		$this->setTplData('submenu', ['file' => 'Services\Views\services-submenu']);


	}


	function proxy()
	{
		$segments = $this->request->uri->getSegments();

		$last = array_pop($segments);

		unset($segments[0]);

		$params = $this->request->getGet($last);

		$params = urldecode($params);

		$info = [];
		if ($params)
		{
			$params = _explode(';', $params);
			foreach ($params as $p)
			{
				list($k, $v) = _explode(':', $p);
				$info[$k] = $v;
			}
		}


		if (empty($info['h']) || strpos($info['h'], HOST_APP) === false || preg_match('#[^0-9a-z\.-]#si', $info['h']))
		{
			e404();
		}

		$redirect = 'https://' . $info['h'] . '/' . join('/', $segments);

		$qs = $this->request->uri->getQuery();

		if ($qs)
			$redirect .= '?' . $qs;

		r($redirect);

	}





	public function install($service = '', $action = '', $param = '')
	{
		if (empty($this->services[$service]))
			e404();

		if (!$action)
		{
			return view( 'Services\Views\install/'.$service.'.php');
		}
		else
		{
			$data = $this->installers[ $service ]->{$action}($param);


			if (!empty($data['service']))
			{

			}


		}

	}







	public function config($id)
	{

		$service = $this->serviceApp->where(['id' => $id])->first();

		if (!$service || empty($this->services[$service['name']]))
			e404();

		$service['client'] = !empty($service['client_params']) ? json_decode($service['client_params'], true) : [];
		$service['params'] = !empty($service['token_params']) ? json_decode($service['token_params'], true) : [];


		$service['webhook'] = $this->installers[$service['name']]->webhook->getWebhookUrl( $this->installers[$service['name']]->service->number2alpha($service['id']) );


		return view( 'Services\Views\install/'.$service['name'].'_config.php', compact(['service']));
	}




	public function update($id)
	{
		$service = $this->serviceApp->where(['id' => $id])->first();

		if (!$service || empty($this->services[$service['name']]))
			e404();

		$oCH = new \App\Models\Channel();
		$channel = $oCH->where('service_id', $id)->first();

		$response = [
			'status' => 'success',
			'message' => __('Информация успешно обновлена.'),
			// 'redirect' => '/services'
		];


		$config = $this->request->getPost('data');
		$config['id'] = $id;



		$result = $this->installers[$service['name']]->update( $config, $service );

		if (empty($result['status']))
		{
			return json_encode( ['status' => 'error', 'message' => __('Ошибка при обновлении настроек приложения. Попробуйте, пожалуйста, еще раз.')] );
		}

		if ($result['status'] == 'error')
		{
			return json_encode( ['status' => 'error', 'message' => $result['message']] );
		}

		if ($result['message'])
			$response['message'] = $result['message'];



		$app = [
			'id' => $service['id'],
		];

		$app['token'] = !empty($result['token']) ? $result['token'] : $service['token'];

		if (!empty($result['expires']))
			$app['token_expires'] = $result['expires'];

		if (!empty($result['params']))
			$app['token_params'] = json_encode($result['params']);

		if (!empty($result['client']))
			$app['client_params'] = json_encode($result['client']);


		$result = $this->installers[$service['name']]->beforeUpdate( $app, $channel );

		if (empty($result['status']))
		{
			return json_encode( ['status' => 'error', 'message' => __('Ошибка при проверке настроек приложения. Попробуйте, пожалуйста, еще раз.')] );
		}

		if ($result['status'] == 'error')
		{
			return json_encode( ['status' => 'error', 'message' => $result['message']] );
		}



		if (!$this->serviceApp->save( $app ))
		{
			return json_encode( ['status' => 'error', 'message' => __('Ошибка при сохранении приложения. Попробуйте, пожалуйста, еще раз.')] );
		}




		$result = $this->installers[$service['name']]->afterUpdate( $app, $result );

		if (empty($result['status']))
		{
			return json_encode( ['status' => 'error', 'message' => __('Ошибка при проверке настроек приложения. Попробуйте, пожалуйста, еще раз.')] );
		}

		if ($result['status'] == 'error')
		{
			return json_encode( ['status' => 'error', 'message' => $result['message']] );
		}




		return json_encode($response);
	}




	public function delete($id)
	{
		$this->isAllowed('services.delete');

		$service = $this->serviceApp->where(['id' => $id])->first();


		$result = $this->installers[$service['name']]->beforeDelete( $service );

		if (empty($result['status']))
		{
			return json_encode( ['status' => 'error', 'message' => __('Ошибка при удалении приложения. Попробуйте, пожалуйста, еще раз.')] );
		}

		if ($result['status'] == 'error')
		{
			return json_encode( ['status' => 'error', 'message' => $result['message']] );
		}




		$this->serviceApp->delete( $id );

		$oCH = new \App\Models\Channel();

		$oCH->where(['service_id' => $id])->delete();


		session()->setFlashdata('flash', ['type' => 'success', 'message' => sprintf(__('Сервис %s успешно удален.'), $service['title']) ]);

	}




	public function setup($service = '')
	{
		if (empty($this->installers[$service]))
			e404();


		$response = [
			'status' => 'success',
		];


		$result = $this->installers[$service]->install( $this->request->getPost('data') );

		if (empty($result['status']))
		{
			return json_encode( ['status' => 'error', 'message' => __('Ошибка при установке приложения. Попробуйте, пожалуйста, еще раз.')] );
		}


		if ($result['status'] == 'error')
		{
			return json_encode( ['status' => 'error', 'message' => $result['message']] );
		}


		$app = [
			'name' => $this->services[$service]['id'],
			'title' => $this->services[$service]['title'],
			'active' => 1,
			'user_id' => USER_ID,
			'token' => $result['token'] ?? '',
			'uniq' => $result['uniq'] ?? '',
			'token_expires' => $result['expires'] ?? '',
			'token_params' => !empty($result['params']) ? json_encode($result['params']) : '',
			'client_params' => !empty($result['client']) ? json_encode($result['client']) : '',
		];


		if (!$this->serviceApp->save( $app ))
		{
			return json_encode( ['status' => 'error', 'message' => __('Ошибка при сохранении приложения. Попробуйте, пожалуйста, еще раз.')] );
		}

		$app['id'] = $this->serviceApp->getInsertID();

		$app['hash'] = $this->serviceApp->number2alpha( $app['id'] );

		$this->serviceApp->save( $app );



		if (!empty($this->services[$service]['channel_type']))
		{
			$channel = [
				'service_id' => $app['id'],
				'type' => $this->services[$service]['channel_type'],
				'title' => $result['channel_name'],
				'external_id' => $result['channel_id'] ?? '',
			];


			if (!($channelId = $this->installers[$service]->registerChannel($channel)))
			{
				$channel['id'] = $channelId;

				$this->serviceApp->delete( $app['id'] );

				return json_encode( ['status' => 'error', 'message' => __('Ошибка при добавлении канала для рассылок. Попробуйте, пожалуйста, еще раз.')] );
			}

		}



		$check = $this->installers[$service]->beforeUpdate( $app, $channel ?? [] );

		if (empty($check['status']))
		{
			$this->serviceApp->delete( $app['id'] );

			return json_encode( ['status' => 'error', 'message' => __('Ошибка при создании настроек приложения. Попробуйте, пожалуйста, еще раз.')] );
		}


		if ($check['status'] == 'error')
		{
			$this->serviceApp->delete( $app['id'] );

			return json_encode( ['status' => 'error', 'message' => $check['message']] );
		}



		if (!$this->serviceApp->save( $app ))
		{
			$this->serviceApp->delete( $app['id'] );

			return json_encode( ['status' => 'error', 'message' => __('Ошибка при обновлении приложения. Попробуйте, пожалуйста, еще раз.')] );
		}







		if (!empty($result['info']))
		{
			$response['message'] = $result['info'];

			session()->setFlashdata('flash', ['type' => 'success', 'message' => $result['info']]);
		}
		else
		{
			$response['message'] = __('Сервис успешно добавлен.');
		}


		if (!empty($this->services[$service]['oninstall']))
			$response['redirect'] = '/services?continue=' . $this->services[$service]['id'];
		else
			$response['redirect'] = '/services' ;


		return json_encode($response);


	}





	public function index()
	{
		$this->isAllowed('services.allow');

		// $oFS = new \Flows\Models\FlowSubscriber();

		// $oFS->checkTriggers('payment', ['sku' => 'PR01']);


		$services = $this->services;


		// \Platform::getProjectByDomain('demo', true);


		$oninstall = '';

		if (!empty($this->request->getGet('continue')))
		{
			$continue = $this->request->getGet('continue');

			if (!empty($services[$continue]['oninstall']))
			{
				if ($services[$continue]['oninstall'] === true)
				{
					// $oninstall = view( 'Services\Views\install/' . $continue . '_oninstall' ) ;

				}
			}
		}





		$installed = $this->serviceApp->
			select('services.id, services.hash, services.active, services.name, services.title, services.client_params, channels.type channel_type, channels.title channel_title')->
			join('channels', 'services.id = channels.service_id', 'left')->
			orderBy('services.title')->findAll();


		


		echo view( 'Services\Views\services', compact('services', 'oninstall', 'installed'));

	}






}

