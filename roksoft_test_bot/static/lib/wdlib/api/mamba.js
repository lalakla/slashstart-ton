wdlib.module("wdlib/api/mamba.js", [
	"wdlib/api/base.js"
],
function(exports) {
"use strict";

var _users = new Map;

var MAMBA_JS_SDK = "https://js.aplatform.ru/v2.js";

var FRIEND_CALL_DELAY = 1000;
var USER_FIELDS = "location,flags,other";

// ============================================================================
// wdlib.api.mamba.Api class

class Api extends wdlib.api.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		super(args);
		
		console.log("wdlib.api.mamba.Api : ", args);

		this.viewer_id = args.oid || this.viewer_id;
		this.auth_key = args.auth_key || "";
		this.sid = args.sid || "";
		this.app_id = args.app_id | "";
		this.partner_url = args.partner_url || "https://www.mamba.ru";
		this.extra_param = args.extra || "";

		this.auth_params = {};
		for(var k in args) {
			this.auth_params[k] = args[k];
		}

		wdlib.module.config({
			map: {
				"mamba.api.js" : MAMBA_JS_SDK
			}
		});
	}

	appurl(extra)
	{
		var url = this.partner_url + "app_platform/?action=view&app_id=" + this.app_id;
		if(extra) {
			url += "&extra=" + extra;
		}
		return url;
	}
	/**
	 * @param Object user
	 * @param String extra [optional, default = undefined]
	 * @return String
	 */
	profileurl(user, extra)
	{
		var url = this.partner_url + "mb" + user.remote_id;
		if(user.anketa_link && user.anketa_link.length) {
			url = user.anketa_link;
			if(url.search(this.partner_url) == -1) {
				// need replace
				url = url.replace(/^http[s]{0,1}:\/\/[^\/\?]+/, "");
				if(url.charAt(0) == '/') {
					url = url.substr(1);
				}
				url = this.partner_url + url;
			}
		}
		if(extra) {
			if(url.indexOf('?') == -1) {
				url += "?extra=" + extra;
			}
			else {
				url += "&extra=" + extra;
			}
		}
		return url;
	}

	/**
	 * @param Function callback
	 */
	init(callback, on_error)
	{
		if(this._init_state != wdlib.api.INIT_STATE_START) {
			// double call of api.init
			var error = new wdlib.model.Error({error: "API INIT ERROR : double call", error_code: wdlib.model.error.ERROR_API});
			error.file = "wdlib.api.mamba.js";

			if(this.on_error_callback) {
				this.on_error_callback.call(null, error);
			}
			return;
		}

		super.init(callback, on_error);

		var self = this;

		this._init_state = wdlib.api.INIT_STATE_CALLED;

		wdlib.load([{src: "mamba.api.js", type: "js", crossOrigin: null}], function() {

			if(self._init_state != wdlib.api.INIT_STATE_CALLED) {
				// double call of api.init
				var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.load_sdk", error_code: wdlib.model.error.ERROR_API});
				error.file = "wdlib.api.mamba.js";

				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
				return;
			}
			self._init_state = wdlib.api.INIT_STATE_SDK_LOADED;

			try {
				mamba.init(function() {

					if(self._init_state != wdlib.api.INIT_STATE_SDK_LOADED) {
						// double call of api.init
						var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.init_sdk", error_code: wdlib.model.error.ERROR_API});
						error.file = "wdlib.api.mamba.js";

						if(self.on_error_callback) {
							self.on_error_callback.call(null, error);
						}
						return;
					}

					// init OK
					console.log("wdlib.api.mamba.Api: init OK");
					self._init_state = wdlib.api.INIT_STATE_INIT_PASSED;

					self.getProfile(self.viewer_id, function(data) {

						if(self._init_state != wdlib.api.INIT_STATE_INIT_PASSED) {
							// double call of api.init
							var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.getProfile", error_code: wdlib.model.error.ERROR_API});
							error.file = "wdlib.api.mamba.js";

							if(self.on_error_callback) {
								self.on_error_callback.call(null, error);
							}
							return;
						}

						self._init_state = wdlib.api.INIT_STATE_COMPLETE;
						callback.call(null, data, self.auth_params);
					});

				}, function() {
					// init ERROR
					console.log("wdlib.api.mamba.Api: init ERROR");

					var error = new wdlib.model.Error({error: "API INIT ERROR : mamba.init error called", error_code: wdlib.model.error.ERROR_API});
					error.file = "wdlib.api.mamba.js";

					if(self.on_error_callback) {
						self.on_error_callback.call(null, error);
					}
				});
			}
			catch(e) {
				console.log("MAMBA API INIT ERROR CATCH : ", e);
				
				var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.mamba.js"});
				error.error += "; method=INIT";
				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
			}
		});
	}

	_call(method, params, callback)
	{
		params.method = method;

		try {
			mamba.api(params, function(err, data) {
				// console.log("wdlib.api.mamba.Api::_call::" + method + ": ", err, data);
				callback.call(undefined, err, data);
			});
		}
		catch(e) {
			var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.mamba.js"});
			error.error += "; method=" + method;
			error.error += "; params=" + JSON.stringify(params);
			if(this.on_error_callback) {
				this.on_error_callback.call(null, error);
			}
		}
	}
	_ui_error(e, method)
	{
		var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.mamba.js"});
		error.error += "; method=" + method;
		if(this.on_error_callback) {
			this.on_error_callback.call(null, error);
		}
	}

	/**
	 * @param String user_id
	 * @param Function callback
	 */
	getProfile(user_id, callback)
	{
		var user = undefined;
		user_id = String(user_id);
		
//		console.log("wdlib.api.mamba.Api::getProfile : ", user_id);

		// check internal cache
		if((user = _users.get(user_id))) {
			callback.call(null, user);
			return;
		}
	
		user = {
			remote_id: user_id,
			platform: wdlib.Config.CLIENT_PLATFORM,
			name: "",
			sex: 0,
			pic: "",
			big_pic: "",
			age: 0,
			city: wdlib.api.DEFAULT_CITY,
			city_id: 0,
			anketa_link: "",
			has_mobile: 0
		};

		this._call("anketa.getInfo", {
			oids: user_id,
//			logins: "",
			blocks: USER_FIELDS
		}, function(err, data) {
//			console.log("wdlib.api.mamba.Api::getProfile result: ", err, data);

			if(!data || data.status != 0 || !data.data || !data.data.length) {
				console.log("wdlib.api.mamba.Api::getProfile ERROR : ", err, data);
			}
			else {
				data = data.data[0];
				
				user.name = data.info.name;
				user.sex = (data.info.gender == 'M') ? wdlib.api.MALE : wdlib.api.FEMALE;
				user.pic = data.info.medium_photo_url || user.pic;
				user.big_pic = data.info.square_photo_url || user.pic;
				user.age = data.info.age || user.age;
				user.city = (data.location ? (data.location.city) : undefined) || user.city;
				user.city_id = (data.location ? (data.location.city_id) : undefined) || user.city_id;
				user.anketa_link = data.info.anketa_link || user.anketa_link;

				//user.has_mobile = data.has_mobile || user.has_mobile;
				
				user.is_app_user = data.info.is_app_user || 0;
				user.is_online = data.flags.is_online || 0;

				_users.set(user.remote_id, user);
			}

			callback.call(null, user);
		});
	}

	/**
	 * @param String user_id
	 * @param Function callback
	 */
	getUserPhotos(user_id, callback)
	{
		var _photos = [];
		var _albums = [];

		var self = this;

		var _loadPhotos = function()
		{
			if(!_albums.length) {
				callback.call(null, user_id, _photos);
				return;
			}

			var album = _albums.shift();
			self._call("photos.get", {
				oid: user_id,
				album_id: album.album_id
			}, function(err, data) {
				console.log("wdlib.api.mamba.Api::photos.get result: ", err, data);
			
				if(!data || data.status != 0 || !data.data || !data.data.photos || !data.data.photos.length) {
					console.log("wdlib.api.mamba.Api::photos.get ERROR : ", err, data);
				}
				else {
					for(var i=0; i<data.data.photos.length; ++i) {
						_photos.push({
							small: data.data.photos[i]["small_photo_url"],
							medium: data.data.photos[i]["medium_photo_url"],
							big: data.data.photos[i]["huge_photo_url"]
						});
					}
				}

				_loadPhotos();
			});
		}

		// load albums first
		this._call("photos.getAlbums", {
			oid: user_id
		}, function(err, data) {
			console.log("wdlib.api.mamba.Api::photos.getAlbums result: ", err, data);

			if(!data || data.status != 0 || !data.data || !data.data.albums || !data.data.albums.length) {
				console.log("wdlib.api.mamba.Api::photos.getAlbums ERROR : ", err, data);
				callback.call(null, user_id, _photos);
				return;
			}

			_albums = data.data.albums.slice();

			_loadPhotos();
		});
	}

	/**
	 * @param int limit
	 * @param Function callback
	 */
	getFriends(limit, callback)
	{
		var self = this;
		var retval = [];

		var offset = 0;
		var online = true;

		// FUNCTIONS

		var _load = function()
		{
			var params = {
				blocks: USER_FIELDS,
//				offset: offset,
				limit: 100
			};

			if(online) {
				params.online = 1;
			}

			self._call("contacts.getContactList", params, _onFriendsLoaded);
		}

		var _onFriendsLoaded = function(err, data)
		{
			// console.log("wdlib.api.mamba.Api::_onFriendsLoaded result: ", err, data);

			data = (data && data.data && data.data.contacts) ? data.data.contacts : [];

			var count = data ? data.length : 0;
			// console.log("wdlib.api.mamba.Api::_onFriendsLoaded found: ", count, " friends, offset: ", offset, " online: ", online);

			for(var i=0; i<data.length; ++i) {
				var user_id = String(data[i].info["oid"]);

				// check internal cache
				if(_users.get(user_id)) {
					// already loaded
					continue;
				}

				var user = {
					remote_id: user_id,
					platform: wdlib.Config.CLIENT_PLATFORM,
					name: data[i].info["name"],
					sex: (data[i].info.gender == 'M') ? wdlib.api.MALE : wdlib.api.FEMALE,
					pic: data[i].info.medium_photo_url || "",
					big_pic: data[i].info.square_photo_url || "",
					age: data[i].info.age || 0,
					city: (data[i].location ? (data[i].location.city) : undefined) || wdlib.api.DEFAULT_CITY,
					city_id: (data[i].location ? (data[i].location.city_id) : undefined) || 0,
					anketa_link: data[i].info.anketa_link || (self.partner_url + "mb" + user_id),
					zodiac_name: "",
					is_app_user: data[i].info.is_app_user || 0,
					is_from_contact: 1,
					is_online: data[i].flags.is_online || 0
				};

				_users.set(user.remote_id, user);
				retval.push(user);
			}

			if(!online) {
				// all friends loaded
				//wdlib.utils.object.shuffle(retval);
				callback.call(undefined, retval);
			}
			else {
				online = false;
				setTimeout(_load, FRIEND_CALL_DELAY);
			}
		}

		// START HARE

		_load.call();
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
		// temporary switch payments off
//		callback.call(null, 0, wdlib.api.BILLING_FALSE);
//		return;

		var _result = function(e)
		{
			console.log("wdlib.api.mamba.Api::billingDialog : ", e);

			mamba.off("paymentCancel", _cancel);
			mamba.off("paymentSuccess", _complete);
			mamba.off("paymentFail", _fail);
			
			callback.call(null, amount, wdlib.api.BILLING_WAIT);
		}
		var _complete = function(e) {_result(e);}
		var _cancel = function(e) {_result(e);}
		var _fail = function(e) {_result(e);}

		mamba.on("paymentCancel", _cancel);
		mamba.on("paymentSuccess", _complete);
		mamba.on("paymentFail", _fail);
	
		try {
			mamba.method("pay", amount, extra);
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
		var _result = function(e)
		{
			console.log("wdlib.api.mamba.Api::MessageDialog : ", e);

			mamba.off("messageCancel", _cancel);
			mamba.off("messageComplete", _complete);

			var sended = undefined;

			if(e && e.data && e.data.data && e.data.data.sended) {
				sended = e.data.data.sended.split(',');
			}
			
			callback.call(undefined, sended);
		}

		var _complete = function(e) {_result(e);}
		var _cancel = function(e) {_result(e);}

		mamba.on("messageCancel", _cancel);
		mamba.on("messageComplete", _complete);
		
		try {
			mamba.method("message", mess, (extra && extra.extra) ? extra.extra : "", uids);
		}
		catch(e) {
			this._ui_error(e, "messageDialog");
		}
	}

	/**
	 * @param int width
	 * @param int height
	 */
	setAppSize(width, height)
	{
		try {
			mamba.method("resize", width, height);
		}
		catch(e) {
			this._ui_error(e, "setAppSize");
		}
	}

	/**
	 * @param Function callback
	 */
	isFavourites(callback)
	{
		this._call("anketa.inFavourites", {
		}, function(err, data) {
			//console.log("wdlib.api.mamba.Api::isFavourites result: ", err, data);
			
			var result = data && data.data && data.data.in_favourites;
			callback.call(undefined, result);
		});
	}

	wallPost(user_id, mess, data, callback)
	{
		this._call("achievement.set", {
			text: mess,
			extra_params: data.extra
		}, function(err, data) {
			console.log("wdlib.api.mamba.Api::wallPost result: ", err, data);
			
			//var result = data && data.data && data.data.in_favourites;
			//callback.call(undefined, result);
		});
	}
}

exports.Api = Api;
// ============================================================================

}, (wdlib.api = wdlib.api || {}).mamba = {});
