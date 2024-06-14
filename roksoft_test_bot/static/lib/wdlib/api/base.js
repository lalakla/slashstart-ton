wdlib.module("wdlib/api/base.js", [
],
function(exports) {
"use strict";

// PLATFORM CONSTANTS
exports.API_LOCAL = 0;
exports.API_MAMBA = 1;
exports.API_VK = 2;
exports.API_FS = 3;
exports.API_MAMBA_TEST = 4;
exports.API_VK_TEST = 5;
exports.API_FS_TEST = 6;
exports.API_OK = 7;
exports.API_OK_TEST = 8;
exports.API_MM = 9;
exports.API_MM_TEST = 10;
exports.API_AUTOBOT = 11;
exports.API_OK_MOBILE = 12;
exports.API_VK_MOBILE = 13;
exports.API_MM_MOBILE = 14;
exports.API_FS_MOBILE = 15;
exports.API_VK_STANDALONE = 19;
exports.API_OK_STANDALONE = 20;
exports.API_TELEGRAM = 21;
exports.API_STANDALONE = 99;

// SEX CONSTANTS
exports.MALE = 2;
exports.FEMALE = 1;

// other CONSTANTS
exports.DEFAULT_CITY = "Скрытенбург";

exports.BILLING_TRUE = 1;
exports.BILLING_WAIT = 0;
exports.BILLING_FALSE = -1;

exports.INIT_STATE_START = 0;
exports.INIT_STATE_CALLED = 1;
exports.INIT_STATE_SDK_LOADED = 2;
exports.INIT_STATE_INIT_PASSED = 3;
exports.INIT_STATE_COMPLETE = 4;

// ============================================================================
// wdlib.api.Base class

class Base {

	constructor(args)
	{
		this.viewer_id = "0";
		this.extra_param = "";
		this.api_args = args || {};
		this.on_error_callback = undefined;
		this.partner_url = "";

		this._init_state = 0;
	}

	getApiArgs()
	{
		return this.api_args;
	}

	/**
	 * @param String extra [optional, default = undefined]
	 * @return String
	 */
	appurl(extra)
	{
		console.log("wdlib.api.Base::appurl called");
		return "";
	}
	/**
	 * @param Object user
	 * @param String extra [optional, default = undefined]
	 * @return String
	 */
	profileurl(user, extra)
	{
		console.log("wdlib.api.Base::profileurl called");
		return "";
	}
	/**
	 * @return String
	 */
	extra()
	{
		return this.extra_param;
	}

	init(args, on_error)
	{
		this.on_error_callback = on_error;
	}

	storageGet(keys, callback)
	{
		console.log("wdlib.api.Base::storageGet called");
	}
	storageSet(key, value)
	{
		console.log("wdlib.api.Base::storageSet called");
	}
	
	/**
	 * @param callback
	 * @params String scope1, scope2, scope3, ...
	 */
	checkAllowedScopes()
	{
		console.log("wdlib.api.Base::checkAllowedScopes called");
		
		let args = Array.prototype.slice.call(arguments);
		let callback = args.shift();

		callback.call(undefined, true);
	}

	/**
	 * @param String user_id
	 * @param Function callback
	 */
	getProfile(user_id, callback)
	{
		console.log("wdlib.api.Base::getProfile called");
	}

	/**
	 * @param String user_id
	 * @param Function callback
	 */
	getUserPhotos(user_id, callback)
	{
		console.log("wdlib.api.Base::getUserPhotos called");
	}

	/**
	 * @param Function callback
	 */
	getOffers(callback)
	{
		console.log("wdlib.api.Base::getOffers called");
	}

	/**
	 * @param int limit
	 * @param Function callback
	 */
	getFriends(limit, callback)
	{
		console.log("wdlib.api.Base::getFriends called");
	}

	/**
	 * @param int gold
	 * @param int amount
	 * @param Function callback
	 * @param String name
	 * @param String desc
	 * @param String pic
	 * @param String extra
	 */
	billingDialog(gold, amount, callback, name, desc, pic, extra)
	{
		console.log("wdlib.api.Base::billingDialog called");
	}

	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	messageSend(mess, uids, callback, extra)
	{
		this.messageDialog(mess, uids, callback, extra);
	}

	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	messageDialog(mess, uids, callback, extra)
	{
		setTimeout(function() {
			callback.call(undefined, undefined);
		}, 1000);
	}

	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	inviteDialog(mess, uids, callback, extra)
	{
		this.messageDialog(mess, uids, callback, extra);
	}

	/**
	 * @param int width
	 * @param int height
	 */
	setAppSize(width, height)
	{
		console.log("wdlib.api.Base::setAppSize called");
	}
	
	scrollApp(top, speed = undefined)
	{
		console.log("wdlib.api.Base::scrollApp called");
	}

	/**
	 * @param Function callback
	 */
	isFavourites(callback)
	{
		console.log("wdlib.api.Base::isFavourites called");
	}
	/**
	 * @param Function callback
	 */
	favouritesDialog(callback)
	{
		console.log("wdlib.api.Base::favouritesDialog called");
	}

	/**
	 * @param String group_id
	 * @param String user_id
	 * @param Function callback
	 */
	isGroupMember(group_id, user_id, callback)
	{
		console.log("wdlib.api.Base::isGroupMember called");
	}

	/**
	 * @param String type
	 * @param Function callback
	 */
	getUploadServer(type, callback)
	{
	}
	/**
	 * @param String user_id
	 * @param String type
	 * @param Object data
	 * @param Function callback
	 */
	saveUploadedData(user_id, type, data, callback)
	{
	}

	/**
	 * @param String user_id
	 * @param String mess
	 * @param Object data
	 * @param Function callback
	 */
	wallPost(user_id, mess, data, callback)
	{
	}

	/**
	 * @param String user_id
	 * @param Object data
	 * @param Function callback
	 */
	isLiked(user_id, data, callback)
	{
	}

	/**
	 * @param int counter
	 * @param Boolean increment [optional, default=false]
	 */
	setCounter(counter, increment = false)
	{
	}
}

exports.Base = Base;

// ============================================================================

// ============================================================================
// STATIC HELPER FUNCTIONS

exports.isApi = function()
{
	let api = wdlib.Config.CURRENT_API || wdlib.Config.CLIENT_PLATFORM
	for(var i=0; wdlib.api.IApi && i<arguments.length; ++i) {
		if(api == arguments[i]) {
			return true;
		}
	}

	return false;
}
exports.isApiCurrent = function()
{
	var api = wdlib.Config.CURRENT_API || wdlib.Config.CLIENT_CURRENT_PLATFORM || wdlib.Config.CLIENT_PLATFORM;
	for(var i=0; wdlib.api.IApi && i<arguments.length; ++i) {
		if(api == arguments[i]) {
			return true;
		}
	}

	return false;
}
exports.name = function(api)
{
	var r = "unknown";

	switch(api) {
		case wdlib.api.API_LOCAL:
			r = "local";
			break;
		case wdlib.api.API_MAMBA:
		case wdlib.api.API_MAMBA_TEST:
			r = "mamba";
			break;
		case wdlib.api.API_FS:
		case wdlib.api.API_FS_TEST:
			r = "fotostrana";
			break;
		case wdlib.api.API_FS_MOBILE:
			r = "fotostrana-mobile";
			break;
		case wdlib.api.API_VK:
		case wdlib.api.API_VK_TEST:
			r = "vkontakte";
			break;
		case wdlib.api.API_VK_MOBILE:
			r = "vkontakte-mobile";
			break;
		case wdlib.api.API_OK:
		case wdlib.api.API_OK_TEST:
			r = "odnoklassniki";
			break;
		case wdlib.api.API_OK_MOBILE:
			r = "odnoklassniki-mobile";
			break;
		case wdlib.api.API_MM:
		case wdlib.api.API_MM_TEST:
			r = "moi-mir";
			break;
		case wdlib.api.API_MM_MOBILE:
			r = "moi-mir-mobile";
			break;
	}

	return r;
}

// ============================================================================

// current API referrence
exports.IApi = null;
	
}, (wdlib.api = wdlib.api || {}));
