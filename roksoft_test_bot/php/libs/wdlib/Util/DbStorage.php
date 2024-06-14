<?php

namespace WDLIB;

class Util_DbStorage {

	const __DB_STORAGE_DEFAULT = "default";

	static public function storage($className)
	{
		$className = str_replace(array('_','\\'), '/', $className);

		$items = explode('/', $className);

		$config = Core::config("mydb_storage_util");
		for($i=0; $i<count($items); ++$i) {
			$o = Util_Array::constIsset($config, $items[$i], self::__DB_STORAGE_DEFAULT);

			if(is_array($o)) {
				$config = $o;
				continue;
			}

			$storage = $o;
			break;
		}

		return $storage;
	}
}
