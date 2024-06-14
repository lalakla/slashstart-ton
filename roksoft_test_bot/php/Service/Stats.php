<?php

class Service_Stats {

	static public function add(\WDLIB\Model_User $user, $stat, $amount = 1)
	{
		$platform = 0;
		if($info = Model_UserInfo::select($user->id, Model_UserInfo::USER_LAST_PLATFORM)) {
			$platform = $info->get(Model_UserInfo::USER_LAST_PLATFORM);
		}

		self::_add($platform, $user->id, $stat, $amount);
	}

	static public function _add($platform, $user_id, $stat, $amount = 1)
	{
//		\WDLIB\Stats::add($platform, $user_id, $stat, $amount);
	}
}
