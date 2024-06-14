wdlib.module("wdlib/utils/object.js", [
],
function(exports) {
"use strict";

/**
 * check if variable is object {}
 *
 * @return bool
 */
exports.isObject = function(item)
{
	return (item !== null && typeof item === "object" && !Array.isArray(item));
}

/**
 * @param Object data
 * @param string|int key
 * @param mixed def, default null
 * @return mixed
 */
exports.isset = function(data, key, def = undefined)
{
	var retval = def;
	if(data && data.hasOwnProperty(key)) {
//	if(data && (data[key] || data.hasOwnProperty(key))) {
		retval = data[key];
	}
	return retval;
}

/**
 * @param Array arr
 * @return mixed
 */
exports.randomItem = function(arr)
{
	var idx = Math.floor((Math.random() * 1000) % arr.length);
	return arr[idx];
}

/**
 * @param Array arr
 *
 * функция взята из статейки на хабре
 * https://habr.com/ru/post/358094/
 */
exports.shuffle = function(arr)
{
	var j, temp;
	for(var i = arr.length - 1; i > 0; i--) {
		j = Math.floor(Math.random()*(i + 1));
		temp = arr[j];
		arr[j] = arr[i];
		arr[i] = temp;
	}
	return arr;
}

exports.toString = function(o)
{
	return JSON.stringify(o);
}

}, ((wdlib.utils = wdlib.utils || {}).object = {}));
