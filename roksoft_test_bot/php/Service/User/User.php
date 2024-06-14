<?php

class Service_User_User {

	const FLAG_CONFIRMED = 0x1;
	const FLAG_MODER = 0x8;
	const FLAG_ADMIN = 0x10;
	const FLAG_SUPER_ADMIN = 0x20;

	static public function isUserLogged(\WDLIB\Model_User $user = null) : bool
	{
		return $user != null && $user->id && $user->id !== "" && $user->id !== "0";
	}
	static public function isUserConfirmed(\WDLIB\Model_User $user = null) : bool
	{
		return self::isUserLogged($user) && ($user->flags & self::FLAG_CONFIRMED);
	}
	static public function isUserModer(\WDLIB\Model_User $user = null) : bool
	{
		return self::isUserConfirmed($user) && ($user->flags & self::FLAG_MODER);
	}
	static public function isUserAdmin(\WDLIB\Model_User $user = null) : bool
	{
		return self::isUserConfirmed($user) && ($user->flags & self::FLAG_ADMIN);
	}
	static public function isUserSuperAdmin(\WDLIB\Model_User $user = null) : bool
	{
		return self::isUserAdmin($user) && ($user->flags & self::FLAG_SUPER_ADMIN);
	}

	static public function registerByApi(\WDLIB\Api_Base $api, \WDLIB\Model_User_ApiAuth $api_user, array $user_data, array &$errors) : ?\WDLIB\Model_User
	{
		$ok = true;
		$user = null;
		$_user_exists = false;

		// проверим, нет ли уже этого пользователя в другом - смежном апи
		// например если пришел от ВК-миниапп - посмотрим, а нет ли такого в ВК-сайты

		$_other_api = null;
		if($api_user->platform == \WDLIB\API_VK) {
			$_other_api = \WDLIB\Api_Storage::getInstance()->getApi(\WDLIB\API_VK_AUTH);
		}
		if($api_user->platform == \WDLIB\API_VK_AUTH) {
			$_other_api = \WDLIB\Api_Storage::getInstance()->getApi(\WDLIB\API_VK);
		}
		if($_other_api && ($_other_api_user = \WDLIB\Model_User_ApiAuth::selectOne($_other_api->platform, $api_user->api_user_id))) {
			// есть уже!
			// добавим к нему же
			if($user = \WDLIB\Model_User::selectById($_other_api_user->user_id)) {
				$_user_exists = true;
			}
		}

		if(!$user) {
			// create new user
			$user = new \WDLIB\Model_User;
			$user->name = \WDLIB\Util_Array::isset($user_data, "user-name", "");
			$user->flags = $user->flags | self::FLAG_CONFIRMED; // при регистрации через АПИ (соц.сети) - сразу ставим CONFIRMED 
			$user->reg_date = $api_user->reg_date;
			$user->last_date = $api_user->last_date;
			$user->reg_ip = $api_user->reg_ip;
			$user->last_ip = $api_user->last_ip;

			if(\WDLIB\Model_User::update($user) != \WDLIB\OK) {
				\WDLIB\Logger::error(__METHOD__." : ERROR : Model_User::update failed");
				$ok = false;
			}
		}

		if($ok) {
			$api_user->user_id = $user->id;
			if(\WDLIB\Model_User_ApiAuth::update($api_user) != \WDLIB\OK) {
				\WDLIB\Logger::error(__METHOD__." : ERROR : Model_User_ApiAuth::update failed");
				$ok = false;
			}
		}

		if($ok) {
			// add stats
			Service_Stats::_add($api_user->platform, $api_user->user_id, "user-register");
		}

		if(!$ok) {
			if(empty($errors)) {
				$errors["error"] = "Внутренняя ошибка!";
			}
		}

		return $ok ? $user : null;
	}
}
