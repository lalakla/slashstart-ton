<?php


require_once(dirname(__FILE__)) . '/service.php';


/*
INSERT INTO chats_messages (`message_text`, `message`, `message_parsed`, `worker`, chat_id, external_id, has_shortcuts, is_in, is_sent, is_sent_error, errors, message_shortcuts, message_info, message_queue) SELECT `message_text`, `message`, `message_parsed`, 0, 0, 0, 0, 0, 0, 0, 0, message_shortcuts, message_info, message_queue FROM `chats_messages`
*/

// https://core.telegram.org/bots/api#sending-files
// https://vk.com/dev/manuals
// https://vk.com/dev/bots_docs
// https://developers.facebook.com/docs/messenger-platform/introduction
// https://developers.facebook.com/docs/messenger-platform/introduction/conversation-components
// https://developers.facebook.com/docs/messenger-platform/send-messages/buttons
// https://developers.facebook.com/docs/whatsapp/pricing/




class ServiceMessanger extends Service
{

	protected $channel;

	protected $chat;

	protected $oChat;

	protected $oChatMessage;

	protected $oChannel;

	protected $subscriberChannel;


	protected $oStat;





	function __construct($settings = array())
	{


		$this->oChatMessage = new \Subscribers\Models\ChatMessage();

		$this->oChat = new \Subscribers\Models\Chat();

		$this->oChannel = new \App\Models\Channel();

		$this->oShortLink = new \App\Models\Shortlink();

		$this->oSubscriberChannel = new \Subscribers\Models\SubscriberChannel();

		$this->oStat = new \Flows\Models\Stat();

		parent::__construct($settings);


	}


	function getFile($path, $type = 'document')
	{
		$oCF = new \App\Models\ChannelFile();

		$hash = md5( $this->channel['id'] . ':' . $type . ':' . $path );

		$file = $oCF->where('hash', $hash)->first();


		if (!$file)
		{
			$response = $this->uploadFile( $path, $type );

			if (!empty($response['error']))
				return $response;


			$response['hash'] = $hash;
			$response['channel_id'] = $this->channel['id'];
			$response['type'] = constant('CHANNEL_FILE_TYPE_' . strtoupper($type));
			$response['file'] = $path;
			$response['user_id'] = $this->channel['user_id'];


			$oCF->save($response);

			$response['id'] = $oCF->getInsertID();

			$file = $response;

		}


		return $file;
	}





	function getSubscriptionUrl($token = '', $widgetId = 0, $widget = [])
	{
		return '';
	}



	function mkPayload($payload)
	{

		return mkPayload($payload);

	}


	function mkRedirect($link, $payload = [])
	{

		$url = $this->oShortLink->new($link, ['payload' => $payload]);

		return $url['url'];
	}








	function setSubscriberAndChannel($subscriber, $channel, $chat = [], $subscriberChannel = [])
	{
		$this->subscriber = is_numeric($subscriber) ? $this->oSubscriber->getById( $subscriber ) : $subscriber;

		$this->channel = is_numeric($channel) ? $this->oChannel->getById( $channel ) : $channel;

		$this->chat = $chat ? $chat : $this->oChat->getBySubscriberAndChannel( $this->subscriber['id'], $this->channel['id'] );

		$this->subscriberChannel = $subscriberChannel ? $subscriberChannel : $this->oSubscriberChannel->getBySubscriberAndChannel( $this->subscriber['id'], $this->channel['id'] );

		if (!$this->subscriber || !$this->channel || !$this->chat)
			return false;

		return true;

	}


	function getChat()
	{
		return $this->chat;
	}



	function queue($data)
	{
		$result = ['status' => 'error'];

		$queueData = [
			'service' => $this->config,
			'subscriber' => $this->subscriber,
			'channel' => $this->channel,
			'subscriber_channel' => $this->subscriberChannel,
			'chat' => $this->chat,
			'trigger_params' => !empty($data['trigger_params']) ? $data['trigger_params'] : [],
		];


		if (!empty($this->needSendCopyright) && !empty($data['message']))
		{
			$data['message']['need_send_copy'] = 1;

			$this->needSendCopyright = false;
		}

		$message = [
			'flow_item_id' => !empty($data['flow_item_id']) ? (int)$data['flow_item_id'] : 0,
			'flow_state_id' => !empty($data['flow_state_id']) ? (int)$data['flow_state_id'] : 0,
			'flow_id' => !empty($data['flow_id']) ? (int)$data['flow_id'] : 0,
			'input_state' => !empty($data['input_state']) ? (int)$data['input_state'] : 0,
			'worker' => $this->subscriber['id'] % SERVICES_MESSANGER_WORKERS,
			'channel_type' => $this->channel['type'],
			'channel_id' => $this->channel['id'],
			'is_queued' => 1,
			'priority' => !empty($data['priority']) ? (int)$data['priority'] : 0,
			'message_queue' => json_encode($queueData),
			'chat_id' => $this->chat['id'],
			'subscriber_id' => $this->subscriber['id'],
			'is_in' => 0,
			'has_shortcuts' => !empty($data['message']['s']) ? 1 : 0,
			// 'message' => htmlemoji(_json_encode($data['message'])),
			'message' => json_encode($data['message']),
		];


		if ($this->oChatMessage->save( $message ))
		{
			$message['id'] = $this->oChatMessage->getInsertID();

			$this->notifyWorker( $message['worker'], $message );


			if (!empty($message['flow_id']))
			{
				$this->oStat->save([
					'item_id' => $message['flow_item_id'],
					'subscriber_id' => $this->subscriber['id'],
					'flow_id' => $message['flow_id'],
					'channel_id' => $this->channel['id']
				]);
			}


			return ['status' => 'success', 'id' => $this->oChatMessage->getInsertID()];
		}

		return $result;

	}



	function notifyWorker($worker, $message = [])
	{
		$project = \Platform::getProject( );

		$message['project_id'] = $project['id'];

		$message['project_host'] = $project['project_host'];


		$lockKey = 'mq_' . $message['project_id'] . '_' . $message['subscriber_id'];
		rds()->rPush($lockKey, $message['id']); // на 5 секунд


		$qprId = $project['id'];

		$userQueueName = sprintf(QUEUES_PROJECT_MESSAGES_USER, $qprId, $message['subscriber_id']);


		


		// Добавляем в очередь сообщений самого проекта название очереди сообщений пользователя
		$queueName = sprintf(QUEUES_PROJECT_MESSAGES, $qprId);
		rds()->rPush($queueName, $userQueueName);


		// Очередь пользователя в проекте, все сообщения идут по очереди
		rds()->rPush($userQueueName, encrypt(_json_encode($message)));


		// +1 в очередь сообщений проекта
		rds()->zIncrBy(QUEUES_PROJECTS_MESSAGES_COUNT, 1, $qprId);
		

		// $oQ = new \App\Models\Queue();
		// $oQ->queue( $message, 1 );

	}




	function setData($data)
	{
		$this->data = $data;


		$this->setVars( $data['message_queue']['subscriber'], $data['message_queue'] );

		if (!empty($data['message_queue']['trigger_params']['vars']))
		{
			$this->appendVars($data['message_queue']['trigger_params']['vars']);
		}



	}







	function send($data)
	{

		$result = [];

		$parsed = [];

		$errors = [];

		$isBlocked = 0;


		$this->setData( $data );



		$data = $this->replaceLinks($data);



		$shortcuts = $parsedShortcuts = [];

		if (!empty($data['message']['s']))
		{
			$buttons = [];

			$sn = 0;

			foreach ($data['message']['s'] as $id=>$b)
			{
				$sn ++;

				$payload = ['b' => 'sc:' . $id, 'u' => $this->channel['user_id'], 'm' => $data['id']];

				$st = $this->parseVars( $b['t'] );

				$buttons[] = [[ 'title' => htmlemojiDecode($st), 'payload' => $payload ]];


				$parsedShortcuts[$st] = $parsedShortcuts['/' . $sn] = ['t' => $b['t'], 'i' => $id];

			}

			$onetime = !empty($data['message']['hs']) ? 1 : 0;
			$aslist = !empty($data['message']['wql']) ? 1 : 0;

			$shortcuts = ['buttons' => $buttons, 'onetime' => $onetime, 'aslist' => $aslist];
		}


		if (empty($data['message']['m']))
			return;

		$isCopySend = false;
		$lastTextKey = 0;
		if (!empty($data['message']['need_send_copy']))
		{
			foreach ($data['message']['m'] as $k => $message)
				if ($message['t'] == 1 || $message['t'] == 8)
					$lastTextKey = $k;
		}



		foreach ($data['message']['m'] as $k => $message)
		{
			$method = 'sendMessage';

			$msg = ['_id' => $data['id']];

			// 1 : 'message',
			// 2 : 'image',

			// 5 : 'audio',
			// 6 : 'video',
			// 7 : 'file',

			// 8 : 'input'


			switch ($message['t'])
			{
				case 1:
				case 8:
					// $msg['message'] = $this->parseVars( $message['c'] ); // Уже заменено в replaceLinks, так как шорткоды их генерируют
					$msg['message'] = htmlemojiDecode($message['c']);

					$parsed[$k] = ['t' => 1, 'c' => $msg['message']];

					if (!empty($data['message']['need_send_copy']) && $k == $lastTextKey)
					{
						$msg['need_send_copy'] = $isCopySend = true;
					}

				break;
				case 2:
				case 5:
				case 6:
				case 7:

					if ($message['t'] == 2)
						$method = 'sendPhoto';
					elseif ($message['t'] == 5)
						$method = 'sendAudio';
					elseif ($message['t'] == 6)
						$method = 'sendVideo';
					elseif ($message['t'] == 7)
						$method = 'sendDocument';

					if (!empty($message['u']))
					{
						$msg['url'] = $this->parseVars( $message['u'] );
						$msg['title'] = !empty($message['c']) ? $this->parseVars( $message['c'] ) : '';


						$parsed[$k] = ['t' => htmlemojiDecode($message['t']), 'u' => $msg['url'], 'c' => htmlemojiDecode($msg['title'])];
					}


				break;

			}


			if (!empty($message['b']))
			{
				$buttons = [];

				$rown = 0;
				$rowSize = 0;

				foreach ($message['b'] as $id=>$b)
				{
					$payload = ['b' => $b['i'], 'u' => $this->channel['user_id'], 'm' => $data['id']];

					$buttons[$rown][] = [ 'title' => $this->parseVars( htmlemojiDecode($b['t']) ), 'link' => !empty($b['l']) ? $b['l'] : '', 'width' => $b['w'], 'payload' => $payload, 'source' => $b];

					$bw = $b['w'] ? $b['w'] : 100;

					$rowSize += $bw;

					if ($rowSize >= 100)
					{
						$rowSize = 0;
						$rown++;
					}
				}

				// log_message('critical', print_r($buttons, 1));

				$msg['buttons'] = $buttons;
			}
			elseif (!empty($shortcuts))
			{
				$msg['shortcuts'] = $shortcuts;
				$shortcuts = [];
			}



			// d($data); dd($msg);



			$status = $this->{$method}( $msg );

			l($msg, 'msg', 7);





			if (!empty($status['error']))
			{
				$errors[] = $status['message'];

				if (!empty($status['is_blocked']))
				{

					$errors['is_blocked'] = $isBlocked = 1;
				}
			}



			$result[] = $status;




			if (!empty($message['i']))
			{
				$data['input_state'] = !empty($data['input_state']) ? $data['input_state'] : $message['i']['i'];

				break;
			}



		}



		$data['message_info'] = $result;
		$data['message_parsed'] = $parsed;


		$result = $this->afterSend( $data, $parsedShortcuts, $errors );



		return $result;

	}




	function afterSend($message = [], $shortcuts = [], $errors = [])
	{
		// Проверка частичной отправки


		$result = ['id' => $message['id']];


		if ($errors)
		{

			// file_put_contents('/var/www/rplto/data/www/rplto.net/writable/msn.log', print_r($errors, 1));



			$result['is_sent_error'] = 1;

			$result['errors'] = _json_encode($errors);

			if (!empty($errors['is_blocked']))
			{
				unset( $errors['is_blocked'] );

				$this->oSubscriberChannel->disableChannel([
					'id' => $this->subscriberChannel['id'],
					'disabled_reason' => join(', ', $errors),
				]);

			}
		}

		$result['input_state'] = !empty($message['input_state']) ? $message['input_state'] : 0;

		$result['external_id'] = $message['message_info'][0]['external_id'] ?? 0;

		$result['message_links'] = !empty($message['message_links']) ? json_encode($message['message_links']) : '';
		// $result['message_parsed'] = htmlemoji(_json_encode($message['message_parsed']));
		$result['message_parsed'] = json_encode($message['message_parsed']);


		// $result['message_info'] =  htmlemoji(_json_encode($message['message_info']));
		$result['message_info'] = '';
		




		$result['message_queue'] = '';

		$result['message_shortcuts'] = json_encode($shortcuts);


		$result['is_sent'] = 1;
		$result['sent_at'] = df();




		if ($result['input_state'])
		{
			$sst = [];

			$sst['id'] = $this->subscriberChannel['id'];
			$sst['input_state_item'] = $message['flow_item_id'];
			$sst['input_state_input'] = $result['input_state'];
			$sst['input_flow_state_id'] = $message['flow_state_id'];


			$this->oSubscriberChannel->save($sst);
		}


		if (!empty($this->chat) && !empty($message['message']['m']))
		{
			$text = [];
			foreach ($message['message']['m'] as $m)
				if (!empty($m['c']))
					$text[] = $m['c'];

			$this->oChat->save([
				'id' => $this->chat['id'],
				'last_message' => h(htmlemoji( join(' ', $text) )),
				'last_message_date' => df(),
			]);
		}



		if ($this->oChatMessage->save( $result ))
		{
			$result['status'] = 'success';

			return $message;
		}

		return ['status' => 'error', 'message' => 'System Error. Message was sent but status not updated'];

	}




}



