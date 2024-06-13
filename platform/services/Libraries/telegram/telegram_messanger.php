<?php



// ALTER TABLE `wpef_chats_messages` ADD `flow_item_id` BIGINT NOT NULL DEFAULT '0' AFTER `channel_type`;
// ALTER TABLE `wpef_subscribers_groups` ADD `handle` VARCHAR(32) NOT NULL AFTER `user_id`, ADD INDEX (`handle`);



require_once(dirname(__FILE__)) . '/../messanger.php';




class TelegramMessanger extends ServiceMessanger
{


	function apiQuery($method, $data = [])
	{
		$_x = microtime(true);

		$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/' . $method;


		$ch = curl_init();


		curl_setopt_array($ch, array(
		    CURLOPT_URL => $url,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_SSL_VERIFYPEER => false,
		    CURLOPT_SSL_VERIFYHOST => false,
		    CURLOPT_FOLLOWLOCATION => true,
		    CURLOPT_POST => true,
		    CURLOPT_POSTFIELDS => $data,
		));

		$response = curl_exec($ch);

		$response = json_decode($response, true);


		$_y = microtime(true);


		$info = curl_getinfo($ch);
		// log_message('critical', print_r($info, 1));
		// log_message('critical', number_format($_y - $_x, 5));


		return $response;
	}



	function getSubscriptionUrl($token = '', $widgetId = 0, $widget = [])
	{
		return [
			'd' => 'https://t.me/' . $this->config['client_params']['name'] . '?start=' . $token,
			'm' => 'tg://resolve?domain='.$this->config['client_params']['name'].'&start=' . $token,
		];
	}





	function uploadFile($path, $type)
	{
		$method = 'sendDocument';
		$key = 'document';

		switch ($type)
		{
			case 'photo':
			case 'audio':
			case 'video':

				$key = $type;
				$method = 'send' . ucfirst($type);

			break;
		}



		$file = new CURLFile($path);

		$query = [
			'chat_id' => $this->subscriberChannel['user_id'],
			$key => $file,
		];

		$response = $this->apiQuery( $method, $query );


		if (empty($response['ok']))
		{
			return ['error' => 1, 'message' => $response['description']];
		}



		if (!empty($response['result'][$key]['file_id']))
		{
			return ['external_id' => $response['result'][$key]['file_id']];
		}


		$file = array_pop($response['result'][$key]);

		if (empty($file['file_id']))
		{
			return ['error' => 1, 'message' => 'file_id not found'];
		}



		return ['external_id' => $file['file_id']];



	}


	/*
		id - internal id
		https://core.telegram.org/bots/api#sendphoto

		chat_id
		photo - InputFile or String
		title = caption


	*/

	function sendPhoto($params = [])
	{
		return $this->sendAttachment( $params, 'photo' );
	}


	function sendVideo($params = [])
	{
		return $this->sendAttachment( $params, 'video' );
	}


	function sendDocument($params = [])
	{
		return $this->sendAttachment( $params, 'document' );
	}



	function sendAudio($params = [])
	{
		return $this->sendAttachment( $params, 'audio' );
	}



	function mkButtons($buttons)
	{
		$result = [];

		// l($buttons);
		// l($this);

		foreach ($buttons as $row=>$btns)
		{
			foreach ($btns as $b)
			{
				$button = [];

				$button['text'] = $b['title'];


				if (!empty($b['payload']))
					$button['callback_data'] = $this->mkPayload( $b['payload'] );


				if (!empty($b['link']))
				{
					// Ссылки уже заменены при отправке
					// $link = $this->mkRedirect($b['link'], !empty($button['callback_data']) ? $button['callback_data'] : []);

					$button['url'] = $b['link'];

				}


				if (!empty($b['source']['s']) && strpos($b['source']['s'], '[app') !== false)
				{

					$button['web_app'] = [
						'url' => $button['url'],
					];

					unset($button['url'], $button['callback_data']);

				}


				$result[$row][] = $button;
			}

		}

		// l($result, 'sc');

		return $result;
	}


	function mkShortcuts($shortcuts)
	{

		$result = [];


		$result['resize_keyboard'] = !empty($shortcuts['resize']) ? true : false;
		$result['one_time_keyboard'] = !empty($shortcuts['onetime']) ? true : false;
		$result['keyboard'] = [];

		$result['resize_keyboard'] = true;


		foreach ($shortcuts['buttons'] as $row=>$buttons)
		{
			foreach ($buttons as $b)
			{
				$button = [];

				$button['text'] = $b['title'];

				if (!empty($b['payload']))
					$button['callback_data'] = $this->mkPayload( $b['payload'] );

				$result['keyboard'][$row][] = $button;
			}
		}


		return $result;
	}


	function sendAttachment($params, $type = 'document')
	{
		if (empty($params['file']) && empty($params['url']))
		{
			return ['error' => 1, 'message' => 'url or file required'];
		}



		if (empty($params['url']))
		{
			$file = $this->getFile( $params['file'], $type );

			if (!empty($file['error']))
				return $file;
		}
		else
		{
			$file = ['external_id' => $params['url']];

		}



		$method = 'sendDocument';
		$key = 'document';

		switch ($type)
		{
			case 'photo':
			case 'audio':
			case 'video':

				$key = $type;
				$method = 'send' . ucfirst($type);

			break;
		}


		$query = [
			'chat_id' => $this->subscriberChannel['user_id'],
			$key => $file['external_id']
		];

		if (!empty($params['title']))
			$query['caption'] = $params['title'];


		if (!empty($params['buttons']))
			$query['reply_markup'] = json_encode(['inline_keyboard' => $this->mkButtons($params['buttons']) ]);

		if (!empty($params['shortcuts']))
			$query['reply_markup'] = json_encode( $this->mkShortcuts($params['shortcuts']) );



		$_x = microtime(true);
		$result = $this->apiQuery( $method, $query );
		$_y = microtime(true);




		if (empty($result['result']['message_id']))
		{
			$blocked = stripos($result['description'], 'blocked') !== false || stripos($result['description'], 'заблокировал') !== false || stripos($result['description'], '\u0437\u0430\u0431\u043b\u043e\u043a\u0438') !== false ? 1 : 0;

			return ['error' => 1, 'message' => $result['description'], 'is_blocked' => $blocked];
		}


		$status = [
			'id' => $params['_id'],
			'external_id' => $result['result']['message_id'],
			'is_sent' => 1,
			'sent_at' => date('Y-m-d H:i:s'),
			'message_info' => $result
		];


		return $status;



	}




	function sendMessage($params = [])
	{
		// https://core.telegram.org/bots/api#sendmessage


		$query = [
			'chat_id' => $this->subscriberChannel['user_id'],
			'text' => $params['message'],
			'parse_mode' => 'HTML',
		];


		if (!empty($params['buttons']))
			$query['reply_markup'] = json_encode(['inline_keyboard' => $this->mkButtons($params['buttons']) ]);


		if (!empty($params['shortcuts']))
			$query['reply_markup'] = json_encode( $this->mkShortcuts($params['shortcuts']) );




		$result = $this->apiQuery( 'sendMessage', $query );


		if (empty($result['result']['message_id']))
		{
			$blocked = stripos($result['description'], 'blocked') !== false ? 1 : 0;

			return ['error' => 1, 'message' => $result['description'], 'is_blocked' => $blocked];
		}


		$status = [
			'id' => $params['_id'],
			'external_id' => $result['result']['message_id'],
			'is_sent' => 1,
			'sent_at' => date('Y-m-d H:i:s'),
			'message_info' => $result
		];

		return $status;

	}



}