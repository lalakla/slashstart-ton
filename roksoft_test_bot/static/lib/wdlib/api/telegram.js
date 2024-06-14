wdlib.module("wdlib/api/telegram.js", [
	"wdlib/api/base.js",
	"wdlib/model/user/user.js",
	"wdlib/utils/object.js",
	"wdlib/utils/string.js",
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.api.telegram.Api class

class Api extends wdlib.api.Base {

	constructor(args)
	{
		super(args);

		this.TG = undefined;
		this.initData = undefined;
		this.userData = undefined;

		wdlib.module.config({
			map: {
				"telegram.api" : "https://telegram.org/js/telegram-web-app.js"
			}
		});
	}

	appurl(extra)
	{
		var url = "https://t.me/";

		if(wdlib.Config.TELEGRAM_WEB_APP_ID) {
			url += wdlib.Config.TELEGRAM_WEB_APP_ID;
		}

		if(extra !== undefined) {
			if(!(typeof extra === "object")) {
				url += "?startapp=" + extra;
			}
			else {
				url += "?startapp=" + wdlib.net.Http.encode(extra);
			}
		}

		return url;
	}
	extra()
	{
		return this._extra;
	}

	init(callback, on_error, on_event = undefined)
	{
		if(this._init_state != wdlib.api.INIT_STATE_START) {
			// double call of api.init
			var error = new wdlib.model.Error({error: "API INIT ERROR : double call", error_code: wdlib.model.error.ERROR_API});
			error.file = "wdlib.api.telegram.js";

			if(this.on_error_callback) {
				this.on_error_callback.call(null, error);
			}
			return;
		}
		
		this.on_error_callback = on_error;
		this.on_event_callback = on_event;

		var self = this;
		this._init_state = wdlib.api.INIT_STATE_CALLED;

		var onWebAppInit = function(data)
		{
			// API INIT OK
			console.log("VK.APP API: init ok : ", data);
			self._init_state = wdlib.api.INIT_STATE_INIT_PASSED;

			// loading current-user profile data
			self.getProfile(self.viewer_id, function(data) {
				console.log("VK.APP API: init.getProfile : ", data);

				if(self._init_state != wdlib.api.INIT_STATE_INIT_PASSED) {
					// double call of api.init
					var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.getProfile", error_code: wdlib.model.error.ERROR_API});
					error.file = "wdlib.api.vk.app.js";

					if(self.on_error_callback) {
						self.on_error_callback.call(null, error);
					}
					return;
				}

				self._init_state = wdlib.api.INIT_STATE_COMPLETE;
				callback.call(null, data);
			});
		}

		wdlib.load([{src: "telegram.api", type: "js", crossOrigin: null}], function() {

			console.log("TELEGRAM.API loaded");

			if(self._init_state != wdlib.api.INIT_STATE_CALLED) {
				// double call of api.init
				var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.load_sdk", error_code: wdlib.model.error.ERROR_API});
				error.file = "wdlib.api.telegram.js";

				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
				return;
			}
			self._init_state = wdlib.api.INIT_STATE_SDK_LOADED;

			try {
				console.log("TELEGRAM API : ", window.Telegram.WebApp);

				self.TG = window.Telegram.WebApp;
				self.initData = {};

				var initDataParams = self.TG.initData.split('&');
				for(var i=0; i<initDataParams.length; i++) {
					var tmp = initDataParams[i].split("=");
					self.initData[tmp[0]] = decodeURIComponent(tmp[1]);
				}

				var extraParams = wdlib.utils.object.isset(self.TG.initDataUnsafe, "start_param", undefined);
				if(extraParams) {
					self._extra = {};
					extraParams = extraParams.split('&');
					for(var i=0; i<extraParams.length; i++) {
						var tmp = extraParams[i].split("=");
						self._extra[tmp[0]] = decodeURIComponent(tmp[1]);
					}
				}
				
				console.log("TELEGRAM API : initData : ", self.initData, self.TG.initDataUnsafe, self._extra);

				if(self.initData.hasOwnProperty("user")) {
					self.userData = JSON.parse(self.initData["user"]);
				
					console.log("TELEGRAM API : initData : userData ", self.userData);

					var user = {
						remote_id: self.userData.id,
						api_user_id: self.userData.id,
						platform: wdlib.api.API_TELEGRAM,
						name: wdlib.utils.string.trim(self.userData["first_name"] + ' ' + self.userData["last_name"]),
						sex: 0,
						pic: "",
						big_pic: "",
						age: 0,
						city: wdlib.api.DEFAULT_CITY,
						city_id: 0,
						anketa_link: "https://t.me/" + self.userData["username"],
					}
					
//					callback.call(null, user, self.TG.initData);
					callback.call(null, user, self.initData);
				}
			}
			catch(e) {
				console.error("TELEGRAM API INIT ERROR CATCH : ", e);
				
				var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.telegram.js"});
				error.error += "; method=INIT";
				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
			}

		});
	}

	billingDialog(gold, amount, callback, name, desc, pic, extra)
	{
		callback.call(null, amount, wdlib.api.BILLING_TRUE);
	}

}

exports.Api = Api;
// ============================================================================

}, (wdlib.api = wdlib.api || {}).telegram = {});
