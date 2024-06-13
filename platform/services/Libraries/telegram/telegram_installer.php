<?php



require_once(dirname(__FILE__)) . '/../installer.php';


// ErpTestDevBot
// 1452192359:AAHFII-xj6aiDIIQc-DAEPXVLr02QNslUAQ


class TelegramInstaller extends ServiceInstaller
{


	public function getInfo()
	{
		$info = [
				'id' => 'telegram',
				'groups' => ['messenger'],
				'title' => __('services.title_telegram', 'Telegram'),
				'description' => ('Канал для рассылок и чат-ботов.'),
				'photo' => '/assets/services/telegram.svg',
				'channel_type' => CHANNEL_TYPE_TELEGRAM,
				'oninstall' => true,
		];

		return $info;
	}


	public function beforeUpdate($service, $channel = [])
	{
		$status = ['status' => 'error', 'message' => ''];

		$wh = $this->webhook->getWebhookUrl( $this->service->number2alpha($service['id']) );

		$url = 'https://api.telegram.org/bot' . $service['token'] . '/setWebhook?url=' . urlencode($wh);

		$response = json_decode(curl_get($url), true);

		if (empty($response['ok']))
		{
			$status['message'] .= ' Telegram: ' . (!empty($response['description']) ? $response['description'] . ' ('.$response['error_code'].')' : '') . ' ('.$wh.')';

			return $status;
		}


		$status['status'] = 'success';

		return $status;
	}




	public function check($token)
	{
		$url = 'https://api.telegram.org/bot' . $token . '/getMe';

		$response = json_decode(curl_get($url), true);

		return $response;
	}






	public function update($config, $current = [])
	{
		$status = ['status' => 'error', 'message' => __('Некорректный параметр Token.')];

		if (empty($config['token']['token']))
		{
			return $status;
		}

		if (!$this->isUnique($config))
		{
			return ['status' => 'error', 'message' => __('Канал с таким токеном уже используется в другой вашей интеграции.')];
		}


		$token = $config['token']['token'];


		$response = $this->check($token);

		if (empty($response['ok']))
		{
			$status['message'] .= ' Telegram: ' . (!empty($response['description']) ? $response['description'] . ' ('.$response['error_code'].')' : '');

			return $status;
		}


		$status['status'] = 'success';

		$status['token'] = $token;

		$status['message'] = '';

		return $status;
	}






	public function install($config)
	{
		$status = ['status' => 'error', 'message' => __('Некорректный параметр Token.')];

		if (empty($config['token']['token']))
		{
			return $status;
		}


		if (!$this->isUnique($config))
		{
			return ['status' => 'error', 'message' => __('Канал с таким токеном уже используется в другой вашей интеграции.')];
		}

		$token = $config['token']['token'];

		$response = $this->check($token);

		if (empty($response['ok']))
		{
			$status['message'] .= ' Telegram: ' . (!empty($response['description']) ? $response['description'] . ' ('.$response['error_code'].')' : '');

			return $status;
		}


		$status['status'] = 'success';

		$status['token'] = $token;

		$status['client'] = ['id' => $response['result']['id'], 'name' => $response['result']['username']];

		$status['channel_name'] = $response['result']['first_name'] . ' (@' . $response['result']['username'] . ')';

		$status['channel_id'] = $response['result']['username'];

		$status['uniq'] = md5($token . ':' .  $this->info['id']);

		$status['info'] = sprintf(__('Телеграм бот с именем @%s успешно добавлен.'), $response['result']['username']);

		return $status;

	}



	public function beforeDelete($service)
	{
		$status = ['status' => 'error', 'message' => __('Ошибка при удалении приложения. Попробуйте, пожалуйста, еще раз.')];

		if (empty($service['token']))
		{
			return $status;
		}

		$cp = json_decode($service['client_params'], true);
		$tp = json_decode($service['token_params'], true);

		$response = json_decode(curl_get('https://api.telegram.org/bot' . $service['token'] . '/setWebhook?url='), true);


		// l($service, 'tg-delete'); l($response, 'tg-delete');


		$status['status'] = 'success';

		

		return $status;

	}




}