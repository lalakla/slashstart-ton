<?php namespace Services\Controllers;


// $.get('https://rplto.net/flows/process');
// $.get('https://rplto.net/services/messages/worker/test');
// https://ruhighload.com/supervisor


use CodeIgniter\Controller;



require_once(dirname(__FILE__)) . '/../Libraries/service.php';

class MessagesQueue extends \App\Controllers\AppController
{

	var $workersCount = 5;

	var $workersPerProject = 5; // 1W = 4msg/sec. 5w = 20 msg/sec., for BC
	var $messagesPerWorkerBCSlots = 3; // Broadcasters = $workersPerProject * $messagesPerWorkerBCSlots;

	// var $messagesPerWorker = 100; // 100msg / 4msg/sec = 25 sec iteration + delay for 1 minute. 100msg+/min per worker.
	var $messagesPerWorker = 50; // test



	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		$this->oChatMessage = new \Subscribers\Models\ChatMessage();

		$this->oChat = new \Subscribers\Models\Chat();

		$this->oSubscriber = new \Subscribers\Models\Subscriber();

		$this->oChannel = new \App\Models\Channel();

		$this->oService = new \Services\Models\ServiceModel();

		$this->oTask = new \Flows\Models\Task();

		$this->oQueue = new \App\Models\Queue();

		$this->oGearman = new \Services\Models\Gearman();
	}



	public function geramanWorker($workerId = 0)
	{
		
		// while (true)
		// {
		// 	sleep(100);
		// 	continue;
		// }


		$worker = new \GearmanWorker();

		$worker->addServer(GEARMAN_SERVER, GEARMAN_PORT);

		$workerId = (int)$workerId + 1;

		// l($workerId, 'gws');

		while (true)
		{
			$ff = 0;
			$gearmanQueue = $this->oGearman->select('DISTINCT(function_name) as f')->findAll();


			// if (date('s') == 0)
			// {
			// 	// l('ok - ' . print_r($gearmanQueue, 1), 'gw');
			// }

			if (!$gearmanQueue)
			{
				sleep(1);
				continue;
			}

			// l('ok - ' . print_r($gearmanQueue, 1) . ' - ' . $workerId, 'gw');

			
			foreach ($gearmanQueue as $f)
			{
				if (preg_match('#run_msg_wrk_(\d+)_' . $workerId . '#si', $f['f']))
				// if (preg_match('#run_msg_wrk_(\d+)#si', $f['f']))
				{
					$ff = 1;
					$worker->addFunction($f['f'], [$this, 'sendMessageFromRedisQueue']);
				}

			}



			try
			{
				if ($gearmanQueue && $ff)
				{
					$worker->setTimeout(100); // 0.1 сек. если нет заданий, сразу выход.

					$worker->work();
				}

			}
			catch (\Throwable $e)
			{


			}


			// l($worker->returnCode(), 'gw');


		}


	}




	public function runMessagesWorkers4Projects()
	{
		$workersPerProject = $this->workersPerProject; // 1W = 4msg/sec. 5w = 20 msg/sec.
		$messagesPerWorker = $this->messagesPerWorker; // 100msg / 4msg/sec = 25 sec iteration + delay for 1 minute. 100msg+/min per worker.
		$messagesPerWorker2 = floor($messagesPerWorker / 2);

		$client = new \GearmanClient();
		$client->addServer(GEARMAN_SERVER, GEARMAN_PORT);

		// disabled for addTaskBackground
		// $client->setCompleteCallback(function(\GearmanTask $task) {
		// 	l(df(), 'cne-ct');
		// 	// l($task, 'cne-ct');
			
		// });



		while (true)
		// for ($__i = 0; $__i < 10; $__i++)
		{
			$countNotEmpty = rds()->zCount(QUEUES_PROJECTS_MESSAGES_COUNT, 1, '+inf');

			if (!$countNotEmpty)
			{
				usleep(100000);
				continue;
			}

			$listNotEmpty = rds()->zRangeByScore(QUEUES_PROJECTS_MESSAGES_COUNT, 1, '+inf', ['withscores' => true]);


			// Удаляем из очереди, чтобы не зависало, пока не отработает очередь (до этого момента $countNotEmpty > 0 все время).
			// А если вылетит? В catch сделан возврат назад в очередь. Но если сами сообщения не улетят? Подождать тест на практике, как будет.
			foreach ($listNotEmpty as $prId=>$count)
			{
				$deleted = rds()->zRem(QUEUES_PROJECTS_MESSAGES_COUNT, $prId);
			}
			// $countNotEmpty2 = rds()->zCount(QUEUES_PROJECTS_MESSAGES_COUNT, 1, '+inf');
			l($listNotEmpty, 'queues-projects-messages-count');
			// l('cn2 - ' . $deleted . ' - ' . $countNotEmpty2, 'cne');


			foreach ($listNotEmpty as $prId=>$count)
			{


				$wc = ceil($count / $messagesPerWorker);

				if ($wc > $workersPerProject)
					$wc = $workersPerProject;

				$qKey = 'run_msg_wrk_' . $prId;

				for ($i = 1; $i <= $wc; $i++)
				{
					$lockKey = 'rmw_' . $prId . '_' . $i;

					// Еще работает предыдущий воркер.
					// if (!rds()->set($lockKey, 1, ['nx', 'ex' => 60]))
						// continue;

					$payload = _json_encode(['p' => $prId, 'c' => $count, 'w' => $i, 't' => $wc]);

					try
					{
						// $result = $client->doBackground($qKey, $payload);
						if ($count > $messagesPerWorker2)
						{
							$result = $client->addTaskLowBackground($qKey . '_' . $i, $payload, null);
						}
						elseif ($count == 1)
						{
							$result = $client->addTaskHighBackground($qKey . '_' . $i, $payload, null);
						}
						else
						{
							$result = $client->addTaskBackground($qKey . '_' . $i, $payload, null);
						}


						// l($result, 'mq-gm');

						if ($result)
						{
							// Определяем, какие задачи для германа нужно запустить
							rds()->zAdd(QUEUES_PROJECTS_MESSAGES_RUN_WORKERS, $count, $prId);
						}

					}
					catch (\Throwable $e)
					{
						// Возвращаем назад
						rds()->zAdd(QUEUES_PROJECTS_MESSAGES_COUNT, $count, $prId);


						l('Gearman runMessagesWorkers4Projects: ' . print_r($e->getMessage(), 1), 'gearman');

					    // exit(255);
					}

				}



				// Удаляем очередь, чтобы не было повторов и вечных циклов. А если дальше не отработает? Воркер обновляет размер очереди. А если он собъется?
				// каждый обновляет число очереди, надо считать число не пустых и не заблокированных. но пока с обнулением - быстрое оптимизационное решение
				// rds()->zAdd(QUEUES_PROJECTS_MESSAGES_COUNT, 0, $prId);


			}


			$client->runTasks(); 
			


		}


		// echo 1;
	}



	public function processProjectMessages($workerId = 0)
	{
		// $workerId = 000, 001, 002, 003...

		$workerId = (int)$workerId + 1;

		// $isBroadcaster = $workerId % 2|3|4|5 == 0;
		$workersCount = $this->workersCount;
		$broadcastersCount = $this->messagesPerWorkerBCSlots * $this->workersPerProject;

		
		$isBroadcaster = $workerId > $workersCount ? 1 : 0;
		$broadcasterId = $workerId - $workersCount;

		// l($workerId . ' - ' . $isBroadcaster . ' - ' . $broadcasterId, 'w');
		

		$worker = new \GearmanWorker();

		try
		{

			$worker->addServer(GEARMAN_SERVER, GEARMAN_PORT);
		}
		catch (\Throwable $e)
		{
			
		}


		//rds()->zRemRangeByScore(QUEUES_PROJECTS_MESSAGES_RUN_WORKERS, 0, 0);


		while (true)
		{

			$countNotEmpty = rds()->zCount(QUEUES_PROJECTS_MESSAGES_RUN_WORKERS, 1, '+inf');

			// l($countNotEmpty, 'gm-p');

			if (!$countNotEmpty)
			{
				usleep(100000);
				continue;
			}


			$requiredWorkers = rds()->zRangeByScore(QUEUES_PROJECTS_MESSAGES_RUN_WORKERS, 1, '+inf', ['withscores' => true]);

			// l($requiredWorkers, 'mq-gm');


			try
			{
				$ff = false;
				$ww = 0;

				foreach ($requiredWorkers as $prId => $c)
				{
					// Любое число, когда считать рассылкой. Если больше, чем количество воркеров стандартных
					if ($c > $this->workersCount)
					{
						if (!$isBroadcaster)
							continue;


						$wlk = 'bc_wlk_' . $prId;
						// rds()->del( $wlk );

						$activeWorkersCount = rds()->lLen($wlk);
						if ($activeWorkersCount >= $this->workersPerProject)
						{
							// Удаляем от вечного цикла.
							// Новое число появится после отработки воркера, когда он освободится. 

							$deleted = rds()->zRem(QUEUES_PROJECTS_MESSAGES_RUN_WORKERS, $prId);

							continue;
						}


						// 1 worker per project
						// if (($prId + 1) % $broadcastersCount != $broadcasterId - 1)
						// 	continue;

						// x random workers from y available per project

						$slot = mt_rand(1, $this->messagesPerWorkerBCSlots);
						$slotFirstBCId = $slot * $this->workersPerProject + 1;
						// [6, 7, 8, 9, 10], [11, 12, 13, 14, 15], [16, 17, 18, 19, 20]

						
						
						$currencBC = $activeWorkersCount + 1;

						if (($slotFirstBCId + $currencBC) != $workerId)
							continue;


						rds()->lPush($wlk, $workerId);

						$ww = $currencBC;

						// rds()->zIncrBy(QUEUES_PROJECTS_MESSAGES_RUN_WORKERS, -$this->messagesPerWorker, $prId);
						
					}
					else
					{
						if ($isBroadcaster)
							continue;

						if (($prId + 1) % $workersCount != $workerId - 1)
						{
							// 1 / 7 = 1
							// 2 / 7 = 2
							// 3 / 7 = 3
							// 4 / 7 = 4
							// 5 / 7 = 5
							// 6 / 7 = 6
							// 7 / 7 = 0


							continue;
						}

						$ww = 1;

						rds()->zIncrBy(QUEUES_PROJECTS_MESSAGES_RUN_WORKERS, -$c, $prId);

					}

					

					$qKey = 'run_msg_wrk_' . $prId . '_' . $ww;

					$ff = true;

					$worker->addFunction($qKey, [$this, 'sendMessageFromRedisQueue']);

					// Не удаляем, чтобы другой воркер мог взять
					// $deleted = rds()->zRem(QUEUES_PROJECTS_MESSAGES_RUN_WORKERS, $prId);


					// l('pr: ' . $prId . ', count: ' . $c . ', worker: ' . $workerId . ', ww: ' . $ww, 'wq', true);
				}


				
				if ($ff)
				{
					$worker->setTimeout(100); // 0.1 сек. если нет заданий, сразу выход.

					$worker->work();


					if ($worker->returnCode() != GEARMAN_SUCCESS)
					{
						// log_message('critical', 'Gearman 0: ' . $worker->returnCode());
					}

				}


				




			}
			catch (\Throwable $e)
			{
				l('gearman fail: ' . df() . ' ' . print_r($e->getMessage(), 1), 'gearman');
				exec('systemctl restart gearmand &' . PHP_EOL);
				exec('systemctl restart supervisord &' . PHP_EOL);
			}


		}


	}





	



	function sendMessageFromRedisQueue($job = null, $workload = null)
	{
		if (!$workload)
		{
			$workload = $job->workload();

			$workload = json_decode($workload, true);
		}


		$prqId = $workload['p'];

		$queueName = sprintf(QUEUES_PROJECT_MESSAGES, $prqId);

		$n = 0;

		// l('before - ' . rds()->lLen($queueName) . ' - ' . $prqId . ':' . $workload['p'], 'mq-gwl', true);

		// $range = rds()->lRange($queueName, 0, -1);
		// l('pr- ' . $prqId, 'gw', true);
		// l($range, 'gw', true);

		$response = [];

		while (($n < $this->messagesPerWorker) && ($userQueueName = rds()->lPop($queueName)))
		{

			// usleep(250000); // 1/4 sec
			// usleep(100000); // 1/10 sec

			$n++;

			// l($userQueueName, 'gw', true);

			// l('userQueueName - ' . $userQueueName . ':' . $workload['p'], 'mq-gwl', true);

			try
			{
				while ($message = rds()->lPop($userQueueName))
				{
					
					$message = json_decode(decrypt($message), true);

					// $userMessagesLeft = rds()->lLen( $userQueueName );
					// l('before w-'.($workload['w']??0).'. message - ' . $message['id'] . ' / ' . $userMessagesLeft . ' - ' . $workload['p'], 'mq-gwl', 3165);


					$response = $this->sendMessage( $message, $workload );


					$userMessagesLeft = rds()->lLen( $userQueueName );
					
					if (!$userMessagesLeft)
						rds()->del( $userQueueName );

					

				}

			}
			catch (\Throwable $e)
			{
				l($response, 'sendMessageFromRedisQueue', true);
				l($workload, 'sendMessageFromRedisQueue', true);
				l($message, 'sendMessageFromRedisQueue', true);
			}
		}


		$messagesLeft = rds()->lLen( $queueName );

		
		// l('after - ' . $prqId . ':' . $workload['p'], 'mq-gwl', true);


		rds()->zAdd(QUEUES_PROJECTS_MESSAGES_COUNT, $messagesLeft, $prqId);

		rds()->zRemRangeByScore(QUEUES_PROJECTS_MESSAGES_COUNT, 0, 0);


		$lockKey = 'rmw_' . $prqId . '_' . $workload['w'];
		rds()->del($lockKey);

		$wlk = 'bc_wlk_' . $prqId;
		rds()->lPop($wlk);

		if (!rds()->lLen($wlk))
			rds()->del($wlk);

		// l(rds()->lLen($wlk), 'mq-gwl', true);


		return true;

	}



	function sendMessage($message, $workload = [])
	{

		// l($message, 'msg', 3165);

		try
		{
			if (!$this->oChatMessage->setDatabase(MYSQL_BASE_NAME_TPL . $message['project_id'] ?? 0))
				return false;

			$pr = \Platform::setProject( $message['project_id'] );

		}
		catch (\Throwable $e)
		{
			l($e->getMessage(), 'gearman-server');

		    return;
		}


		$message['message_queue'] = json_decode($message['message_queue'], true);


		// Шлем по 1, но не по ID выбираем, а по дате поступления в очередь сортируем.
		$limit = 1; // Для ВК можно будет в 1 execute собрать

		$mselector = [
			'is_queued' => 1,
			'is_sent' => 0,
			'subscriber_id' => $message['message_queue']['subscriber']['id']
		];

		// ? по порядку ли они выбираются
		$mselector = [
			'id' => $message['id']
		];


		// l($mselector, 'msg', 3165);

		$messages = $this->oChatMessage->where($mselector)->orderBy('id', 'asc')->limit($limit)->find();

		
		// l($messages, 'msg', 3165);


		if ($messages)
		{
			return $this->oChatMessage->process($messages);
		}

	}




	function sendMessageFromQueue($job)
	{
		try
		{

			$_x = microtime(true);


			// Из Gearman
			$workload = $job->workload();



			// Из Redis
			// $workload = rds()->lPop('queue_item_1');



			$workload = json_decode($workload, true);

			if (empty($workload['data']))
				return;

			$message = json_decode(decrypt($workload['data']), true);




			$lockKey = 'mq_' . $workload['project_id'] . '_' . $message['subscriber_id'];
			$n = 0;
			while (rds()->lindex($lockKey, 0) != $message['id'] && $n < 50)
			{
				usleep(200000); // 1000000 / 5 = 1/5 секунды. n = 5 = 1 сек, 25 = 5 сек, 50 = 10 сообщений по 1 сек

				$n++;
			}



			$this->sendMessage( $message );

			rds()->lPop($lockKey);


			$_y = microtime(true);



			$log = [
				't' => df(),
				'w' => $worker ?? 0,
				's' => $serverId ?? 0,
				'd' => number_format($_y - $_x, 5),
			];

			if ($message)
			{
				$log['m'] = $message['id'] ?? '';
				// $log['c'] = $message;
				$fp = fopen(CRON_DIR . 'messages-supervisor.log', 'a+');
				fwrite($fp, _json_encode($log) . "\r\n");
				fclose($fp);
			}

		}
		catch (\Throwable $e)
		{
			l('Gearman Messages: ' . print_r($e->getMessage(), 1), 'gearman');

		    exit(255);
		}


	}



	function runTaskFromQueue($job)
	{
		try
		{

			$_x = microtime(true);

			$_ts = df();

			$workload = json_decode($job->workload(), true);

			$task = json_decode(decrypt($workload['data']), true);


			try
			{
				if (!$this->oChatMessage->setDatabase(MYSQL_BASE_NAME_TPL . $task['project_id'] ?? 0))
					return false;

				$pr = \Platform::setProject( $task['project_id'] );

			}
			catch (\Throwable $e)
			{
				l($e->getMessage(), 'gearman-server');

			    return;
			}



			list($ts, $tt, $tm) = explode('/', $task['name']);

			$service = loadLibrary($ts, $tt);

			if (!$service->isAllowedMethod($tm))
				return false;


			$service->setApp($this->oService, json_decode($task['service'], true));

			$_tx = microtime(true);

			$result = $service->{$tm}( json_decode($task['params'], true) );

			$_ty = microtime(true);


			$tdata = [
				'id' => $task['id'],
				'is_completed' => 1,
				'completed_at' => df(),
				'started_at' => $_ts,
				'duration' => ($_ty - $_tx) * 1000,
				'service' => '',
				'result' => json_encode($result),
			];

			$this->oTask->save($tdata);


			$_y = microtime(true);



			$log = [
				't' => df(),
				'w' => $worker ?? 0,
				's' => $serverId ?? 0,
				'd' => number_format($_y - $_x, 5),
			];

			if ($task)
			{
				$fp = fopen(CRON_DIR . 'tasks-supervisor.log', 'a+');
				fwrite($fp, json_encode($log) . "\r\n");
				fclose($fp);
			}

		}
		catch (\Throwable $e)
		{
			l('Gearman Task: ' . print_r($e, 1), 'gearman');

		    exit(255);
		}


	}



	public function tasksWorker()
	{

		$worker = new \GearmanWorker();

		try {

			$worker->addServer(GEARMAN_SERVER, GEARMAN_PORT);

			$worker->addFunction('queue_item_2', [$this, 'runTaskFromQueue']);


			while(true)
			{
				$worker->work();

				if ($worker->returnCode() != GEARMAN_SUCCESS)
				{
					log_message('critical', 'Gearman task 0: ' . $worker->returnCode());
				}
			}

		}
		catch (\Throwable $e)
		{
			l('gearman task fail: ' . df() . ' ' . print_r($e->getMessage(), 1), 'gearman');
			exec('systemctl restart gearmand &' . PHP_EOL);
			exec('systemctl restart supervisord &' . PHP_EOL);
		}

	}





	public function worker($query)
	{

		if (!$query) return;

		$_x = microtime(true);

		$query = _explode(',', $query);

		$params = [];
		foreach ($query as $k=>$v)
		{
			list($kk, $vv) = _explode('=', $v);
			$params[$kk] = $vv;
		}


		$stop = false;

		$worker = $params['worker'] ?? 1;
		$serverId = $params['server'] ?? 0;



		$worker = new \GearmanWorker();

		try {

			$worker->addServer(GEARMAN_SERVER, GEARMAN_PORT);

			$worker->addFunction('queue_item_1', [$this, 'sendMessageFromQueue']);

			$worker->addFunction('queue_item_2', [$this, 'runTaskFromQueue']);


			while(true)
			{
				$worker->work();

				if ($worker->returnCode() != GEARMAN_SUCCESS)
				{
					log_message('critical', 'Gearman 0: ' . $worker->returnCode());
				}
			}

		}
		catch (\Throwable $e)
		{
			l('gearman fail: ' . df() . ' ' . print_r($e->getMessage(), 1), 'gearman');
			exec('systemctl restart gearmand &' . PHP_EOL);
			exec('systemctl restart supervisord &' . PHP_EOL);
		}





	}






	// VK: https://demo.rplto.net/messages/worker/testchannels/25/32

	public function testchannels($channelId = 0, $subscriberId = 0)
	{
		$channel = $this->oChannel->getMessengers( $channelId );

		$channel = $channel[0];

		$msn = loadLibrary($channel['channel_name'], 'messanger');


		$msnService = $this->oService->limit(1)->find( $channel['service_id'] );


		$msn->setApp($this->oService, $msnService);

		$msn->setSubscriberAndChannel( $subscriberId, $channel );






		$message = [
			'id' => 0,
			'message' => 'Test message ' . date('Y-m-d H:i:s')
		];

		// $result = $msn->sendMessage( $message ); d($result);





		$message = [
			'id' => 0,
			'message' => 'Click me ' . date('Y-m-d H:i:s'),
			'buttons' => [
				[
					['title' => 'Btn #1', 'link' => 'https://egrnkvch.com/#btn1', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 1]],
					['title' => 'Btn #2', 'link' => 'https://egrnkvch.com/#btn2', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 2]]
				],
				[
					['title' => 'Btn #3', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 3]]
				]
			]
		];

		// $result = $msn->sendMessage( $message ); d($result);


		$message = [
			'id' => 0,
			'file' => ROOTPATH . 'uploads/photo.jpg',
			'title' => 'Петронасы',
			'buttons' => [
				[
					['title' => 'Btn #1', 'link' => 'https://egrnkvch.com/#btn1', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 1]],
					['title' => 'Btn #2', 'link' => 'https://egrnkvch.com/#btn2', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 2]]
				],
				[
					['title' => 'Btn #3', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 3]]
				]
			],
			'shortcuts' => [
				'resize' => 1,
				'onetime' => 1,
				'buttons' => [
					[
						['title' => 'SC #1', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 1]],
						['title' => 'SC #2', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 2]]
					],
					[
						['title' => 'SC #3', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 3]]
					]
				]
			]
		];
		// $result = $msn->sendPhoto( $message ); d($result);







		$message = [
			'id' => 0,
			'file' => ROOTPATH . 'uploads/photo.jpg',
			// 'title' => 'CC'
		];

		// $result = $msn->sendDocument( $message ); d($result);




		$message = [
			'id' => 0,
			'file' => ROOTPATH . 'uploads/video.mp4',
			'title' => 'Video'
		];

		// $result = $msn->sendVideo( $message ); d($result);




		$message = [
			'id' => 0,
			'file' => ROOTPATH . 'uploads/audio.ogg',
			'title' => 'Audio'
		];

		// $result = $msn->sendAudio( $message ); d($result);






		$message = [
			'id' => 0,
			'message' => 'Click me ' . date('Y-m-d H:i:s'),
			// 'buttons' => [
			// 	[
			// 		['title' => 'Btn #1', 'link' => 'https://egrnkvch.com/#btn1', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 1]],
			// 		['title' => 'Btn #2', 'link' => 'https://egrnkvch.com/#btn2', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 2]]
			// 	],
			// 	[
			// 		['title' => 'Btn #3', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 3]]
			// 	]
			// ],
			'shortcuts' => [
				'resize' => 1,
				'onetime' => 1,
				'buttons' => [
					[
						['title' => 'SC #1', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 1]],
						['title' => 'SC #2', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 2]]
					],
					[
						['title' => 'SC #3', 'payload' => ['u' => 1000000000, 's' => 1000000000000, 'i' => 100000000, 'b' => 3]]
					]
				]
			]
		];
		// $result = $msn->sendMessage( $message ); d($result);









		// shortcuts - ответы, buttons - действия




	}













}
