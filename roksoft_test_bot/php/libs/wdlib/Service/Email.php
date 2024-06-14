<?php

namespace WDLIB;

class Service_Email {

	static public function selectToSend(int $portion)
	{
		$queue = Model_Email_Queue::select(Model_Email_Queue::STATUS_WAIT, $portion);
		if(!empty($queue)) {
			foreach($queue as $item) {
				$item->status = Model_Email_Queue::STATUS_SENDING;
			}
			Model_Email_Queue::updateList($queue);
		}
		return $queue;
	}

	static public function send(Model_Email_Queue $item, array $config) : Model_Email_Queue
	{
		$mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

		try {
			$mailer->isSMTP();
			$mailer->Host = $config["smtp"];
			$mailer->SMTPAuth = true;
			$mailer->Username = $config["user"];
			$mailer->Password = $config["pass"];
			$mailer->SMTPSecure = "ssl";
			$mailer->Port = $config["port"];

			$mailer->setFrom($config["user"]);
			$mailer->addAddress($item->to);

			$mailer->Subject = $item->subject;
			$mailer->Body = $item->body;

			$mailer->CharSet = \PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8;

			$mailer->send();
			
			$item->status = Model_Email_Queue::STATUS_SENDED;
		}
		catch(\PHPMailer\PHPMailer\Exception $e) {
			Logger::error(__METHOD__." : PHPMailer error: {$mailer->ErrorInfo}");
			$item->status = Model_Email_Queue::STATUS_ERROR;
		}
		catch(Exception $e) {
			Logger::error(__METHOD__." : PHPMailer error: {$mailer->ErrorInfo}");
			$item->status = Model_Email_Queue::STATUS_ERROR;
		}

		$item->date = Core::curtime();
		Model_Email_Queue::update($item);

		return $item;
	}
}
