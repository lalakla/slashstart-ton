<?php



require_once(dirname(__FILE__)) . '/../webhook.php';


// ErpTestDevBot
// 1452192359:AAHFII-xj6aiDIIQc-DAEPXVLr02QNslUAQ

// https://mf.my/services/4E2222E-a08cf753289f1d870019a5ca4a8a89ab/webho8ok


class TelegramWebhook extends ServiceWebhook
{


	var $allowedMethods = array('process');





	public function process()
	{
		$updates = file_get_contents('php://input');

		//l($updates, 'tg-wh', true);
		
		if (!empty($_GET['f']))
		{
			// $updates = @file_get_contents( dirname(__FILE__) . '/updates-'.preg_replace('#[^0-9a-z]#si', '', $_GET['f']).'.txt' );
		}

		if (!LOCALHOST && empty($_GET['f']))
		{
			// file_put_contents(dirname(__FILE__) . '/updates-' . time() . '.txt', $updates);
		}

		$updates = $updatesSource = json_decode($updates, true);

		$updates = $this->extractData($updates);

		// l($updates, 'tg-updates');

		if (empty($updates['telegram_id']))
		{
			return false;
		}

		// Подписка из виджета
		$attempt = []; $fromRef = null;

		if ($updates['type'] == 'message'
			&& !empty($updates['message'])
			&& strpos($updates['message'], '/start') === 0
			&& preg_match('#/start\s([0-9a-z\-]{16,})#si', $updates['message'], $m))
		{
			$token = $m[1];

			$oSA = new \Subscribers\Models\SubscriberAttempt();

			$attempt = $oSA->where('hash', $token)->first();

			if (!$attempt || $attempt['is_completed'])
			{
				die('n/a');
			}

			$attempt['data'] = json_decode($attempt['data'], true);
		}else if ($updates['type'] == 'message'
                && !empty($updates['message'])
                && strpos($updates['message'], '/start') === 0){ // Подписка от реферала
            $m = explode(' ', $updates['message']);
            if (isset($m[1])){
                $fromRef = $m[1];
            }
        }

		$oFS = new \Flows\Models\FlowSubscriber();
		$oS = new \Subscribers\Models\Subscriber();
		$oSG = new \Subscribers\Models\SubscriberGroup();
		$oSC = new \Subscribers\Models\SubscriberChannel();
		$oC = new \App\Models\Channel();
		$oChat = new \Subscribers\Models\Chat();
		$oCM = new \Subscribers\Models\ChatMessage();

		$data = ['telegram_id' => $updates['telegram_id']];

		if ($fromRef!=null){
            $data['from_ref'] = $fromRef;
        }

		$subscriber = $oS->getSubscriberWithChannels($data);

		if ($attempt)
		{
			if ($subscriber)
			{
				$attempt['subscriber_id'] = $subscriber['id'];
			}
			else
			{
				$subscriber = $oS->getSubscriberWithChannels(['id' => $attempt['subscriber_id']]);
			}
		}

		$channel = $oC->getByServiceAndType( $this->config['id'], CHANNEL_TYPE_TELEGRAM );

		if (!$channel)
		{
			// Ошибка что канал не добавлен или удален

			return false;
		}

		$channelDataConfig = json_encode(['from' => $updates['telegram_id'], 'chat' => $updates['chat_id'], 'vars' => $attempt['data']['vars'] ?? []]);

		if (empty($subscriber['channels'][ $channel['id'] ]))
		{
			if (!empty($subscriber['id']))
			{
				$data['id'] = $subscriber['id'];
			}

			$data['has_channels'] = 1;


			$channelData = [
				'channel_id' => $channel['id'],
				'channel_type' => $channel['type'],
				'channel_handle' => $channel['external_id'],
				'service_id' => $this->config['id'],
				'user_id' => $updates['telegram_id'],
				'user_name' => trim($updatesSource['message']['from']['first_name'] . ' ' . ($updatesSource['message']['from']['last_name'] ?? '')),
				'user_handle' => $updatesSource['message']['from']['username'] ?? '',
				'config' => $channelDataConfig,
			];


			if (empty($subscriber['name']))
			{
				$data['name'] = $channelData['user_name'];
			}
			if (empty($subscriber['firstname']))
				$data['firstname'] = $updatesSource['message']['from']['first_name'] ?? '';
			if (empty($subscriber['lastname']))
				$data['lastname'] = $updatesSource['message']['from']['last_name'] ?? '';


			$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/getUserProfilePhotos?user_id=' . $data['telegram_id'] . '&offset=0&limit=1';
			$photos = json_decode(curl_get($url), true);
			$photoId = '';

			if (!empty($photos['result']['photos']))
			{
				// file_put_contents(dirname(__FILE__) . '/updates-photos-' . time() . '.txt', json_encode($photos['result']['photos'][0]));

				foreach ($photos['result']['photos'][0] as $ph)
				{
					if ($ph['width'] == 640)
						$photoId = $ph['file_id'];
					elseif ($ph['width'] == 320 && !$photoId)
						$photoId = $ph['file_id'];
					elseif ($ph['width'] == 160 && !$photoId)
						$photoId = $ph['file_id'];
				}
				if ($photoId)
				{
					$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/getFile?file_id=' . $photoId;
					$photo = json_decode(curl_get($url), true);

					// file_put_contents(dirname(__FILE__) . '/updates-photo-' . time() . '.txt', json_encode($photo));

					if (!empty($photo['result']['file_path']))
					{
						$url = 'https://api.telegram.org/file/bot' . $this->config['token'] . '/' . $photo['result']['file_path'];

						$photoUrl = $this->uploadFile($url, $photo['result']['file_path']);

						$data['photo'] = $photoUrl;

					}
				}
			}



			if (!$subscriber)
			{
				$subscriber = $oS->addSubscriberWithChannel($data, $channelData);
			}
			else
			{
				$subscriber = $oS->addSubscriberToChannel($subscriber['id'], $channelData);

				// Обновление новых данных
				$oS->save($data);

			}
		}
		else
		{
			$oSC->save([
				'id' => $subscriber['channels'][ $channel['id'] ]['id'],
				'config' => $channelDataConfig,
			]);
		}

		if (!$subscriber)
		{
			// Системная ошибка

			return false;
		}

		if (!empty($subscriber['channels'][ $channel['id'] ]['is_disabled']))
		{
			$oSC->enableChannel($subscriber['channels'][ $channel['id'] ]['id'], $subscriber['id']);
		}

		// Подписка на группу
		if ($updates['type'] == 'message'
			&& !$attempt
			&& !empty($updates['message'])
			&& strpos($updates['message'], '/start') === 0
			&& preg_match('#/start\s([0-9a-z\-]{2,})#si', $updates['message'], $m))
		{

			$group = $oSG->get( $m[1] );
			if ($group)
			{
				$oS->addToGroup($subscriber['id'], $group['id']);
			}

		}

		if ($updates['type'] == 'edited_message')
		{
			// обновление сообщения
		}

		// Нажание на кнопку
		if ($updates['type'] == 'callback_query')
		{
			$payload = getPayload( $updates['message'] ?? '' );



			if (!$payload)
			{
				return false;
			}


			$payload['b'] = alphaID($payload['b'], true);
			$payload['m'] = alphaID($payload['m'], true);
			$payload['u'] = alphaID($payload['u'], true);


			$message = $oCM->limit(1)->find( $payload['m'] );

			if (!$message)
			{
				return false;
			}


			$imbtns = json_decode($message['message'], true);
			foreach ($imbtns['m'] as $mm)
			{
				if (!empty($mm['b'][$payload['b']]))
				{
					$imsg = [
						'is_button' => 1,
						'message_text' => $mm['b'][$payload['b']]['t'],
						'type' => 1, // тип = текст
					];

					$oChat->incomeMessage($channel, $subscriber, $imsg);

					break;
				}
			}



			$oFSA = new \Flows\Models\FlowSubscriberAttribute();

			// Проверка на повторый клик по кнопкам и повтор процесса. Сделать настройку на повтор процесса
			// $clicked = $oFSA->where('param', 'fm_' . $message['flow_item_id'] . '_b_' . $payload['b'])->first();

			// if ($clicked)
			// {
			// 	// return true;
			// }





			$now = df();


			$oSSM = new \Flows\Models\Stat();
			$oSSM->trackClick($message, $payload, $now);



			// Ответ на callback, чтобы колесико пряталось
			$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/answerCallbackQuery?callback_query_id=' . $updates['cb_id'];
			curl_get($url);


			$messageInfo = json_decode($message['message_info'], true);

			// Прячем клавиатуру, если нужно (вынести в парметр $message)
			if (!empty($imbtns['db']))
			{
				$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/editMessageReplyMarkup?reply_markup=&chat_id='.$updates['telegram_id'].'&message_id=' . $message['external_id'];
				curl_get($url);
			}

			if (!empty($imbtns['dm']))
			{
				$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/deleteMessage?chat_id='.$updates['telegram_id'].'&message_id=' . $message['external_id'];
				curl_get($url);
			}


		}

		// l($updates, 'twh', 3);

		// Сохраняем сообщение
		if (!$attempt && $updates['type'] == 'message' && !empty($updates['message']) && strpos($updates['message'], '/start') === false)
		{
			$message = [
				'external_id' => $updates['external_id'],
				'message_text' => $updates['message'],
				'type' => 1, // тип = текст
			];

			$chat = $oChat->incomeMessage($channel, $subscriber, $message);
		}

		if (!$attempt && $updates['type'] == 'message' && !empty($updates['message']) && ($updates['message'] === '/start' || $updates['message'] === 'start' || $updates['message'] === 'старт' || $updates['message'] === 'Старт'))
		{
			$message = [
				'external_id' => $updates['external_id'],
				'message_text' => 'start',
				'type' => 1, // тип = текст
			];

			$chat = $oChat->incomeMessage($channel, $subscriber, $message);
		}


		// Фото
		if ($updates['type'] == 'photo' && !empty($updates['file']['url']))
		{
			$pmsg = [['t' => 2, 'u' => $updates['file']['url']]];
			$message = [
				'type' => 2, // тип = фото
				'external_id' => $updates['external_id'],
				'message' => json_encode(['m' => $pmsg]),
				'message_parsed' => json_encode($pmsg),
				'message_file' => $updates['file']['url'],
				'file' => $updates['file'],
				'message_info' => '{"message_id":"'.$updates['external_id'].'"}',
			];

			$chat = $oChat->incomeMessage($channel, $subscriber, $message);
		}


		// Файл
		if ($updates['type'] == 'document' && !empty($updates['file']['url']))
		{
			$pmsg = [['t' => 7, 'u' => $updates['file']['url']]];
			$message = [
				'type' => 7, // тип = файл
				'external_id' => $updates['external_id'],
				'message' => json_encode(['m' => $pmsg]),
				'message_parsed' => json_encode($pmsg),
				'message_file' => $updates['file']['url'],
				'file' => $updates['file'],
				'message_info' => '{"message_id":"'.$updates['external_id'].'"}',
			];

			$chat = $oChat->incomeMessage($channel, $subscriber, $message);
		}

		// l($updates, 'tgw', true);



		if ($attempt)
		{
			$oSA->complete($attempt, $subscriber);
		}


		// if (!empty($_GET['f'])) // удалить, включено на тест
		$this->afterProcess( $subscriber, CHANNEL_TYPE_TELEGRAM );


		echo 'ok';

    }






    public function setup()
    {

    	$whurl = $this->getWebhookUrl($this->config['hash'], 'process');

    	$qurl = 'https://api.telegram.org/bot'.$this->config['token'].'/setWebhook?url=' . urlencode($whurl);

    	$result = json_decode(curl_get($qurl), true);

    	if (!empty($result['result']) && $result['result'] == 1)
    	{
    		return array('result' => 1, 'message' => $result['description']);
    	}

    	return array('result' => 0, 'message' => $result['description']);
    }





    public function getUpdatesType($update)
	{
		if (isset($update['inline_query']))
            return 'inline_query';

        if (isset($update['callback_query']))
            return 'callback_query';

        if (isset($update['edited_message']))
            return 'edited_message';

        if (isset($update['message']['text']))
            return 'message';

        if (isset($update['message']['photo']))
            return 'photo';

        if (isset($update['message']['video']))
            return 'video';

        if (isset($update['message']['audio']))
            return 'audio';

        if (isset($update['message']['voice']))
            return 'voice';

        if (isset($update['message']['contact']))
            return 'contact';

        if (isset($update['message']['location']))
            return 'location';

        if (isset($update['message']['reply_to_message']))
            return 'reply';

        if (isset($update['message']['animation']))
            return 'animation';

        if (isset($update['message']['sticker']))
            return 'sticker';

        if (isset($update['message']['document']))
            return 'document';

        if (isset($update['channel_post']))
            return 'channel_post';


        return false;

	}



	public function extractData($updates)
	{

		$type = $this->getUpdatesType($updates);


		$result = ['type' => $type];


		switch ($type)
		{
			case 'photo':
			case 'video':
			case 'audio':
			case 'document':
				// log_message('critical', print_r($updates, 1));

				if ($type == 'photo')
				{
					$fileData = array_pop( $updates['message'][$type] );
				}
				else
				{
					$fileData = $updates['message'][$type];
				}

				$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/getFile?file_id=' . $fileData['file_id'];
				$file = json_decode(curl_get($url), true);


				if (!empty($file['result']['file_path']))
				{
					$url = 'https://api.telegram.org/file/bot' . $this->config['token'] . '/' . $file['result']['file_path'];

					$fileUrl = $this->uploadFile($url, $file['result']['file_path']);

					$result['file'] = ['url' => $fileUrl, 'type' => $type == 'document' ? 'file' : $type, 'name' => basename($file['result']['file_path'])];
				}

				// log_message('critical', print_r($result, 1));

			break;
			case 'callback_query':
			case 'channel_post':
			case 'edited_message':
				$result['first_name'] = $updates[$type]['from']['first_name'];
	            $result['last_name'] = $updates[$type]['from']['last_name'] ?? '';
	            $result['username'] = $updates[$type]['from']['username'] ?? '';

	            if ($type == 'callback_query')
	            {
					$result['message'] = $updates[$type]['data'];
	            	$result['telegram_id'] = $updates[$type]['from']['id'];
	            	$result['chat_id'] = $updates[$type]['message']['chat']['id'];
            		$result['external_id'] = $updates[$type]['message']['message_id'];
            		$result['cb_id'] = $updates[$type]['id'];
	            }
		        else
		        {
		        	$result['message'] = $updates[$type]['text'];
		        	$result['telegram_id'] = $updates[$type]['from']['id'];
		        	$result['chat_id'] = $updates[$type]['chat']['id'];
            		$result['external_id'] = $updates[$type]['message_id'];

		        }
		    break;

		    case 'inline_query':
		    	 $result['telegram_id'] = $updates['inline_query']['from']['id'];
			break;

		}


        if (!empty($updates['message']['text']))
        	$result['message'] = $updates['message']['text'];

        if (!empty($updates['message']['chat']['id']))
        	$result['chat_id'] = $updates['message']['chat']['id'];

        if (!empty($updates['message']['from']['id']))
        	$result['telegram_id'] = $updates['message']['from']['id'];

        if (!empty($updates['message']['message_id']))
        	$result['external_id'] = $updates['message']['message_id'];

        return $result;

	}




}