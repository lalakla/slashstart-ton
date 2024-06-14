<?php

class Controller_Api_Telegram extends Controller_Api_Base {
	
	public function __construct()
	{
		parent::__construct();

		$this->addMethod("request", array($this, "request"));
	}

	public function request()
	{
		$this->output = \WDLIB\OUTPUT_RAW;

		$this->api = \WDLIB\Api_Storage::getInstance()->getApi(\WDLIB\API_TELEGRAM);
		if(!$this->api) {
			throw new Exception("TELEGRAM API NOT FOUND", \WDLIB\ERROR);
		}

		$this->api->client->command("start", \Closure::fromCallable(array($this, "commandStart")));
		$this->api->client->command("help", \Closure::fromCallable(array($this, "commandHelp")));

		$this->api->client->run();
	}

	public function commandStart($message)
	{
		\WDLIB\Logger::log("start request : chat-id=".$message->getChat()->getId());

		$app = $this->api->app_id;
		$game = $this->api->data["web_app_id"];

		$mess =  <<<EOD
Привет, это Телеграм-Бот!
Тестовый, для всяких экспериментов...
Так что, прошу это иметь ввиду ;-)

А тут у нас web-app'а, переходи!
https://t.me/$app/$game

А ещё, можно написать /help чтобы вывести помощь :-)
EOD;
		$this->api->client->sendMessage($message->getChat()->getId(), $mess);
	}
	public function commandHelp($message)
	{
		$mess =  <<<EOD
Тут вообще-то помощь...
В любой момент можно написать команду /start чтобы вернуться к началу диалога.
EOD;
		$this->api->client->sendMessage($message->getChat()->getId(), $mess);
	}

}
