wdlib.module("wdlib/api/standalone.js", [
	"wdlib/api/base.js",
	"wdlib/model/user/user.js",
	"wdlib/utils/object.js"
],
function(exports) {
"use strict";

var _user = {
	remote_id: wdlib.Config.CLIENT_USER_ID,
	platform: wdlib.Config.CLIENT_PLATFORM,
	name: "",
	sex: 0,
	pic: "",
	big_pic: "",
	age: 0,
	city: wdlib.api.DEFAULT_CITY,
	anketa_link: "",
	has_mobile: 0
};
var _users = {};

// ============================================================================
// wdlib.api.standalone.Api class

class Api extends wdlib.api.Base {

	constructor(args)
	{
		super(args);

		this.partner_url = "http://localhost";
		this._extra = args.extra || "";

		this.viewer_id = args.viewer_id || this.viewer_id;
	}

	appurl(extra)
	{
		return "" + (extra ? extra : ""); 
	}
	extra()
	{
		return this._extra;
	}

	init(callback)
	{
		super.init(callback);

		_user.remote_id = wdlib.Config.CLIENT_USER_ID;
		_user.platform = wdlib.Config.CLIENT_PLATFORM;

		setTimeout(function() {
			// API INIT OK
			console.log("STANDALONE API: init ok");
			callback.call(null, _user);
		}, 500);
	}

	/**
	 * @param String user_id
	 * @param Function callback
	 */
	getUserPhotos(user_id, callback)
	{
		setTimeout(function() {
			callback.call(null, user_id, []);
		}, 500);
	}

	/**
	 * @param int limit
	 * @param Function callback
	 */
	getFriends(limit, callback)
	{
		setTimeout(function() {
			callback.call(null, []);
		}, 500);
	}

	billingDialog(gold, amount, callback, name, desc, pic, extra)
	{
		callback.call(null, amount, wdlib.api.BILLING_FALSE);
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

			var sended = undefined;
			
			callback.call(undefined, sended);
		}, 500);
	}

	setAppSize(width, height)
	{
	}
}

exports.Api = Api;

// ============================================================================

}, (wdlib.api = wdlib.api || {}).standalone = {});
