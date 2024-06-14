wdlib.module("wdlib/utils/utils.js", [
],
function(exports) {
"use strict";

/**
 * return integer value of variable
 * @param Mixed num
 * @return int
 */
exports.intval = function(val)
{
	//val >>>= 0;
	val = ~~val;
	return val;
}

/**
 * convert color from int to css.rgba
 * @param int num
 * @return String
 */
exports.rgba = function(num)
{
	num >>>= 0;
	var b = num & 0xFF,
	g = (num & 0xFF00) >>> 8,
	r = (num & 0xFF0000) >>> 16,
	a = 1; /*( (num & 0xFF000000) >>> 24 ) / 255;*/
	return "rgba(" + [r, g, b, a].join(",") + ")";
}

/**
 * @param String url
 * @return String
 */
exports.url = function(url)
{
	var retval = wdlib.Config.PROJECT_URL + url;
	return retval;
}

/**
 * @param String url
 * @return String
 */
exports.appurl = function(url)
{
	var retval = wdlib.Config.APP_URL + url;
	return retval;
}

var APPURL_PATTERN = new RegExp("%appurl\\(([^\\)]+)\\)%", "g");

/**
 * @param String str
 * @return String
 */
exports.appurlPattern = function(str)
{
	var s = str.replace(APPURL_PATTERN, function() {
		var s = wdlib.utils.appurl(arguments[1]);
		return s;
	});

	return s;
}

/**
 * @param String url
 * @return String
 */
exports.staticurl = function(url)
{
	var retval = wdlib.Config.STATIC_URL + url;
	return retval;
}

var HTTP_PATTERN = new RegExp("^http://")
exports.https = function(url)
{
	url = url.replace(HTTP_PATTERN, "https://");
	return url;
}

}, ((wdlib.utils = wdlib.utils || {})));
