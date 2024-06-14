wdlib.module("wdlib/utils/string.js", [
	"wdlib/api/base.js"
],
function(exports) {
"use strict";

var GENDER_PATTERN = new RegExp("%gender\\(([^,]+),([^\\)]+)\\)%", "g");
var USER_PATTERN = new RegExp("%user\\.([a-zA-Z0-0_]+)%", "g");
var TRIM_PATTERN = new RegExp("^[\\s]+|[\\s]+$", "g");
var LTRIM_PATTERN = new RegExp("^[\\s]+", "g");
var RTRIM_PATTERN = new RegExp("[\\s]+$", "g");

// ============================================================================
// wdlib.utils.string interface

exports._plural = function(n, v1, v2, v3) {
	var m = n % 10;

	if (((n % 100) > 10) && ((n % 100) < 20)) {
		return v3;
	}
	else if(m == 1) {
		return v1;
	}
	else if(m > 1 && m < 5) {
		return v2;
	}

	return v3;
}
exports.plural = function(n, v1, v2, v3) {
	return String(n) + " " + exports._plural(n, v1, v2, v3);
}

/**
 * @param String str
 * @return String
 */
exports.trim = function(str)
{
	return str.replace(TRIM_PATTERN, '');
}
exports.ltrim = function(str)
{
	return str.replace(LTRIM_PATTERN, '');
}
exports.rtrim = function(str)
{
	return str.replace(RTRIM_PATTERN, '');
}

/**
 * @param String str
 * @param int n
 * @return String
 */
exports.cat = function(str, n)
{
	if(str.length > n) {
		return str.substr(0, n);
	}
	return str;
}

/**
 * @param int sex
 * @param String v1
 * @param String v2
 * @return String
 */
exports.gender = function(sex, v1, v2)
{
	return (sex == wdlib.api.FEMALE) ? v2 : v1;
}

/**
 * @param int sex
 * @param String str
 * @return String
 */
exports.genderPattern = function(sex, str)
{
	var s = str.replace(GENDER_PATTERN, function() {
		var s = (sex == wdlib.api.FEMALE) ? arguments[2] : arguments[1];
		return s;
	});

	return s;
}
/**
 * @param wdlib.model.User user
 * @param String str
 * @return String
 */
exports.userPattern = function(user, str)
{
	var s = str.replace(USER_PATTERN, function() {

		var s = "";
		if(user.hasOwnProperty(arguments[1])) {
			s = user[arguments[1]];
		}

		return s;
	});

	return s;
}

/**
 * @param int c
 * @param Array<String> forms
 * @return String
 */
exports.cases = function(c, forms)
{
	if(!forms || !forms.length) return "";
 
	c = c < forms.length - 1 ? c : forms.length - 1;
	return forms[c];
}

/**
 * @param String str
 * @return String
 */
exports.upperFirst = function(str)
{
	if(str.length) {
		str = str.charAt(0).toUpperCase() + str.substr(1);
	}
	return str;
}

exports.htmlDecode = function(str)
{
	var ta = document.createElement("textarea");
	ta.innerHTML = str;
	return ta.textContent;
}
exports.htmlEncode = function(str)
{
	var ta = document.createElement("textarea");
	ta.textContent = str;
	return ta.innerHTML;
}

exports.nl2br = function(str)
{
	return str.replace(/\n/g, "<br/>");
}

// ============================================================================

}, ((wdlib.utils = wdlib.utils || {}).string = {}));
