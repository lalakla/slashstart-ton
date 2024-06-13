<?php



require_once(dirname(__FILE__)) . '/../flow.php';



class TelegramFlow extends ServiceFlow
{

	var $allowedMethods = ['tgbetaf1'];




	function tgbetaf1($data)
	{

		$action = $data['p1'] ?? '';

		foreach ($data as $k=>$v)
		{
			$data[$k] = $this->parseVars($v);
		}

		$data['token'] = 'e37c148180ed623eAAH6kXxYQ907aad22255a1a4c';


		$data['action'] = $action;

		$url = 'https://ig2tgvk.egrnkvch.com/ig2tg.php';

		$result = curl_post($url, $data);

		// l($url, 'tga', 3);
		// l($result, 'tga', 3);

		if (!empty($data['p3']))
		{
			$oSA = new \Subscribers\Models\SubscriberAttribute();

			$attr = $oSA->select('id')->where('id', $data['p3'])->limit(1)->first();
			if ($attr)
				$oSA->saveAttr( $this->subscriber['id'], $attr['id'], $result);
		}



	}








}