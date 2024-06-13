<?php


require_once(dirname(__FILE__)) . '/service.php';


class ServicePayment extends Service
{

	function __construct($settings = array())
	{

		parent::__construct($settings);


		$this->oService = new \Services\Models\ServiceModel();


		$this->successUrl = PROJECT_URL . 'invoice/success';
		$this->checkUrl = PROJECT_URL . 'invoice/check';
		$this->failUrl = PROJECT_URL . 'invoice/fail';

	}



	public function formatOrder($data)
	{
		$full = [
			'sku' => $data['sku'] ?? ($data['articul'] ?? ''), // SKU
			'product' => $data['product'] ?? '', // product id
			'order' => $data['order'] ?? '', // product id
			'price' => $data['price'] ?? '',
			'link' => $data['link'] ?? '',
		];

		$short = [
			's' => $data['subscriber'],
			'a' => $full['sku'],
			'i' => $full['product'],
			'o' => $full['order'], // order id
			'p' => $full['price'],  // price
			'l' => $full['link'],  // link
			'c' => $data['currency'] ?? '',  // currency
		];

		return compact('full', 'short');
	}



	public function invoice($order = [])
	{

		$oFSA = new \Flows\Models\FlowSubscriberAttribute();

		$df = df();

		$subscriber_id = extIdDecode($order['s']);



		$batch = [
			[
				'subscriber_id' => $subscriber_id,
				'param' => 'invoice',
				'attr' => '',
				'value' => 1,
				'created_at' => $df
			],
			[
				'subscriber_id' => $subscriber_id,
				'param' => 'invoice_price',
				'attr' => 'invoice_price',
				'value' => $order['p'],
				'created_at' => $df
			]
		];


		if ($order['a'])
		{
			$batch[] = [
				'subscriber_id' => $subscriber_id,
				'param' => 'invoice_sku',
				'attr' => '',
				'value' => $order['a'],
				'created_at' => $df
			];
		}

		if ($order['i'])
		{
			$batch[] = [
				'subscriber_id' => $subscriber_id,
				'param' => 'invoice_product',
				'attr' => '',
				'value' => $order['i'],
				'created_at' => $df
			];
		}

		if ($order['o'])
		{
			$batch[] = [
				'subscriber_id' => $subscriber_id,
				'param' => 'invoice_order',
				'attr' => '',
				'value' => $order['o'],
				'created_at' => $df
			];
		}


		$oFSA->insertBatch($batch);


		$oFS = new \Flows\Models\FlowSubscriber();

		$params = ['id' => $order['i'], 'sku' => $order['a'], 'price' => $order['p'],  'order' => $order['o'], 'link' => $order['l'] ?? '', 'subscriber_id' => $subscriber_id];

        $vars = ['trigger_order' => $order['o']];

        $triggers = $oFS->checkTriggers('payment_invoice', $params, $vars);

        return compact('triggers');
	}


	public function cancelled($order = [])
	{
		$subscriber_id = extIdDecode($order['s']);

		$oFS = new \Flows\Models\FlowSubscriber();

		$params = ['id' => $order['i'], 'sku' => $order['a'], 'price' => $order['p'],  'order' => $order['o'], 'link' => $order['l'] ?? '', 'subscriber_id' => $subscriber_id];

        $vars = ['trigger_order' => $order['o']];

        $triggers = $oFS->checkTriggers('payment_canceled', $params, $vars);

        return compact('triggers');
	}


	public function complete($order = [], $saved = [])
	{

		$oFSA = new \Flows\Models\FlowSubscriberAttribute();

		$df = df();

		$subscriber_id = extIdDecode($order['s']);



		$batch = [
			[
				'subscriber_id' => $subscriber_id,
				'param' => 'payment',
				'attr' => '',
				'value' => 1,
				'created_at' => $df
			],
			[
				'subscriber_id' => $subscriber_id,
				'param' => 'payment_price',
				'attr' => 'payment_price',
				'value' => $order['p'],
				'created_at' => $df
			]
		];


		if ($saved)
		{
			$batch[] = [
				'subscriber_id' => $subscriber_id,
				'param' => 'payment_method_saved',
				'attr' => 'payment_method_saved',
				'value' => 1,
				'created_at' => $df
			];

			$batch[] = [
				'subscriber_id' => $subscriber_id,
				'param' => 'payment_method_expires',
				'attr' => 'payment_method_expires',
				'value' => $saved['expires'],
				'created_at' => $df
			];

			$this->oSubscriber->save([
				'id' => $subscriber_id,
				'payment' => encrypt(json_encode($saved))
			]);
		}


		if ($order['a'])
		{
			$batch[] = [
				'subscriber_id' => $subscriber_id,
				'param' => 'payment_sku',
				'attr' => '',
				'value' => $order['a'],
				'created_at' => $df
			];
		}

		if ($order['i'])
		{
			$batch[] = [
				'subscriber_id' => $subscriber_id,
				'param' => 'payment_product',
				'attr' => '',
				'value' => $order['i'],
				'created_at' => $df
			];
		}

		if ($order['o'])
		{
			$batch[] = [
				'subscriber_id' => $subscriber_id,
				'param' => 'payment_order',
				'attr' => '',
				'value' => $order['o'],
				'created_at' => $df
			];
		}


		$oFSA->insertBatch($batch);



		// CB
		
		$this->mOrder = new \Services\Models\Order();

		$order = $this->mOrder->select('shop_id, cashback')->where(['id' => $order['o']])->limit(1)->first();
		
		if (!empty($order['shop_id']))
		{
			
			$this->mService = new \Services\Models\ServiceModel();

			$service = $this->mService->select('hash')->where(['id' => $order['shop_id']])->limit(1)->first();
			
			if (!empty($service['hash']))
			{
				$this->mTrans = new \Services\Models\Trans();

				$sKey = 'apps_shop_' . $service['hash'] . '_settings';
				$config = getParam($sKey);
					if ($config)
						$config = json_decode($config, true);

				if (!empty($config['main']['cbonpayment']))
				{
					$trans = [
						'user_id' => $subscriber_id,
						'order_id' => $order['o'],
						'amount' => $order['cashback'],
						'type' => 0,
						'date' => df()
					];
					$this->mTrans->save( $trans );
				}

			}

		}


		



		$oFS = new \Flows\Models\FlowSubscriber();

		$params = ['id' => $order['i'] ?? '', 'sku' => $order['a'] ?? '', 'price' => $order['p'] ?? '',  'order' => $order['o'] ?? '', 'link' => $order['l'] ?? '', 'subscriber_id' => $subscriber_id];

        $vars = ['trigger_order' => $order['o']];

        if ($saved)
        {
        	foreach ($saved as $k=>$v)
        	{
        		$vars['trigger_saved_method_' . $k] = $v;
        	}
        }

        $triggers = $oFS->checkTriggers('payment', $params, $vars);

        return compact('triggers');

	}


	public function start($invoice = [])
	{

	}


	public function process()
	{

	}


	public function check($invoice = [])
	{
		//
	}



	public function onSuccess($params = [])
	{

	}


	public function onFail($params = [])
	{

	}


}



