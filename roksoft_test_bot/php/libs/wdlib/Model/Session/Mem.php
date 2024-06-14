<?php

namespace WDLIB;

class Model_Session_Mem extends Model_Session_Base {

	const KEY_PREFIX = "sess.key.";
	const EXPIRES = 3600;

	public function __construct()
	{
		parent::__construct();
	}

	public function updateSelf() : int
	{
		return self::update($this);
	}

	static public function selectByKey(string $key, $__item = null, string $prefix = null) : ?Model_Session_Mem
	{
		$_item = ($__item) ? $__item : new Model_Session_Mem;
		$_item->clear();

		$_item_ok = false;

		$mem = Driver_Memcached::getInstance();

		$prefix = ($prefix !== null) ? self::KEY_PREFIX.$prefix : self::KEY_PREFIX;
		$sess_key = $prefix.$key;

		if($data = $mem->get($sess_key)) {
			$_item->initFromRow($data);
			$_item_ok = true;
		}

		return $_item_ok ? $_item : null;
	}

	static public function update(Model_Session_Mem $item, string $prefix = null, int $expires = null) : int
	{
		$mem = Driver_Memcached::getInstance();

		$prefix = ($prefix !== null) ? self::KEY_PREFIX.$prefix : self::KEY_PREFIX;
		$sess_key = $prefix.$item->key;

		$expires = ($expires !== null) ? $expires : self::EXPIRES;

		$data = $mem->set($sess_key, array(
			"key" => $item->key,
			"user_id" => $item->user_id,
			"fingerprint" => $item->fingerprint,
			"data" => clsv_binary_serialize($item->dataSerialize()),
			"date" => $item->date,
			"status" => $item->status,
			"ip" => $item->ip
		), Core::curtime() + $expires);

		return OK;
	}
}
