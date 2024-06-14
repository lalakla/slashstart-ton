wdlib.module("/js/config.js", [
],
function(exports) {
"use strict";

var _config = {};

exports.init = function(data)
{
	_config = data;
}

exports.value = function(key)
{
	return _config[key];
}
exports.intval = function(key)
{
	return parseInt(app.Config.value(key) || 0);
}
exports.floatval = function(key)
{
	return parseFloat(app.Config.value(key) || 0);
}
exports.strval = function(key)
{
	return String(app.Config.value(key) || "");
}
exports.vecval = function(key)
{
	// @TODO check it
	return app.Config.value(key) || [];
}

// APP CONSTANTS ==============================================================

// errors
exports.OK = 0;
exports.ERROR = -1;
exports.ERROR_DB = -2;
exports.ERROR_NOT_FOUND = -3;
exports.ERROR_NOT_ENOUGH_DATA = -4;
exports.ERROR_AUTH = -5;
exports.ERROR_INVALID_DATA = -6;

}, ((this.app = this.app || {})).Config = {});
