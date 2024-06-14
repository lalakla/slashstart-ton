wdlib.module("wdlib/api/vk/bridge.js", [
	"wdlib/api/vk/base.js",
	"wdlib/net/http.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/date.js"
],
function(exports) {
"use strict";

var _request_id = 1;

// ============================================================================
// wdlib.api.vk.Bridge class

class Bridge extends wdlib.api.vk.Base {

	constructor(args)
	{
		super(args);
		
		console.log("VK.BRIDGE : ", args);

		// log to server
		// wdlib.net.Http.report("/debug/trace", {args: args});

		this.access_token = args.access_token || "";
	}

	init(callback, on_error, on_event = undefined)
	{
		if(this._init_state != wdlib.api.INIT_STATE_START) {
			// double call of api.init
			var error = new wdlib.model.Error({error: "API INIT ERROR : double call", error_code: wdlib.model.error.ERROR_API});
			error.file = "wdlib.api.vk.bridge.js";

			if(this.on_error_callback) {
				this.on_error_callback.call(null, error);
			}
			return;
		}

		this.on_error_callback = on_error;
		this.on_event_callback = on_event;
		
		var self = this;
		this._init_state = wdlib.api.INIT_STATE_CALLED;

		var onAccessToken = function()
		{
			self.getProfile(self.viewer_id, function(data) {
				if(self._init_state != wdlib.api.INIT_STATE_INIT_PASSED) {
					// double call of api.init
					var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.getProfile", error_code: wdlib.model.error.ERROR_API});
					error.file = "wdlib.api.vk.bridge.js";

					if(self.on_error_callback) {
						self.on_error_callback.call(null, error);
					}
					return;
				}

				
				self._init_state = wdlib.api.INIT_STATE_COMPLETE;
				callback.call(null, data, self.auth_key);
			});
		}

		var onWebAppInit = function()
		{
			// API INIT OK
			console.log("VK.BRIDGE API: init ok");
			self._init_state = wdlib.api.INIT_STATE_INIT_PASSED;

//			self.access_token = "";

			if(!self.access_token) {
				// нужно сначала получить собственно access_token
				self.updateAccessToken(onAccessToken);
			}
			else {
				onAccessToken();
			}
		}

		wdlib.load([{src: "vk.bridge", type: "js", crossOrigin: null}], function() {

			console.log("VK.BRIDGE loaded");

			if(self._init_state != wdlib.api.INIT_STATE_CALLED) {
				// double call of api.init
				var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.load_sdk", error_code: wdlib.model.error.ERROR_API});
				error.file = "wdlib.api.vk.bridge.js";

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
						console.error("wdlib.api.vk.bridge.js : VKWebAppInit : error recived : ", e);
	
						var error = new wdlib.model.Error({error: e.error_data.error_reason, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.bridge.js"});
						error.error += "; method=VKWebAppInit";
						if(self.on_error_callback) {
							self.on_error_callback.call(null, error);
						}	
					});

			}
			catch(e) {
				console.error("VK API INIT ERROR CATCH : ", e);
				
				var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.bridge.js"});
				error.error += "; method=INIT";
				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
			}

		});
	}

	updateAccessToken(callback, scope = undefined, on_error_callback = undefined)
	{
		var self = this;

		scope = scope ? scope : "";

		var params = {
			app_id: wdlib.utils.intval(this.app_id),
			scope: scope
		};

		vkBridge.send("VKWebAppGetAuthToken", params)
			.then(function(data) {
				if(data.access_token) {
					self.access_token = data.access_token;
				}
				callback.call();
			})
			.catch(function(e) {
				console.error("wdlib.api.vk.bridge.js : VKWebAppGetAuthToken : params : ", params);
				console.error("wdlib.api.vk.bridge.js : VKWebAppGetAuthToken : error : ", e);

				var error = new wdlib.model.Error({error: e.error_data.error_reason, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.bridge.js"});
				error.error += "; method=VKWebAppGetAuthToken";
				error.data.error_reason = e.error_data.error_reason;

				if(on_error_callback !== undefined) {
					on_error_callback.call(undefined, error);
				}
				else if(self.on_error_callback !== undefined) {
					self.on_error_callback.call(null, error);
				}
			});
	}


	_send(method, params, callback, on_error_callback = undefined)
	{
		// console.log("wdlib.api.vk.bridge.js : _send : ", method, params);

		var self = this;

		try {
			vkBridge.send(method, params)
				.then(function(data) {
					// console.log("wdlib.api.vk.bridge.js : _send : " + method + " : ", data);
					callback.call(undefined, data);
				}).catch(function(e) {
					console.error("wdlib.api.vk.bridge.js : _send : " + method + " : error recived : ", e);
	
					var error = new wdlib.model.Error({
						error: JSON.stringify({
							type: e.error_type,
							code: e.error_data.error_code,
							reason: e.error_data.error_reason
						}),
						error_code: wdlib.model.error.ERROR_API,
						// stack: e.stack,
						file: e.fileName || "wdlib.api.vk.bridge.js"
					});
					
					error.error += "; method=" + method;
					error.error += "; params=" + JSON.stringify(params);
					error.data.error_reason = e.error_data.error_reason;

					if(on_error_callback !== undefined) {
						on_error_callback.call(undefined, error);
					}
					else if(self.on_error_callback !== undefined) {
						self.on_error_callback.call(null, error);
					}
				});
		}
		catch(e) {
			console.error("wdlib.api.vk.bridge.js : _send : error catch : ", e);

			var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.bridge.js"});
			error.error += "; method=" + method;
			error.error += "; params=" + JSON.stringify(params);

			if(on_error_callback !== undefined) {
				on_error_callback.call(undefined, error);
			}
			else if(this.on_error_callback !== undefined) {
				this.on_error_callback.call(null, error);
			}
		}
	}
	_call(method, params, callback, on_error_callback = undefined)
	{
		// console.log("wdlib.api.vk.bridge.js : _call : ", method, params);

		var self = this;

		// api version
		params.v = params.v || wdlib.api.vk.API_VERSION;
		// access_token
		params.access_token = this.access_token;

		try {
			var req_id = this.viewer_id + "_" + String(_request_id);
			++_request_id;
			
			vkBridge.send("VKWebAppCallAPIMethod", {
				"method" : method,
				"request_id" : req_id,
				params : params
			}).then(function(data) {
				// console.log("wdlib.api.vk.bridge.js : _call : " + method + " : ", data);
				callback.call(undefined, data);
			}).catch(function(e) {
				console.error("wdlib.api.vk.bridge.js : _call : " + method + " : error recived : ", e);
	
				var error = new wdlib.model.Error({
					error: JSON.stringify({
						type: e.error_type,
						code: e.error_data.error_code,
						reason: e.error_data.error_reason
					}),
					error_code: wdlib.model.error.ERROR_API,
					// stack: e.stack,
					file: e.fileName || "wdlib.api.vk.bridge.js"
				});
				error.error += "; method=" + method;
				error.error += "; params=" + JSON.stringify(params);
				error.data.error_reason = e.error_data.error_reason;

				if(on_error_callback !== undefined) {
					on_error_callback.call(undefined, error);
				}
				else if(self.on_error_callback !== undefined) {
					self.on_error_callback.call(null, error);
				}
			});
		}
		catch(e) {
			console.error("wdlib.api.vk.bridge.js : _call : error catch : ", e);

			var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.bridge.js"});
			error.error += "; method=" + method;
			error.error += "; params=" + JSON.stringify(params);

			if(on_error_callback !== undefined) {
				on_error_callback.call(undefined, error);
			}
			else if(this.on_error_callback !== undefined) {
				this.on_error_callback.call(null, error);
			}
		}
	}
	_ui_error(e, method)
	{
		var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.bridge.js"});
		error.error += "; method=" + method;
		if(this.on_error_callback) {
			this.on_error_callback.call(null, error);
		}
	}

	/**
	 * @param int width
	 * @param int height
	 *
	 * похоже бага в этом месте у ВК для полноэкранных приложений!
	 * ставится ширина не больше 1000!
	 * а вот если не делать ничего - то 100%
	 * 
	 * для height диапазон значений от 500 до 4050
	 */
	setAppSize(width, height, callback = undefined)
	{
		var params = {
			height: height
		};

		if(width) {
			params.width = width;
		}

		try {
			vkBridge.send("VKWebAppResizeWindow", params)
			.then(function(data) {
				if(callback !== undefined) {
					callback.call(undefined, true, width, height);
				}
			}).catch(function(e) {
				if(callback !== undefined) {
					callback.call(undefined, false, width, height);
				}
			});
		}
		catch(e) {
			this._ui_error(e, "setAppSize");
		}
	}

	/**
	 * @param String user_id
	 * @param String mess
	 * @param Object data
	 * @param Function callback
	 */
	wallPost(user_id, mess, data, callback)
	{
		var params = {
//			owner_id: wdlib.utils.intval(user_id),
			message: mess
		};

		var attach = "";
		if(data.image) {
			attach += (attach.length ? "," : "") + "photo" + String(data.image.owner_id) + "_" + String(data.image.id);
		}
		if(data.url) {
			attach += (attach.length ? "," : "") + data.url;
		}

		// запись только для друзей
		if(data.friends_only) {
			params.friends_only = 1;
		}

		params.attachments = attach;

		console.log("wdlib.api.vk.Bridge::wall.post params: ", params);

		this._send("VKWebAppShowWallPostBox", params, function(data) {
			console.log("wdlib.api.vk.Bridge::wall.post result: ", data);
			
			var post_id = 0;
			if(data.post_id) {
				post_id = data.post_id;
			}

			callback.call(undefined, post_id);
		}, function(error) {
			console.error("wdlib.api.vk.Bridge::wall.post error: ", error.error);
			callback.call(undefined, 0, error);
		});
	}

	/**
	 * @param Function callback
	 */
	favouritesDialog(callback)
	{
		try {
			vkBridge.send("VKWebAppAddToFavorites")
				.then(function(data) {
					console.log("wdlib.api.vk.Bridge::favouritesDialog result: ", data);

					var r = data.result ? 1 : 0;
					callback.call(undefined, r);
				}).catch(function(e) {
					console.error("wdlib.api.vk.Bridge::favouritesDialog error: ", e);
				});
		}
		catch(e) {
			this._ui_error(e, "favouritesDialog");
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

		try {
			vkBridge.send("VKWebAppShowRequestBox", {
				"uid" : user_id,
				"message" : mess,
				"requestKey" : (extra && extra.extra) ? extra.extra : ""
			}).then(function(data) {
				callback.call(undefined, data.success ? [user_id] : undefined);
			}).catch(function(e) {
				callback.call(undefined, undefined);
			});
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
	inviteDialog(mess = undefined, uids = undefined, callback = undefined, extra = undefined)
	{
		if(uids && uids.length == 1) {
			this.messageDialog(mess, uids, callback, extra);
		}
		else {
			try {
				vkBridge.send("VKWebAppShowInviteBox");
			}
			catch(e) {
				this._ui_error(e, "inviteDialog");
			}

			// call immediatly, cause VK doesn't notify about invites sended ...
			if(callback !== undefined) {
				callback.call(undefined, undefined);
			}
		}
	}

	billingDialog(gold, amount, callback, name, desc, pic, extra)
	{
		try {
			vkBridge.send("VKWebAppShowOrderBox", {
				"type" : "item",
				"item" : (amount) ? String(amount) : extra
			}).then(function(data) {
				console.log("wdlib.api.vk.Bridge::billingDialog result: ", data);
				callback.call(undefined, amount, data.success ? wdlib.api.BILLING_TRUE : wdlib.api.BILLING_FALSE);
			}).catch(function(e) {
				// log to server
				// wdlib.net.Http.report("/debug/trace", {e: e});

				callback.call(undefined, amount, wdlib.api.BILLING_FALSE);
			});
		}
		catch(e) {
			this._ui_error(e, "billingDialog");
		}
	}

	closeApp()
	{
		try {
			vkBridge.send("VKWebAppClose", {
				status: "success"
			})
				.then(function(data) {
					console.log("wdlib.api.vk.bridge.js : VKWebAppClose : ", data);
				})
				.catch(function(e) {
					console.error("wdlib.api.vk.bridge.js : VKWebAppClose : error recived : ", e);
				});
		}
		catch(e) {
			this._ui_error(e, "closeApp");
		}
	}

	allowNotificationsDialog(callback)
	{
		console.error("wdlib.api.vk.Bridge::allowNotificationsDialog : APP REQUIRED");
	}

	scrollApp(top, speed = undefined)
	{
		
		speed = (speed === undefined) ? 500 : speed;

		try {
			vkBridge.send("VKWebAppScroll", {
				top: top,
				speed: speed
			})
				.then(function(data) {
					console.log("wdlib.api.vk.app.js : VKWebAppScroll : ", data);
				})
				.catch(function(e) {
					console.error("wdlib.api.vk.app.js : VKWebAppScroll : error recived : ", e);
				});
		}
		catch(e) {
			this._ui_error(e, "scrollApp");
		}
	}

	vkPayToUserDialog(amount, to_user_id, desc, callback)
	{
		try {
			vkBridge.send("VKWebAppOpenPayForm", {
				app_id: wdlib.utils.intval(this.app_id),
				action: "pay-to-user",
				params: {
					amount: wdlib.utils.intval(amount),
					user_id: to_user_id,
					description: desc
				}
			}).then(function(data) {
				console.log("wdlib.api.vk.Bridge::vkPayToUserDialog result: ", data);
				callback.call(undefined, amount, data.status ? wdlib.api.BILLING_TRUE : wdlib.api.BILLING_FALSE, data);
			}).catch(function(e) {
				// log to server
				// wdlib.net.Http.report("/debug/trace", {e: e});
				console.log("wdlib.api.vk.Bridge::vkPayToUserDialog error: ", e);

				callback.call(undefined, amount, wdlib.api.BILLING_FALSE);
			});
		}
		catch(e) {
			this._ui_error(e, "vkPayToUserDialog");
		}
	}
	vkPayToGroupDialog(amount, to_group_id, desc, callback)
	{
		try {
			vkBridge.send("VKWebAppOpenPayForm", {
				app_id: wdlib.utils.intval(this.app_id),
				action: "pay-to-group",
				params: {
					amount: wdlib.utils.intval(amount),
					group_id: to_group_id,
					description: desc
				}
			}).then(function(data) {
				console.log("wdlib.api.vk.Bridge::vkPayToGroupDialog result: ", data);
				callback.call(undefined, amount, data.status ? wdlib.api.BILLING_TRUE : wdlib.api.BILLING_FALSE, data);
			}).catch(function(e) {
				// log to server
				// wdlib.net.Http.report("/debug/trace", {e: e});
				console.log("wdlib.api.vk.Bridge::vkPayToGroupDialog error: ", e);

				callback.call(undefined, amount, wdlib.api.BILLING_FALSE);
			});
		}
		catch(e) {
			this._ui_error(e, "vkPayToGroupDialog");
		}
	}

}

exports.Bridge = Bridge;
// ============================================================================

}, (wdlib.api = wdlib.api || {}).vk = wdlib.api.vk || {});
