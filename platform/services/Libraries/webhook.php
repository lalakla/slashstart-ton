<?php


require_once(dirname(__FILE__)) . '/service.php';



class ServiceWebhook extends Service
{





	function __construct($settings = array())
	{
		$this->mSubscriberGroup = new \Subscribers\Models\SubscriberGroup();


		parent::__construct($settings);

	}


	function getWebhookUrl($serviceId, $method = 'process')
	{
		$serviceIdKey = md5($serviceId . ':' . SERVICES_WEBHOOK_QUERY_KEY . ':' . $method);

		$url = 'https://' . PROJECT_DOMAIN_SYSTEM . '/services/'.$serviceId.'-'.$serviceIdKey.'/webhook' . ($method != 'process' ? '/' . $method : '');

		return $url;
	}




	function afterProcess($subscriber, $type)
	{
		$oFS = new \Flows\Models\FlowSubscriber();

		$oFS->processSubscriber( $subscriber['id'] );

		// 1-4-6-0-6, pid - type - worker (- limit - subscriber)

		$workerId = $subscriber['id'] % SERVICES_MESSANGER_WORKERS;

		// $cdata = [
		// 	PROJECT_ID,
		// 	(int)$type,
		// 	$workerId,
		// 	0,
		// 	$subscriber['id']
		// ];


		// Закомментировано, итак быстро обрабатываются сообщения, параллельно не успевает, дубли ?
		$oCM = new \Subscribers\Models\ChatMessage();
		$oCM->processSubscriber( $subscriber['id'] );
	}




	function click2chat($channel, $subscriber, $message, $payload)
	{
		$oChat = new \Subscribers\Models\Chat();

		$isSc = strripos($payload['b'], 'sc:') === 0 ? 1 : 0;

		$imbtns = json_decode($message['message'], true);


		if ($isSc)
		{
			$scKey = substr($payload['b'], 3);
			if (!empty($imbtns['s'][$scKey]))
			{
				$imsg = [
					'external_id' => !empty($payload['external_id']) ? $payload['external_id'] : '',
					'is_button' => 1,
					'is_shortcut' => $isSc,
					'message_text' => $imbtns['s'][$scKey]['t'],
					'type' => 1, // тип = текст
				];

				$oChat->incomeMessage($channel, $subscriber, $imsg);
			}

		}
		else
		{
			foreach ($imbtns['m'] as $mm)
			{
				if (!empty($mm['b'][$payload['b']]))
				{
					$imsg = [
						'external_id' => !empty($payload['external_id']) ? $payload['external_id'] : '',
						'is_button' => 1,
						'is_shortcut' => $isSc,
						'message_text' => $mm['b'][$payload['b']]['t'],
						'type' => 1, // тип = текст
					];

					$oChat->incomeMessage($channel, $subscriber, $imsg);

					break;
				}
			}
		}

	}



}



