<?php namespace Services\Models;


use CodeIgniter\Model;

define('SERVICES_ALPHA_ID_KEY', 'egrnkvch323d9d17c053d20155c81874f053cadb');


require_once( dirname(__FILE__) . '/../config.php');

class ServiceModel extends \CodeIgniter\Model
{

	protected $table      = 'services';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['hash', 'uniq', 'user_id', 'active', 'name', 'title', 'token_expires', 'token', 'token_params', 'client_params'];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;



    protected $beforeInsert = ['encryptData'];
	protected $beforeUpdate = ['encryptData'];

	protected $afterFind = ['decryptData'];

	protected $afterUpdate = ['clearCache'];
	protected $afterDelete = ['clearCache'];


	protected function clearCache($data)
	{
		$ids = is_array($data['id']) ? $data['id'] : [$data['id']];

		foreach ($ids as $id) deleteCache('app_' . $id);

		deleteCache('ch_messangers');
	}



	protected function encryptData(array $data)
	{

		if (!empty($data['data']['token']))
		{
			$data['data']['token'] = encrypt($data['data']['token']);
		}

		if (!empty($data['data']['token_params']))
		{
			$data['data']['token_params'] = encrypt($data['data']['token_params']);
		}

		return $data;
	}


	protected function decryptData(array $data)
	{

		if (empty($data))
			return;

		if ($data['singleton'])
		{
			if (!empty($data['data']['token']))
			{
				$data['data']['token'] = decrypt($data['data']['token']);
			}

			if (!empty($data['data']['token_params']))
			{
				$data['data']['token_params'] = decrypt($data['data']['token_params']);
			}

			return $data;
		}

		foreach ($data['data'] as $k=>$v)
		{
			if (!empty($v['token']))
			{
				$data['data'][$k]['token'] = decrypt($v['token']);
			}

			if (!empty($v['token_params']))
			{
				$data['data'][$k]['token_params'] = decrypt($v['token_params']);
			}

		}


		return $data;
	}




	function getInstalledServices()
	{
		$services = $this->select('id, name')->find();

		return $services;
	}

	function getPaymentServices()
	{
		$services = $this->whereIn('name', ['yoomoney', 'yookassa', 'getcourse', 'prodamus', 'leadpay'])->find();

		foreach ($services as $k=>$v)
		{
			$methods = [];
			switch ($v['name'])
			{
				case 'getcourse':
					$methods = [
						// 'CARD' => ['title' => __('Банковской картой')],
					];
				break;
				case 'yoomoney':
					$methods = [
						'AC' => ['title' => __('Банковская карта')],
						'PC' => ['title' => __('Юмани кошелек')],
					];
				break;

				case 'yookassa':
					$methods = [
						'bank_card' => ['title' => __('Банковская карта (авто)')],
						'yoo_money' => ['title' => __('Юмани кошелек (авто)')],
						'installments' => ['title' => __('Заплатить по частям')],
						'sberbank' => ['title' => __('СберБанк Онлайн')],
						'qiwi' => ['title' => __('QIWI Кошелек')],
						'webmoney' => ['title' => __('Webmoney')],
						'apple_pay' => ['title' => __('Apple Pay (авто)')],
						'google_pay' => ['title' => __('Google Pay (авто)')],
						'alfabank' => ['title' => __('Альфа-Клик')],
						'tinkoff_bank' => ['title' => __('Тинькофф')],
						'b2b_sberbank' => ['title' => __('СберБанк Бизнес Онлайн')],
						'mobile_balance' => ['title' => __('Баланс телефона')],
						'cash' => ['title' => __('Наличные')],
					];
				break;

			}

			if ($methods)
			{
				$services[$k]['methods'] = $methods;
			}

			$cp = json_decode($v['client_params'], true);

			if (!empty($cp['account']))
				$services[$k]['account'] = $cp['account'];

		}

    	return $services;

	}




    function getByName($name)
    {
    	$service = $this->where(['name' => $name])->first();

    	return $service;

    }


    function getAllByName($name)
    {
    	$services = $this->where(['name' => $name])->findAll();



    	return $services;

    }



	function getById($id)
    {
    	$cKey = 'app_' . $id;

    	if (! $service = getCache($cKey) )
    	{
    		$service = $this->where(['id' => $id])->first();

    		if ($service)
	    		setCache($cKey, $service, WEEK);

    	}

    	return $service;
    }



	function get($key, $id = 0)
	{
		$id = $id ? $id : $this->alpha2number( $key );

		$app = $this->getById( $id );

		return $app;
	}





	function updateData($key, $data)
	{
		$id = $this->alpha2number( $key );

		foreach ($data as $k=>$v)
		{
			if (is_array($v))
				$data[$k] = json_encode($v);
		}

		$data['id'] = $id;


		return $this->save($data);
	}


	function number2alpha($num)
	{
		return alphaID($num, false, 7, SERVICES_ALPHA_ID_KEY);
	}

	function alpha2number($alpha)
	{
		return alphaID($alpha, true, 7, SERVICES_ALPHA_ID_KEY);
	}





}


