<?php





class Service
{
	var $oSubscriber = null;


	var $app = null;

	var $config = array();

	var $params = array();

	var $subscriber = [];

	var $request = null;

	var $flowState = null;

	var $allowedMethods = array();

    var $vars = [];




	function __construct($settings = array())
	{
		$this->params = array_merge($this->params, $settings);


		$this->oSubscriber = new \Subscribers\Models\Subscriber();
	}



	function isAllowedMethod($method)
	{
		return in_array($method, $this->allowedMethods);
	}




	function setApp($app, $config)
	{

		if (!empty($config['token_params']))
			$config['token_params'] = is_array($config['token_params']) ? $config['token_params'] : json_decode($config['token_params'], true);

		if (!empty($config['client_params']))
			$config['client_params'] = is_array($config['client_params']) ? $config['client_params'] : json_decode($config['client_params'], true);

		$this->app = $app;

		$this->config = $config;
	}


	function setFlowState($state = [])
	{
		$this->flowState = $state;

	}


	function setRequest($request)
	{
		$this->request = $request;
	}



	function getServiceUrl($serviceId, $method = 'process')
	{
		$serviceIdKey = md5($serviceId . ':' . SERVICES_QUERY_KEY . ':' . $method);

		$url = 'https://' . getEnv('HTTP_HOST') . '/services/'.$serviceId.'-'.$serviceIdKey.'/process' . ($method != 'process' ? '/' . $method : '');

		return $url;
	}



	function setSubscriber($subscriber)
	{
		$this->subscriber = is_numeric($subscriber) ? $this->oSubscriber->getById( $subscriber ) : $subscriber;


		if (!$this->subscriber)
			return false;

		return true;

	}



	function uploadFile($url, $name)
	{
		$ext = validateExt($name);

		$fname = slugify( preg_replace('#\.'.$ext.'$#si', '', $name . '-' . substr(md5($name), 0, 14) ) ) . '.' . $ext;

		$dir = md5( alphaID(PROJECT_ID) );

		$dir = $dir[0] . $dir[1] . '/' . $dir[2] . $dir[3] . '/' . $dir[4] . $dir[5] . '/' . alphaID(PROJECT_ID) . '/';

		$dest = 'uploads/' . $dir;

		$result = null;

		if (file_exists(PUBLIC_HTML_PATH . $dest . $fname))
		{
			$result = PLATFORM_BASE_URL . $dest . $fname;
		}
		else
		{
			$contents = curl_get($url);



			if ($contents && (is_dir(PUBLIC_HTML_PATH . $dest) || mkdir(PUBLIC_HTML_PATH . $dest, 0755, true)))
			{
				if (file_put_contents(PUBLIC_HTML_PATH . $dest . $fname, $contents))
				{
					$result = PLATFORM_BASE_URL . $dest . $fname;
				}
			}
		}

		return $result;
	}




	function mkExternalId($subscriber = [])
	{
		$subscriber = $subscriber ? $subscriber : $this->subscriber;


		// alphaID($in, $to_num = false, $pad_up = false, $passKey = null)

		$exid = alphaID($subscriber['id'], false, 7, SERVICE_EXTERNAL_ID_KEY) . '-' . alphaID($subscriber['user_id'], false, 7);

		$exid .= '-' . substr(md5( $exid . ':' . SERVICE_EXTERNAL_ID_KEY . ':' . $exid ), 14, 7);

		return $exid;
	}


	function getExternalId($exid)
	{

		if (!preg_match('#^[0-9a-z]+-[0-9a-z]+-[0-9a-f]{7,7}+$#si', $exid))
			return false;


		list($sid, $uid, $hash) = explode('-', $exid);

		$check = substr(md5( $sid . '-' . $uid . ':' . SERVICE_EXTERNAL_ID_KEY . ':' . $sid . '-' . $uid ), 14, 7);

		if ($hash !== $check)
			return false;

		$exid = [
			'subscriber_id' => alphaID($sid, true, 7, SERVICE_EXTERNAL_ID_KEY),
			'user_id' => alphaID($uid, true, 7),
		];

		return $exid;
	}


	function replaceLinks($data)
	{



		if (empty($data['message']['m']))
			return $data;


		// log_message('critical', print_r($data, 1));

		// Удалено {} в конце, где кавычки // https://mathiasbynens.be/demo/url-regex
		$pattern = '#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\];:\'".,<>?«»“”‘’]))#iS';



		$links = [];

		foreach ($data['message']['m'] as $mn=>$m)
		{
			if (!empty($m['c']))
			{
				$data['message']['m'][$mn]['c'] = $m['c'] = $this->parseVars( $m['c'] );

				preg_match_all($pattern, $m['c'], $flinks);

				if (!empty($flinks[0]))
				{
					foreach ($flinks[0] as $link)
					{
						$links[] = [$link];
					}
				}
			}

			if (!empty($m['b']))
			{
				foreach ($m['b'] as $bk=>$btn)
				{
					if ($btn['l'])
					{
						$data['message']['m'][$mn]['b'][$bk]['s'] = $btn['l'];
						$data['message']['m'][$mn]['b'][$bk]['l'] = $btn['l'] = $this->parseVars( $btn['l'] );


						$links[] = [$btn['l'], $btn['i'], $btn['l']];
					}
				}
			}
		}


		if (!$links)
			return $data;

		// $links = array_unique($links);
		usort($links, function($a, $b) {
			if ($a[0] == $b[0])
				return 0;

			return $a[0] > $b[0] ? -1 : 1;
		});


		$result = [];


		$project = \Platform::getProject();

		$linksKey = md5($project['id']);
		$linksUrl = 'https://' . $project['project_host'] . '/c/';


		$msgId = alphaID($data['id'], false, 4, $linksKey);


		foreach ($links as $id=>$link)
		{
			$linkId = alphaID($id + 1, false, 2, $linksKey);

			$btnId = !empty($link[1]) ? alphaID($link[1], false, false, $linksKey) : '';
			// $btnId = $link[1];

			if ($btnId)
				$linkId .= '/' . $btnId;


			$redirect = $linksUrl . $msgId . '/' . $linkId;


			$result[$linkId] = [
				'i' => $linkId,
				'l' => $link[0],
				'b' => $link[1] ?? '',
				'r' => $redirect,
				's' => $link[2] ?? '',
			];

			foreach ($data['message']['m'] as $k=>$m)
			{
				if (!empty($m['c']))
				{
					$data['message']['m'][$k]['c'] = str_replace($link[0], $redirect, $m['c']);
				}

				if (!empty($m['b']))
				{
					foreach ($m['b'] as $k2=>$btn)
					{
						if ($btn['l'] && !empty($link[1]))
						{
							$data['message']['m'][$k]['b'][$k2]['l'] = str_replace($link[0], $redirect, $btn['l']);
						}
					}
				}
			}

		}


		$data['message_links'] = $result;


		// log_message('critical', print_r($result, 1));
		// log_message('critical', print_r($data['message'], 1));


		return $data;
	}


	function parseVars($content, $fullData = '')
	{
		$project = Platform::getProject( );


		if (empty($this->vars))
			$this->setVars();




		// Сначала меняем ссылки, они без замен


		if ($fullData)
		{
			$nv = $this->vars;
			$this->vars['subscriber_json'] = json_encode($nv);
			$this->vars['subscriber_query'] = http_build_query($nv);
		}



		// Простая замена
		foreach ($this->vars as $k=>$v)
			$content = preg_replace('#{{\s*' . $k .'\s*}}#si', $v, $content);





		// Замена основных шорткодов, где не нужны данные подписчика
		requireLibrary('shortcodes');
		$content = processShortcodes($content);


		// Функции []
		if ($shortcodes = getShortcodes($content))
		{
			foreach ($shortcodes as $code)
			{
				$value = '';

				switch ($code['f'])
				{
					case 'app':
						$appUrl = 'https://' . $project['project_host'] . '/apps/' . $code['p']['name'];

						$sid = $this->vars['subscriber'];
						// $mid = alphaID($b['payload']['m'], false, 5, $sid);
						$mid = 'e';
						$aid = $code['p']['id'] ?? '';
						$hid = substr(md5($sid . ':' . $mid . ':' . $aid . ':' . SERVICE_EXTERNAL_ID_KEY), 5, 14);


						$aqp = [
							API_PREFIX_SHORT . 'id' => $sid,
							API_PREFIX_SHORT . 'app' => $aid,
							API_PREFIX_SHORT . 'm' => $mid,
							API_PREFIX_SHORT . 'h' => $hid,
                            'ref' => $this->vars['ref']
						];

						foreach ($code['p'] as $ck=>$cv)
						{
							$aqp[$ck] = $cv;
						}

						$appUrl .= '?' . http_build_query($aqp);

						$value = $appUrl;

						if ($code['p']['name'] == 'telegram_appredirect' && !empty($code['p']['url']))
							$value = $code['p']['url'];


					    break;
					case 'invoice':

						$params = $code['p'];

						$params['subscriber'] = $this->vars['subscriber'];

						$invoice = invoiceParams($params);

						// log_message('critical', print_r($invoice, 1));

						if ($invoice)
						{
							$value = 'https://' . $project['project_host'] . '/invoice?' . http_build_query($invoice);
						}
					    break;
				}


				$content = str_replace($code['m'], $value, $content);


				// d($params);
			}

			// d($m);

		}


		// log_message('critical', print_r($content, 1));

		// Очистка пустых атрибутов
		$content = preg_replace('#{{\s*[0-9a-z_-]+\s*}}#si', '', $content);

		// log_message('critical', print_r($content, 1));



		// Замена с модификаторами {{$k|lower}}

		return $content;

	}


	function resetVars()
	{
		$this->vars = [];

		if (!empty($this->subscriber['id']))
		{
			$this->subscriber = $this->oSubscriber->getAllData($this->subscriber['id']);
		}
	}


	function setVars($subscriber = [], $queue = [])
	{
		$subscriber = $subscriber ? $subscriber : $this->subscriber;

		$vars = [
			'id' => !empty($subscriber['id']) ? extIdEncode($subscriber['id']) : '',
			'subscriber' => !empty($subscriber['id']) ? extIdEncode($subscriber['id']) : '',
            'ref' => $subscriber['ref_link'] ?? '',
			'email' => $subscriber['email'] ?? '',
			'phone' => $subscriber['phone'] ?? '',
			'name' => $subscriber['name'] ?? '',
			'fullname' => $subscriber['name'] ?? '',
			'firstname' => $subscriber['firstname'] ?? '',
			'lastname' => $subscriber['lastname'] ?? '',
			'username' => $subscriber['username'] ?? '',
			'telegram_id' => !empty($subscriber['telegram_id']) ? $subscriber['telegram_id'] : '',
            'vk_id' => !empty($subscriber['vk_id']) ? $subscriber['vk_id'] : '',
            'viber_id' => !empty($subscriber['viber_id']) ? $subscriber['viber_id'] : '',
            'whatsapp_id' => !empty($subscriber['whatsapp_id']) ? $subscriber['whatsapp_id'] : '',
            'facebook_id' => !empty($subscriber['facebook_id']) ? $subscriber['facebook_id'] : '',
            'instagram_id' => !empty($subscriber['instagram_id']) ? $subscriber['instagram_id'] : '',
            'instagram_handle' => $subscriber['instagram_handle'] ?? '',
            'instagram_login' => $subscriber['instagram_handle'] ?? '',
            'timeoffset' => $subscriber['timeoffset'] ?? '',
            'timezone' => $subscriber['timezone'] ?? '',
            'photo' => $subscriber['photo'] ?? '',
            'gender' => $subscriber['gender'] ?? '',
            'created_at' => $subscriber['created_at'] ?? '',
		];

		if ($vars['fullname'])
		{
			if (strpos($vars['fullname'], ' ') !== false)
			{
				list($fn, $ln) = _explode(' ', $vars['fullname']);
			}
			else
			{
				$fn = $vars['fullname'];
				$ln = '';
			}

			if (!$vars['firstname']) $vars['firstname'] = $fn;
			if (!$vars['lastname']) $vars['lastname'] = $ln;
		}
		$vars['first_name'] = $vars['firstname'];
		$vars['last_name'] = $vars['lastname'];

		// log_message('critical', print_r($queue, 1));

		if (!empty($queue['subscriber_channel']['window24_start']))
		{
			$_ds = sToDHMS(time() - strtotime($queue['subscriber_channel']['window24_start']));

			$_dc = zerofill($_ds['d'], 2) . ':' . zerofill($_ds['h'], 2) . ':' . zerofill($_ds['m'], 2);

			$vars['w24_start_h'] = $_ds['d'] * 24 + $_ds['h'];
			$vars['w24_start_m'] = $_ds['d'] * 1440  + $_ds['h'] * 60 + $_ds['m'];
			$vars['w24_start_hm'] = zerofill($vars['w24_start_h'], 2) . ':' . zerofill($_ds['m'], 2);
			$vars['w24_start'] = $queue['subscriber_channel']['window24_start'];

		}

		if (!empty($this->subscriberChannel['channel_id']))
		{
			$vars['channel_id'] = $this->subscriberChannel['channel_id'];
			$vars['channel_handle'] = $this->subscriberChannel['channel_handle'] ?? '';
		}
		elseif (!empty($queue['subscriber_channel']['channel_id']))
		{
			$vars['channel_id'] = $queue['subscriber_channel']['channel_id'];
			$vars['channel_handle'] = $queue['subscriber_channel']['channel_handle'] ?? '';
		}
		// l($vars, 'channel');





		// $oSC = new \Subscribers\Models\SubscriberConstant();
		// $consts = $oSC->getGlobals('name');
		// if ($consts)
		// {
		// 	foreach ($consts as $ck=>$cv)
		// 	{
		// 		$vars[$ck] = $cv;
		// 	}
		// }


		if (!empty($subscriber['globals']))
		{
			$vars = array_merge($vars, $subscriber['globals']);
		}

		if (!empty($subscriber['attrs']))
		{
			$vars = array_merge($vars, $subscriber['attrs']);
		}

		if (!empty($subscriber['utms']['last']))
		{
			$vars = array_merge($vars, $subscriber['utms']['last']);
		}

		if (!empty($this->appendVarsQueue))
		{
			$vars = array_merge($vars, $this->appendVarsQueue);
		}



		if (!empty($this->subscriberChannel['config']))
		{
			$config = json_decode($this->subscriberChannel['config'], true);
			if (!empty($config['vars']))
			{
				foreach ($config['vars'] as $k=>$v)
				{
					$vars['ext_vars_' . $k] = $v;
				}
			}
		}
		elseif (!empty($queue['subscriber_channel']['config']))
		{
			$config = json_decode($queue['subscriber_channel']['config'], true);
			if (!empty($config['vars']))
			{
				foreach ($config['vars'] as $k=>$v)
				{
					$vars['ext_vars_' . $k] = $v;
				}
			}
		}


		// l($vars, 'vars', 1186);
		// l($config ?? [], 'vars-c', 1186);


		$this->vars = $vars;




	}


	function appendVars($vars = [])
	{
		// if (empty($this->vars))
			// $this->vars = [];

		if (empty($vars))
			$vars = [];

		if (empty($this->appendVarsQueue))
			$this->appendVarsQueue = [];

		$this->appendVarsQueue = array_merge($this->appendVarsQueue, $vars);

		if (!empty($this->vars))
			$this->vars = array_merge($this->vars, $vars);


	}




	function getExtServiceId($subscriber_id, $account = '')
	{
		$oSS = new \Subscribers\Models\SubscriberService();

		$query = [
			'subscriber_id' => $subscriber_id,
			'service_name' => $this->config['name'],
			'service_account' => $account ? $account : $this->config['client_params']['account'] ?? '',
		];

		$external = $oSS->select('id, external_id')->where($query)->limit(1)->first();

		return $external;

	}


	function saveExtServiceId($subscriber_id, $external_id, $account = '')
	{
		$oSS = new \Subscribers\Models\SubscriberService();

		$current = $this->getExtServiceId($subscriber_id, $account);

		$query = [
			'service_name' => $this->config['name'],
			'service_account' => $account ? $account : $this->config['client_params']['account'] ?? '',
			'subscriber_id' => $subscriber_id,
			// 'external_id' => $external_id,
		];

		if (is_numeric($external_id))
		{
			$query['external_id'] = $external_id;
		}
		elseif (is_array($external_id))
		{
			$query['external_id'] = $external_id['external_id'] ?? 0;
			$query['external_login'] = $external_id['external_login'] ?? '';
		}
		else
		{
			$query['external_login'] = $external_id;
		}

		if ($current)
			$query['id'] = $current['id'];
		else
			$query['created_at'] = df();

		$oSS->save($query);

		return $current ? $current['id'] : $oSS->getInsertID();

	}



	function addFlowTask($name, $params = [])
	{

		$oT = new \Flows\Models\Task();

		$task = [
			'user_id' => $this->subscriber['user_id'] ?? 0,
			'name' => $name, //'getcourse/flow/_add',
			'params' => $params,
			'service' => $this->config,
		];

		$oT->create( $task );

		return $oT->getInsertID();

	}


}



