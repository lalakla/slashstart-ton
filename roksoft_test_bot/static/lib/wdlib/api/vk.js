wdlib.module("wdlib/api/vk.js", [
	"wdlib/api/vk/base.js"
],
function(exports) {
"use strict";

var _users = new Map;
var _installed = undefined;

var VK_JS_SDK = "https://vk.com/js/api/xd_connection.js?2";

// ============================================================================
// wdlib.api.vk.Api class

class Api extends wdlib.api.vk.Base {

	constructor(args)
	{
		super(args);
		
		console.log("VK API : ", args);

		this.viewer_id = args.viewer_id || this.viewer_id;
		this.auth_key = args.auth_key || "";
		this.app_id = args.api_id || "";
		this.partner_url = "http://vk.com";

		this.extra_param = args.request_key || args.referrer || "";
		if(this.extra_param) {
			this.extra_param = this.extra_param.replace(/^ad_/, "");
		}

		wdlib.module.config({
			map: {
				"vk.api.js" : VK_JS_SDK
			}
		});
	}

	init(callback, on_error)
	{
		if(this._init_state != wdlib.api.INIT_STATE_START) {
			// double call of api.init
			var error = new wdlib.model.Error({error: "API INIT ERROR : double call", error_code: wdlib.model.error.ERROR_API});
			error.file = "wdlib.api.vk.js";

			if(this.on_error_callback) {
				this.on_error_callback.call(null, error);
			}
			return;
		}
		
		super.init(callback, on_error);

		var self = this;
		this._init_state = wdlib.api.INIT_STATE_CALLED;

		wdlib.load([{src: "vk.api.js", type: "js", crossOrigin: null}], function() {

			if(self._init_state != wdlib.api.INIT_STATE_CALLED) {
				// double call of api.init
				var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.load_sdk", error_code: wdlib.model.error.ERROR_API});
				error.file = "wdlib.api.vk.js";

				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
				return;
			}
			self._init_state = wdlib.api.INIT_STATE_SDK_LOADED;


			try {
				VK.init(function() {

					if(self._init_state != wdlib.api.INIT_STATE_SDK_LOADED) {
						// double call of api.init
						var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.init_sdk", error_code: wdlib.model.error.ERROR_API});
						error.file = "wdlib.api.vk.js";

						if(self.on_error_callback) {
							self.on_error_callback.call(null, error);
						}
						return;
					}

					// API INIT OK
					console.log("VK API: init ok");
					self._init_state = wdlib.api.INIT_STATE_INIT_PASSED;
	
					self.getProfile(self.viewer_id, function(data) {
						if(self._init_state != wdlib.api.INIT_STATE_INIT_PASSED) {
							// double call of api.init
							var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.getProfile", error_code: wdlib.model.error.ERROR_API});
							error.file = "wdlib.api.vk.js";

							if(self.on_error_callback) {
								self.on_error_callback.call(null, error);
							}
							return;
						}

						self._init_state = wdlib.api.INIT_STATE_COMPLETE;
						callback.call(null, data, self.auth_key);
					});

				}, function() {
					console.log("VK API: init FALSE");

					// some error occured
					var error = new wdlib.model.Error({error: "API INIT ERROR : VK.init error called", error_code: wdlib.model.error.ERROR_API});
					error.file = "wdlib.api.vk.js";
//					error.error += "; err=" + err.toString();
	
					if(self.on_error_callback) {
						self.on_error_callback.call(null, error);
					}

				}, wdlib.api.vk.API_VERSION);
			}
			catch(e) {
				console.log("VK API INIT ERROR CATCH : ", e);
				
				var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.js"});
				error.error += "; method=INIT";
				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
			}

		});
	}

	_call(method, params, callback)
	{
		try {
			VK.api(method, params, function(data) {
				callback.call(undefined, data);
			});
		}
		catch(e) {
			console.log("wdlib.api.vk.js : _call : error catch : ", e);

			var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.ok.js"});
			error.error += "; method=" + method;
			error.error += "; params=" + JSON.stringify(params);
			if(this.on_error_callback) {
				this.on_error_callback.call(null, error);
			}
		}
	}
	_ui_error(e, method)
	{
		var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.ok.js"});
		error.error += "; method=" + method;
		if(this.on_error_callback) {
			this.on_error_callback.call(null, error);
		}
	}

	billingDialog(gold, amount, callback, name, desc, pic, extra)
	{
		var _handler = function(result)
		{
			VK.removeCallback("onOrderSuccess", _success);
			VK.removeCallback("onOrderCancel", _cancel);
			VK.removeCallback("onOrderFail", _cancel);

			callback.call(null, amount, result);
		}
		var _success = function(order_id)
		{
			_handler(wdlib.api.BILLING_TRUE);
		}
		var _cancel = function()
		{
			_handler(wdlib.api.BILLING_FALSE);
		}
		
		VK.addCallback("onOrderSuccess", _success);
		VK.addCallback("onOrderCancel", _cancel);
		VK.addCallback("onOrderFail", _cancel);

		try {
			VK.callMethod("showOrderBox", {
				type: "item",
				item: (amount) ? String(amount) : extra
			});
		}
		catch(e) {
			this._ui_error(e, "billingDialog");
		}
	}
	
	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	messageDialog(mess, uids, callback, extra)
	{
		// call for ONE user per time !!!
		var user_id = wdlib.utils.intval(uids[0]);
		
		var _handler = function(result)
		{
			VK.removeCallback("onRequestSuccess", _success);
			VK.removeCallback("onRequestCancel", _cancel);
			VK.removeCallback("onRequestFail", _cancel);

			callback.call(null, result);
		}
		var _success = function()
		{
			_handler.call(undefined, [user_id]);
		}
		var _cancel = function()
		{
			_handler.call(undefined, undefined);
		}
		
		VK.addCallback("onRequestSuccess", _success);
		VK.addCallback("onRequestCancel", _cancel);
		VK.addCallback("onRequestFail", _cancel);

		try {
			VK.callMethod("showRequestBox", user_id, mess, (extra && extra.extra) ? extra.extra : "");
		}
		catch(e) {
			this._ui_error(e, "messageDialog");
		}
	}
	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	inviteDialog(mess, uids, callback, extra)
	{
		if(uids.length == 1) {
			this.messageDialog(mess, uids, callback, extra);
		}
		else {
			try {
				VK.callMethod("showInviteBox");
			}
			catch(e) {
				this._ui_error(e, "inviteDialog");
			}

			// call immediatly, cause VK doesn't notify about invites sended ...
			callback.call(undefined, undefined);
		}
	}

	/**
	 * @param int width
	 * @param int height
	 */
	setAppSize(width, height)
	{
		try {
			VK.callMethod("resizeWindow", width, height);
		}
		catch(e) {
			this._ui_error(e, "setAppSize");
		}
	}

	/**
	 * @param Function callback
	 */
	favouritesDialog(callback)
	{
		var _handler = function(result)
		{
			VK.removeCallback("onSettingsChanged", _handler);
			VK.removeCallback("onSettingsCancel", _handler);
			
			// console.log("wdlib.api.vk.Api::favouritesDialog result: ", result);
			
			var r = wdlib.utils.intval(result ? result : 0);
			callback.call(undefined, (r & wdlib.api.vk.VK_PERM_FAVOURITES) ? 1 : 0);
		}
		
		VK.addCallback("onSettingsChanged", _handler);
		VK.addCallback("onSettingsCancel", _handler);

		try {
			VK.callMethod("showSettingsBox", wdlib.api.vk.VK_PERM_FAVOURITES);
		}
		catch(e) {
			this._ui_error(e, "favouritesDialog");
		}
	}
}

exports.Api = Api;
// ============================================================================

// ============================================================================
// wdlib.api.vk static functions
exports.getProfile = function(uid, access_token, callback)
{
	var req = "https://api.vk.com/method/users.get?user_ids="+uid+"&access_token="+access_token+"&v="+wdlib.api.vk.API_VERSION+"&fields="+wdlib.api.vk.USER_FIELDS;
	$.ajax({
		url : req,
		type : "GET",
		dataType : "jsonp",
		success : function(data){

			console.log(data);

			var user = {
				remote_id: "",
				platform: wdlib.Config.CLIENT_PLATFORM,
				name: "",
				sex: 0,
				pic: "",
				big_pic: "",
				age: 0,
				birthday: "",
				city: wdlib.api.DEFAULT_CITY,
				city_id: 0,
				anketa_link: wdlib.api.vk.VK_URL,
				has_mobile: 0
			};

			if(data && data.response && data.response[0]) {
				data = data.response[0];
				user.remote_id = data.id;
				user.name = data.first_name;
				user.sex = data.sex || user.sex;
				user.pic = data.photo_200 || user.pic;
				user.big_pic = data.photo_max_orig || user.big_pic;
				user.birthday = data.bdate || user.birthday;
				user.has_mobile = data.has_mobile || user.has_mobile;
				user.photo = {
					photo_50: data.photo_50,
					photo_100: data.photo_100,
					photo_200: data.photo_200,
					photo_200_orig: data.photo_200_orig,
					photo_400: data.photo_400,
					photo_400_orig: data.photo_400_orig,
					photo_max: data.photo_max,
					photo_max_orig: data.photo_max_orig
				}
				if(data.city) {
					if(data.city instanceof Object) {
						user.city = data.city.title;
						user.city_id = data.city.id;
					}
					else {
						user.city = "";
						user.city_id = wdlib.utils.intval(data.city);
					}
				}
				user.anketa_link = wdlib.api.vk.VK_URL + "/id" + user.remote_id,

				wdlib.api.vk.setUserAge(user);
			}

			callback.call(undefined, user);
		}
	});

}
// ============================================================================

}, (wdlib.api = wdlib.api || {}).vk = wdlib.api.vk || {});
