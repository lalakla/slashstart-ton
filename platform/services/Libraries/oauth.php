<?php


require_once(dirname(__FILE__)) . '/service.php';

define('SERVICE_OAUTH_STATE_SALT', 'EG5IhPvtmOhCYw1vx0Y4YSb83rqdHohD');




class ServiceOauth extends Service
{
	


	var $params = array(
		'auth_scope' => ''
	);


	function __construct($settings = array())
	{
		
		parent::__construct($settings);
		
	}


	

	function getAuthUrl()
	{
		// $url = 'https://' . $this->params['subdomain'] . '.amocrm.ru/oauth2/access_token';

		return $this->authUrl;

	}

	
	function getState($state = 'authorization')
	{
		$state = $state . '-' . $this->config['hash'] . '-' . time();

		$stateKey = md5( $state . ':' . SERVICE_OAUTH_STATE_SALT);

		$state = base64url_encode($state . ':' . $stateKey);

		return $state;
	}


	function checkState($state)
    {
    	$state = base64url_decode($state);

    	list($state, $stateKey) = explode(':', $state);

    	if ($stateKey !== md5( $state . ':' . SERVICE_OAUTH_STATE_SALT))
    		return false;

    	$result = array();

    	list($result['state'], $result['service_hash'], $result['time']) = explode('-', $state);

    	return $result;

    }



}



