<?php



require_once(__DIR__ . '/tgadmin_flow.php');


class TgadminWebhook extends TgadminFlow
{

	var $allowedMethods = array('process');



	public function exit($state = '')
	{
		
		if (!empty($_GET['f']))
			echo 'ok';
		else
			die('ok');
	}



	public function process()
	{

		$updates = file_get_contents('php://input');
		$updates = json_decode($updates, true);


		// l($updates, 'admin');

		// -1002009494101
		// -4071856974



		$chatId = 0;

		$chatId = $updates['channel_post']['chat']['id'] ?? 0;

		$chatType = $updates['message']['chat']['type'] ?? '';

		if (!$chatId && $chatType && ($chatType == 'group' || $chatType == 'supergroup'))
			$chatId = $updates['message']['chat']['id'] ?? 0;



		$fromId = $updates['message']['from']['id'] ?? 0;
		



		if (strpos(json_encode($updates), 'sstgadminid') !== false)
		{
				
			$message = sprintf(__('Chat ID: %s'), $chatId);

			$url = 'https://api.telegram.org/bot' . $this->config['token'] . '/sendMessage?chat_id=' . $chatId . '&text=' . urlencode($message);

			$response = json_decode(curl_get($url), true);


			// l($response, 'admin');


			return true;
		}


		$message = $updates['message']['text'] ?? '';
		if ($fromId && $message && $chatId)
		{

			$oM = new \Services\Models\TgadminMessage();

			$oM->save([
				'tg_id' => $fromId,
				'text' => $message,
				'chat_id' => $chatId,
				'date' => df(),
			]);

		}



		return $this->exit();

    }














}