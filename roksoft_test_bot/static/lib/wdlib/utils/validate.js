wdlib.module("wdlib/utils/validate.js", [
	"wdlib/utils/string.js"
],
function(exports) {
"use strict";

var _date = new RegExp("^[0-9]{4}-[0-9]{2}-[0-9]{2}$");
var _email = /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;

// ============================================================================
// wdlib.utils.validate.* interface

exports.date = function(str)
{
	return _date.test(str);
}

exports.email = function(str)
{
	return _email.test(str.toLowerCase());
}

// ============================================================================

}, ((wdlib.utils = wdlib.utils || {}).validate = {}));
