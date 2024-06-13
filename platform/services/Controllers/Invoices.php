<?php namespace Services\Controllers;




use CodeIgniter\Controller;



require_once(dirname(__FILE__)) . '/../Libraries/service.php';

class Invoices extends \App\Controllers\AppController
{

	var $oSubscriber;




	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);


		$this->oSubscriber = new \Subscribers\Models\Subscriber();

		$this->oService = new \Services\Models\ServiceModel();

		$this->oTask = new \Flows\Models\Task();
	}





	public function create()
	{
		$params = $this->request->getGet();

		$isValid = invoiceCheckParams($params);

		if (!$isValid)
			return e404();

		$serviceId = $methodName = '';

		if (strripos($params['m'], '/') !== false)
			list($serviceId, $methodName) = explode('/', $params['m']);
		else
			$serviceId = $params['m'];

		l($params, 'inv', true);

		$service = [];
		if ($serviceId)
		{
			$service = $this->oService->getById( $this->oService->alpha2number( $serviceId ) );
		}
		else
		{
			$services = $this->oService->getPaymentServices( );
			if ($services)
			{
				$service = $services[0];
			}
		}


		if (!$service)
		{
			r('/payment/disabled');
		}



		$app = loadLibrary($service['name'], 'payment');

		$app->setApp($this->oService, $service);

		$subscriber = $this->oSubscriber->getAllData( extIdDecode( $params['s'] ) );

		$app->setSubscriber( $subscriber );


		$params['order_id'] = $params['o'] ? $params['o'] : (int)(microtime(true) * 1000);

		$params['method'] = $methodName;



		$cKey = 'invoice_' . $params['order_id'];

		setCache($cKey, $params, HOUR);


		$app->start( $params );


	}




	public function check()
	{
		$params = $this->request->getGet();


		$cKey = 'invoice_' . $params['order_id'];

		$order = getCache($cKey);



		$service = [];

		if (!empty($params['service_id']))
		{
			$serviceId = $methodName = '';

			if (strripos($params['service_id'], '/') !== false)
				list($serviceId, $methodName) = explode('/', $params['service_id']);
			else
				$serviceId = $params['service_id'];

			$service = $this->oService->getById( $this->oService->alpha2number( $serviceId ) );
		}


		if (!$service)
		{
			r('/payment/disabled');
		}


		$app = loadLibrary($service['name'], 'payment');

		$app->setApp($this->oService, $service);

		$subscriber = $this->oSubscriber->getAllData( alphaID($order['s'], true, 7 ) );

		$app->setSubscriber( $subscriber );

		$result = $app->check( $order );

		if (!empty($result['error']))
		{

		}

		if (!empty($result['r']))
		{
			r( $result['r'] );
		}

	}




	public function success()
	{
		$params = $this->request->getGet();

		$order = [];

		if (!empty($params['order_id']))
			$order = getCache('invoice_' . $params['order_id']);

		// Добавить транзакцию
        $current = $this->oSubscriber->where(['id' => extIdDecode($order['s'])])->first();

        $mPaymentTransaction = new \Services\Models\PaymentTransaction();
        
        $mPaymentTransaction->save([
            'subscriber_id' => $current['id'],
            'order_id' => $order['order_id'],
            'service_id' => $order['m'],
            'price' => $order['p'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Проверить настройки магазина на наличии учитывать заказы в магазине при начислении баллов рефералки (если не стоит галочка потом добавить баллы)
        $oRS = new \Referal\Models\ReferralSetting();
        $allowRefPoints = $oRS->get_settings_value_by_name('allow_ref_points');
        if (isset($allowRefPoints) && $allowRefPoints){
            // Добавить баллы
            $oPT = new \Referal\Models\PointTransaction();
            
            $dataPoints = [];
            $getParentSubscribers = $this->oSubscriber->getParents($current['ref_link']);

            // Добавить себе балл
            $settingsValues = $oRS->get_settings_value_by_name('level_0');
            $referalValue = (json_decode($settingsValues, true)['purchase'] ?? 0)  * $order['p'] / 100;
            $dataPoints[0]['subscriber_id'] = $current['id'];
            $dataPoints[0]['point'] = $referalValue;

            // Добавить реффералам до 3 уровня бал
            foreach ($getParentSubscribers as $level => $parents){
                $settingsValues = $oRS->get_settings_value_by_name('level_'.$level);

                // Проценты от покупок
                $referalValue = (json_decode($settingsValues, true)['purchase'] ?? 0)  * $order['p'] / 100;
                $dataPoints[$level]['from_subscriber'] = $current['id'];
                $dataPoints[$level]['subscriber_id'] = $parents['id'];
                $dataPoints[$level]['point'] = $referalValue;
            }
            $oPT->updateSubscriberPoints($dataPoints);
        }

		$redirect = PROJECT_URL . 'payment/success';

		if ($order)
			$redirect .= '?sku=' . $order['a'] . '&id=' . $order['order_id'] . '&price=' . $order['p'];

		r($redirect);

	}


	public function fail()
	{
		$params = $this->request->getGet();

		$order = [];

		if (!empty($params['order_id']))
			$order = getCache('invoice_' . $params['order_id']);

		$redirect = PROJECT_URL . 'payment/error';

		if ($order)
			$redirect .= '?sku=' . $order['a'] . '&id=' . $order['order_id'] . '&price=' . $order['p'];

		r($redirect);

	}


	public function thanks()
	{
		$order = getCache('invoice_' . $this->request->getGet('order_id'));

		if ($order)
		{
			deleteCache('invoice_' . $order['order_id']);
		}


		echo view( 'Services\Views\payments/success', compact([
			'order'
		]));

	}


	public function error()
	{
		$order = getCache('invoice_' . $this->request->getGet('order_id'));

		if ($order)
		{
			deleteCache('invoice_' . $order['order_id']);
		}


		echo view( 'Services\Views\payments/error', compact([
			'order'
		]));

	}


	public function disabled()
	{
		echo view( 'Services\Views\payments/disabled' );
	}















}
