<?php

namespace WDLIB;

class Util_Date {

	/**
	 * @param string $str, format: 'YYYY-mm-dd hh:mm::ss'
	 */
	static public function getTs(string $str, &$_date = null) : int
	{
		$date = getdate();
		$date["hours"] = 0;
		$date["minutes"] = 0;
		$date["seconds"] = 0;

		$prev = 0;
		$pos = 0;

		// year
		$pos = strpos($str, '-', $prev);
		if($pos !== FALSE) {
			$date["year"] = (int)substr($str, 0, $pos);
			$pos++;
			$prev = $pos;
		}
		// month
		$pos = strpos($str, '-', $prev);
		if($pos !== FALSE) {
			$date["mon"] = (int)substr($str, $prev, $pos - $prev);
			$pos++;
			$prev = $pos;
		}
		// day
		$pos = strpos($str, ' ', $prev);
		if($pos !== FALSE) {
			$date["mday"] = (int)substr($str, $prev, $pos - $prev);
			$pos++;
			$prev = $pos;
		}
		// hour
		$pos = strpos($str, ':', $prev);
		if($pos !== FALSE) {
			$date["hours"] = (int)substr($str, $prev, $pos - $prev);
			$pos++;
			$prev = $pos;

			// minute
			$date["minutes"] = (int)substr($str, $prev);
		}

		$ts = mktime($date["hours"], $date["minutes"], $date["seconds"], $date["mon"], $date["mday"], $date["year"]);
		if($_date !== null) {
			$_date = $date;
		}

		return $ts;
	}

	static public function parse(string $date, string $format = "Y.m.d") : array
	{
		$retval = getdate();

		$date = date_parse_from_format($format, $date);
		$retval["year"] = (int)$date["year"];
		$retval["mon"] = (int)$date["month"];
		$retval["mday"] = (int)$date["day"];
		$retval["hours"] = (int)$date["hour"];
		$retval["minutes"] = (int)$date["minute"];
		$retval["seconds"] = (int)$date["second"];
		$retval[0] = mktime($retval["hours"], $retval["minutes"], $retval["seconds"], $retval["mon"], $retval["mday"], $retval["year"]);

		return $retval;
	}

	static public function parse_ts(string $datestr, string $format = "Y.m.d", &$date = null) : int
	{
		$date = self::parse($datestr, $format);
		return $date[0];
	}

	static public function age(array $bdate) : int
	{
		$cur = getdate();

		$age = ($cur["year"] > $bdate["year"]) ? ($cur["year"] - $bdate["year"]) : 0;
		if($age && ($cur["mon"] < $bdate["mon"] || $cur["mon"] == $bdate["mon"] && $cur["mday"] < $bdate["mday"])) {
			$age--;
		}
		
		return $age;
	}

	static public function diffTimeDays(int $start, int $end) : int
	{
		if($end < $start) {
			$_end = $end;
			$end = $start;
			$start = $_end;
		}

		$days = (int)(($end - $start) / 86400);

		if(!$days) {
			$_start = getdate($start);
			$_end = getdate($end);

			if($_end["mday"] != $_start["mday"]) $days++;
		}


		return $days;
	}
	
	static public function diffTimeHours(int $time0, int $time1) : int
	{
		$min = Util_Base::min($time0, $time1);
		$max = Util_Base::max($time0, $time1);

		$hours = ($max - $min) / 3600;
		return $hours;
	}

	static public function curDay() : int
	{
		$ts = 0;

		$now = Core::curtime();

		$date = getdate($now);
		$ts = mktime(0, 0, 0, $date["mon"], $date["mday"], $date["year"]);

		return $ts;
	}
	static public function nextDay() : int
	{
		$ts = 0;

		$now = Core::curtime();
		$now += 86400;

		$date = getdate($now);
		$ts = mktime(0, 0, 0, $date["mon"], $date["mday"], $date["year"]);

		return $ts;
	}
	static public function prevDay() : int
	{
		$ts = 0;

		$now = Core::curtime();
		$now -= 86400;

		$date = getdate($now);
		$ts = mktime(0, 0, 0, $date["mon"], $date["mday"], $date["year"]);

		return $ts;
	}
	static public function curWeek() : int
	{
		$ts = 0;

		$now = Core::curtime();

		$date = getdate($now);
		$date["mday"] -= $date["wday"];
		$ts = mktime(0, 0, 0, $date["mon"], $date["mday"], $date["year"]);

		return $ts;
	}
	static public function prevWeek() : int
	{
		$ts = 0;

		$now = Core::curtime();
		$now -= 86400 * 7;

		$date = getdate($now);
		$date["mday"] -= $date["wday"];
		$ts = mktime(0, 0, 0, $date["mon"], $date["mday"], $date["year"]);

		return $ts;
	}
	static public function curMonth() : int
	{
		$ts = 0;

		$now = Core::curtime();

		$date = getdate($now);
		$date["mday"] = 1;
		$ts = mktime(0, 0, 0, $date["mon"], $date["mday"], $date["year"]);

		return $ts;
	}
	static public function prevMonth() : int
	{
		$ts = 0;

		$now = Core::curtime();

		$date = getdate($now);
		$date["mday"] = 1;
		$date["mon"] -= 1;
		$ts = mktime(0, 0, 0, $date["mon"], $date["mday"], $date["year"]);

		return $ts;
	}
	static public function nextMonth() : int
	{
		$ts = 0;

		$now = Core::curtime();

		$date = getdate($now);
		$date["mday"] = 1;
		$date["mon"] += 1;
		$ts = mktime(0, 0, 0, $date["mon"], $date["mday"], $date["year"]);

		return $ts;
	}
	
	static public function timeout2str($timeout, $fmt = null) : string
	{
		$fmt = $fmt ? $fmt : "dhms";

		$days = floor($timeout / 86400);
		$timeout -= $days * 86400;
		$hours = floor($timeout / 3600);
		$timeout -= $hours * 3600;
		$minutes = floor($timeout / 60);
		$seconds = floor($timeout % 60);

		$str = "";
		if($days && (strpos($fmt, 'd') !== false )) {
			$str .= Util_String::plural($days, "день", "дня", "дней");
		}
		if($hours && (strpos($fmt, 'h') !== false)) {
			if(strlen($str)) $str .= " ";
			$str .= Util_String::plural($hours, "час", "часа", "часов");
		}
		if($minutes && (strpos($fmt, 'm') !== false)) {
			if(strlen($str)) $str .= " ";
			$str .= Util_String::plural($minutes, "минута", "минуты", "минут");
		}
		if($seconds && (strpos($fmt, 's') !== false)) {
			if(strlen($str)) $str .= " ";
			$str .= Util_String::plural($seconds, "секунда", "секунды", "секунд");
		}

		return $str;
	}
}
