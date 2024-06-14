wdlib.module("wdlib/utils/date.js", [
	"wdlib/utils/string.js"
],
function(exports) {
"use strict";

/**
* Parse string like 'YYYY-MM-DD HH:MM:SS' to Date object
* @param String str
* @param Object output [optional, default=undefined]
* @return Date
*/
exports.parse = function(str, output = undefined)
{
	var idx = 0;
	var _idx = 0;

	var vals = [];

	var buffer = "";
	for(var i=0; i<str.length; ++i) {
		switch(str.charAt(i)) {
			case '0':
			case '1':
			case '2':
			case '3':
			case '4':
			case '5':
			case '6':
			case '7':
			case '8':
			case '9':
				buffer += str.charAt(i);
				break;
			case '-':
			case ':':
			case ' ':
				if(buffer.length) {
					vals.push(parseInt(buffer));
					buffer = "";
				}
				break;
			default:
				break;
		}
	}
	if(buffer.length) {
		vals.push(parseInt(buffer));
	}

	if(output) {
		output.vals = vals;
	}

	return new Date(
		vals.length > 0 ? vals[0] : 0, // year
		vals.length > 1 ? vals[1] - 1 : 0, // month
		vals.length > 2 ? vals[2] : 0, // day
		vals.length > 3 ? vals[3] : 0, // hour
		vals.length > 4 ? vals[4] : 0, // minute
		vals.length > 5 ? vals[5] : 0 // second
	);
}
/**
* Parse string like 'DD.MM.YYYY' to Date object
* @param String str
* @param Object output [optional, default=undefined]
* @return Date
*/
exports.parse2 = function(str, output = undefined)
{
	var idx = 0;
	var _idx = 0;

	var vals = [];

	var buffer = "";
	for(var i=0; i<str.length; ++i) {
		switch(str.charAt(i)) {
			case '0':
			case '1':
			case '2':
			case '3':
			case '4':
			case '5':
			case '6':
			case '7':
			case '8':
			case '9':
				buffer += str.charAt(i);
				break;
			case '.':
				if(buffer.length) {
					vals.push(parseInt(buffer));
					buffer = "";
				}
				break;
			default:
				break;
		}
	}
	if(buffer.length) {
		vals.push(parseInt(buffer));
	}

	if(output) {
		output.vals = vals;
	}

	return new Date(
		vals.length > 2 ? vals[2] : 0, // year
		vals.length > 1 ? vals[1] - 1 : 0, // month
		vals.length > 0 ? vals[1] : 0 // day
	);
}

/**
 * @param int timeout [seconds]
 * @param String fmt [optional, default=undefined]
 * @return String
 */
exports.timeout2str = function(timeout, fmt = undefined)
{
	fmt = fmt ? fmt : "dhms";

	var days = Math.floor(timeout / 86400);
	timeout -= days * 86400;
	var hours = Math.floor(timeout / 3600);
	timeout -= hours * 3600;
	var minutes = Math.floor(timeout / 60);
	var seconds = Math.floor(timeout % 60);

	var str = "";
	if(days && (fmt.indexOf('d') != -1)) {
		str += wdlib.utils.string.plural(days, "день", "дня", "дней");
	}
	if(hours && (fmt.indexOf('h') != -1)) {
		if(str.length) str += " ";
		str += wdlib.utils.string.plural(hours, "час", "часа", "часов");
	}
	if(minutes && (fmt.indexOf('m') != -1)) {
		if(str.length) str += " ";
		str += wdlib.utils.string.plural(minutes, "минута", "минуты", "минут");
	}
	if(seconds && (fmt.indexOf('s') != -1)) {
		if(str.length) str += " ";
		str += wdlib.utils.string.plural(seconds, "секунда", "секунды", "секунд");
	}

	return str;
}

exports.diffTimeDays = function(start, end)
{
	if(end < start) {
		var _end = end;
		end = start;
		start = _end;
	}

	var days = wdlib.utils.intval((end - start) / 86400);

	if(!days) {
		var _start = new Date(start * 1000);
		var _end = new Date(end * 1000);

		if(_end.getDate() != _start.getDate()) days++;
	}

	return days;
}


var MONTHS = [
	["Январь", "Января"],
	["Февраль", "Февраля"],
	["Март", "Марта"],
	["Апрель", "Апреля"],
	["Май", "Мая"],
	["Июнь", "Июня"],
	["Июль", "Июля"],
	["Август", "Августа"],
	["Сентябрь", "Сентября"],
	["Октябрь", "Октября"],
	["Ноябрь", "Ноября"],
	["Декабрь", "Декабря"]
];
exports.MONTHS = MONTHS;

/**
 * @param int ts
 * @return String
 */
exports.monthDate = function(ts = 0)
{
	var date = new Date;
	if(ts) {
		date.setTime(ts);
	}

	var str = String(date.getDate()) + " " + MONTHS[date.getMonth()][1];
	return str;
}
/**
 * @param int ts
 * @return String
 */
exports.month = function(ts = 0)
{
	var date = new Date;
	if(ts) {
		date.setTime(ts);
	}

	var str = MONTHS[date.getMonth()][0];
	return str;
}

}, ((wdlib.utils = wdlib.utils || {}).date = {}));
