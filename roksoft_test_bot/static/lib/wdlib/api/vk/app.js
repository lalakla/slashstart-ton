wdlib.module("wdlib/api/vk/app.js", [
	"wdlib/api/vk/base.js",
	"wdlib/api/vk/bridge.js",
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.api.vk.App class

class App extends wdlib.api.vk.Bridge {

	constructor(args)
	{
		super(args);
		
		console.log("VK.APP : ", args);
	}

	init(callback, on_error, on_event = undefined)
	{
		if(this._init_state != wdlib.api.INIT_STATE_START) {
			// double call of api.init
			var error = new wdlib.model.Error({error: "API INIT ERROR : double call", error_code: wdlib.model.error.ERROR_API});
			error.file = "wdlib.api.vk.app.js";

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

		wdlib.load([{src: "vk.bridge", type: "js", crossOrigin: null}], function() {

			console.log("VK.BRIDGE loaded");

			if(self._init_state != wdlib.api.INIT_STATE_CALLED) {
				// double call of api.init
				var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.load_sdk", error_code: wdlib.model.error.ERROR_API});
				error.file = "wdlib.api.vk.app.js";

				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
				return;
			}
			self._init_state = wdlib.api.INIT_STATE_SDK_LOADED;

			try {
				// subscribe to api-events
				vkBridge.subscribe(function(e) {
//					if (e.detail.type === 'VKWebAppViewRestore') {
//						// Логика мини-приложения
//					}
					if(self.on_event_callback !== undefined) {
						self.on_event_callback.call(undefined, e);
					}
				});
				
				vkBridge.send('VKWebAppInit')
					.then(onWebAppInit)
					.catch(function(e) {
						console.error("wdlib.api.vk.app.js : VKWebAppInit : error recived : ", e);
	
						var error = new wdlib.model.Error({error: e.error_data.error_reason, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.app.js"});
						error.error += "; method=VKWebAppInit";
						if(self.on_error_callback) {
							self.on_error_callback.call(null, error);
						}	
					});

			}
			catch(e) {
				console.error("VK API INIT ERROR CATCH : ", e);
				
				var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.app.js"});
				error.error += "; method=INIT";
				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
			}

		});
	}

	allowNotificationsDialog(callback)
	{
		try {
			vkBridge.send("VKWebAppAllowNotifications")
				.then(function(data) {
//					console.log("wdlib.api.vk.app.js : VKWebAppAllowNotifications : ", data);

					if(data.result) {
						// allowed
						callback.call(undefined, true);
					}
					else {
						callback.call(undefined, false);
					}
				})
				.catch(function(e) {
					console.error("wdlib.api.vk.app.js : VKWebAppAllowNotifications : error recived : ", e);
					callback.call(undefined, false);
				});
		}
		catch(e) {
			this._ui_error(e, "allowNotificationsDialog");
		}
	}

}

exports.App = App;
// ============================================================================

}, (wdlib.api = wdlib.api || {}).vk = wdlib.api.vk || {});
