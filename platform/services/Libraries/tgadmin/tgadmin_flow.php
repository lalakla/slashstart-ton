<?php



require_once(dirname(__FILE__)) . '/../flow.php';



class TgadminFlow extends ServiceFlow
{


	var $allowedMethods = ['subs', 'stats', 'ban', 'unban'];



	function subs($data = [])
	{
		$params = [
			'channel' => $this->subscriberChannel,
			'subscriber' => $this->subscriber,
			'config' => $data,
		];

		

		$oSA = new \Subscribers\Models\SubscriberAttribute();


		$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/getChatMember?chat_id=' . $data['chat_id'] . '&user_id=' . $this->subscriberChannel['user_id'];
		$response = json_decode(curl_get($url), true);


		$status = $response['result']['status'] ?? '';


		// l($response, 'adm');


		$status == 'member' || $status == 'creator' || $status == 'administrator';

		if (!empty($data['subs_attr_id']))
		{
			$attr = $oSA->getById( $data['subs_attr_id'] );
			if ($attr)
			{
				$oSA->saveAttr( $this->subscriber['id'], $data['subs_attr_id'], $status );
			}
		}


		return true;

	}




	function stats($data = [])
	{
		$params = [
			'channel' => $this->subscriberChannel,
			'subscriber' => $this->subscriber,
			'config' => $data,
		];

		

		$oSA = new \Subscribers\Models\SubscriberAttribute();
		$oM = new \Services\Models\TgadminMessage();


		$lockKey = 'tgadm_' . PROJECT_ID . '_' . $data['chat_id'];
		if (rds()->set($lockKey, 1, ['nx', 'ex' => 86400]))
		{
			$oM->where(['date <=' => strtotime('-30 days')])->delete();
		}
		
		
		$days = $data['stats_days'] ?? 0;
		if ($days < 1 || $days > 30)
			$days = 7;

		$where = [
			'tg_id' => $this->subscriberChannel['user_id'],
			'date >=' => date('Y-m-d H:i:s', strtotime('-' . $days . ' days')),
			'chat_id' => $data['chat_id'],
		];

		$messages = $oM->where($where)->select('COUNT(*) as cnt')->first();
		$messages = $messages['cnt'] ?? 0;


		if (!empty($data['stats_msgs_attr_id']))
		{
			$attr = $oSA->getById( $data['stats_msgs_attr_id'] );
			if ($attr)
			{
				$oSA->saveAttr( $this->subscriber['id'], $data['stats_msgs_attr_id'], $messages );
			}
		}


		return true;

	}



	function ban($data = [])
	{
		$params = [
			'channel' => $this->subscriberChannel,
			'subscriber' => $this->subscriber,
			'config' => $data,
		];


		$until = $data['ban_secs'] ?? 0;
		if ($until < 0 || $until > 31622400)
			$until = 0;


		$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/banChatMember?chat_id=' . $data['chat_id'] . '&user_id=' . $this->subscriberChannel['user_id'] . '&until_date=' . $until;
		$response = json_decode(curl_get($url), true);


		// l($response, 'adm');


		return true;

	}


	function unban($data = [])
	{
		$params = [
			'channel' => $this->subscriberChannel,
			'subscriber' => $this->subscriber,
			'config' => $data,
		];



		$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/unbanChatMember?chat_id=' . $data['chat_id'] . '&user_id=' . $this->subscriberChannel['user_id'];
		$response = json_decode(curl_get($url), true);


		// l($response, 'adm');


		return true;

	}


}