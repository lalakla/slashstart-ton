wdlib.module("wdlib/api/fs-mobile.js", [
	"md5",
	"wdlib/api/base.js",
	"wdlib/model/error.js",
	"wdlib/net/http.js",
	"wdlib/utils/utils.js"
],
function(exports) {
"use strict";

var _users = new Map;
		
var FS_SETTINGS_PHOTO = 0x80;
var FS_SETTINGS_NOTIFY = 0x20;
var FS_SETTINGS_COMMUNITIES = 0x4;

var FRIEND_CALL_DELAY = 1000;
var USER_FIELDS = "user_name,user_link,sex,birthday,photo_97,photo_big,city_name,city_id,is_online,installed_app";

var WIDGET_JS_SRC = "https://static5.lalakla.ru/libs/fs/widget/widget.js";
var FS_EVENT_URL = "https://m.fotostrana.ru/applications";

// ============================================================================
// wdlib.api.fs.ApiMobile class

class ApiMobile extends wdlib.api.Base {

	constructor(args)
	{
		super(args);
		
		console.log("FS MOBILE API : ", args);

		this.viewer_id = String(args.viewerId || this.viewer_id);
		this.auth_key = args.authKey || "";
		this.app_id = args.appId || "";
		this.app_settings = args.appSettings || 0;
		this.partner_url = "https://m.fotostrana.ru";
		this.extra_param = args.ref || args.optParams || "";
		this.api_url = args.apiUrl || "";
		this.api_proxy = args.apiProxy || this.api_url;
		this.client_key = args.clientKey || "";
		this.session_key = args.sessionKey || "";

		wdlib.module.config({
			map: {
				"fs.widget.js" : WIDGET_JS_SRC
			}
		});
	}

	appurl(extra)
	{
		var url = this.partner_url + "/app/" + this.app_id;
		if(extra) {
			url += "/?ref=" + extra;
		}
		return url;
	}

	init(callback, on_error)
	{
		if(this._init_state != wdlib.api.INIT_STATE_START) {
			// double call of api.init
			var error = new wdlib.model.Error({error: "API INIT ERROR : double call", error_code: wdlib.model.error.ERROR_API});
			error.file = "wdlib.api.fs-mobile.js";

			if(this.on_error_callback) {
				this.on_error_callback.call(null, error);
			}
			return;
		}

		super.init(callback, on_error);

		var self = this;
		this._init_state = wdlib.api.INIT_STATE_CALLED;

		wdlib.load([{src: "fs.widget.js", type: "js", crossOrigin: null}], function() {

			if(self._init_state != wdlib.api.INIT_STATE_CALLED) {
				// double call of api.init
				var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.load_sdk", error_code: wdlib.model.error.ERROR_API});
				error.file = "wdlib.api.fs-mobile.js";

				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
				return;
			}

			self._init_state = wdlib.api.INIT_STATE_SDK_LOADED;
//			console.log("FS WIDJET JS loaded here");

			try {
				widget.init("containerAPI");

				self._init_state = wdlib.api.INIT_STATE_INIT_PASSED;
				console.log("FS MOBILE API : init OK");

				self.getProfile(self.viewer_id, function(data) {
					if(self._init_state != wdlib.api.INIT_STATE_INIT_PASSED) {
						// double call of api.init
						var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.getProfile", error_code: wdlib.model.error.ERROR_API});
						error.file = "wdlib.api.fs-mobile.js";

						if(self.on_error_callback) {
							self.on_error_callback.call(null, error);
						}
						return;
					}

					self._init_state = wdlib.api.INIT_STATE_COMPLETE;
					callback.call(null, data, self.auth_key);
				});
			}
			catch(e) {
				console.log("FS MOBILE API INIT ERROR CATCH : ", e);
				
				var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.fs-mobile.js"});
				error.error += "; method=INIT";
				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
			}
		});
	}

	_sig(method, params, debug = undefined)
	{
		params.method = method;
		params.appId = this.app_id;
		params.sessionKey = this.session_key;
		params.viewerId = this.viewer_id;

		var list = [];
		for(var k in params) {
			if(params[k] === "" || params[k] === undefined || params[k] === null) {
				continue;
			}
			if(typeof params[k] === "object") {
				list.push({k:k, v:JSON.stringify(params[k])});
			}
			else {
				list.push({k:k, v:params[k]});
			}
		}

		list.push({k:"rand", v:Math.random()});
		list.push({k:"timestamp", v:wdlib.utils.intval(Date.now() / 1000)});
		list.push({k:"format", v:1});

		list.sort(function (a, b) {
			if(a.k > b.k) {
				return 1;
			}
			else if(a.k < b.k) {
				return -1;
			}
			else {
				return 0;
			}
		});

		params = {};

		var str = this.viewer_id;
		for(var i=0; i<list.length; ++i) {
			str += String(list[i].k) + '=' + String(list[i].v);
			params[list[i].k] = list[i].v;
		}
		str += this.client_key;
		var sigstr = str;
		
//		console.log("SIGSTR : ", sigstr);

		if(debug !== undefined) {
			debug.sigstr = sigstr;
		}

		params.sig = md5(sigstr);

		return params;
	}

	_call(method, params, callback)
	{
//		console.log("REQUST PARAMS: ", params);

		var self = this;

		params = this._sig(method, params);

		var _err = function(e)
		{
			console.error("wdlib.api.fs.ApiMobile : HTTP ERROR : ", e);
			
			var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.fs-mobile.js"});
			error.error += "; method=" + method;
			error.error += "; params=" + JSON.stringify(params);
			if(self.on_error_callback) {
				self.on_error_callback.call(null, error);
			}
		}

		try {
			let url = this.api_proxy + "?" + wdlib.net.Http.encode(params);
			fetch(url, {
//			fetch(this.api_proxy, {
//				method: "get",
//				method: "post",
//				headers: {
//					"Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
//				},
//				body: wdlib.net.Http.encode(params)
			}).then(function(response) {
				if(!response.ok) {
					throw new Error("error while requst");
				}
				return response.json();
			}).then(function(data) {
//				console.log("wdlib.api.fs.ApiMobile : _call : ", data);

				callback.call(undefined, data);
			}).catch(function(e) {
				_err.call(undefined, e);
			});
		}
		catch(e) {
			_err.call(undefined, e);
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
			birthday: "",
			city: wdlib.api.DEFAULT_CITY,
			city_id: 0,
			anketa_link: "",
			has_mobile: 0
		};

		this._call("User.getProfiles", {
			userIds: user_id,
			fields: USER_FIELDS
		}, function(data) {
//			console.log("wdlib.api.fs.ApiMobile::getProfile result: ", data);

			if(data.response && data.response[user_id]) {
				data = data.response[user_id];
				user.name = data.user_name;
				user.sex = (data.sex == 'm') ? wdlib.api.MALE : wdlib.api.FEMALE;
				user.pic = data.photo_97 || user.pic;
				user.big_pic = data.photo_big || user.big_pic;
				user.birthday = data.birthday || user.birthday;
				user.city = data.city_name || user.city;
				user.city_id = data.city_id || user.city_id;
				user.anketa_link = data.user_link || user.anketa_link;
				
				user.is_app_user = data.installed_app || 0;
				user.is_online = data.is_online || 0;

				wdlib.api.fs.setUserAge(user);

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
		var self = this;

		// need load albums first
		this._call("UsephotoExternal.albums", {
			userId: user_id
		}, function(data) {
//			console.log("wdlib.api.fs.ApiMobile::getUserPhotos : UsephotoExternal.albums : ", data);

			if(!data.response) {
				// some error
				callback.call(null, user_id, []);
				return;
			}

			var album_id = 0;
			var max_count = 0;

			for(var k in data.response) {
				if(data.response[k]["album_age18"]) {
					album_id = data.response[k]["album_id"];
					break;
				}
				if(max_count < data.response[k]["photos_count"]) {
					album_id = data.response[k]["album_id"];
					max_count = data.response[k]["photos_count"];
				}
			}

			// load photos from album
			self._call("UsephotoExternal.album", {
				albumId: album_id,
				userId: user_id,
				size: "big",
				limit: 50,
				page: 1
			}, function(data) {
//				console.log("wdlib.api.fs.ApiMobile::getUserPhotos : UsephotoExternal.album : ", data);
			
				if(!data.response) {
					// some error
					callback.call(null, user_id, []);
					return;
				}

				var photos = [];
				
				for(var k in data.response) {
					var photo = {
						small: data.response[k]["img_url"],
						medium: data.response[k]["img_url"],
						big: data.response[k]["img_url"]
					}
					photos.push(photo);
				}

				callback.call(null, user_id, photos);
			});
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

		var page = 1;
		var online = true;

		// FUNCTIONS

		var _load = function()
		{
			var params = {
				page: page,
				limit: 200
			};

			if(online) {
				params.only_online = 1;
			}

			self._call("User.getFriends", params, _onFriendsLoaded);
		}

		var _onFriendsLoaded = function(data)
		{
			// console.log("wdlib.api.fs.ApiMobile::_onFriendsLoaded result: ", data);
			
			data = data.response || [];
			
			var count = data ? data.length : 0;
			// console.log("wdlib.api.fs.ApiMobile::_onFriendsLoaded found: ", count, " friends, page: ", page, ", online: ", online);

			var foo = _load;

			if(count) {
				var user_ids = data;
				foo = function() {
					self._call("User.getProfiles", {
						userIds: user_ids.join(','),
						fields: USER_FIELDS
					}, function(data) {_onUsersLoaded.call(undefined, data, user_ids);});
				}
				page++;
			}
			else {
				if(online) {
					online = false;
					page = 1;
				}
				else {
					// all friends loaded
					//wdlib.utils.object.shuffle(retval);
					callback.call(undefined, retval);
					return;
				}
			}

			setTimeout(foo, FRIEND_CALL_DELAY);
		}

		var _onUsersLoaded = function(data, user_ids)
		{
			// console.log("wdlib.api.fs.ApiMobile::_onUsersLoaded result: ", data, user_ids);
			
			data = data.response || {};

			for(var i=0; i<user_ids.length; ++i) {
				var user_id = String(user_ids[i]);
				
				// check internal cache
				if(_users.get(user_id)) {
					// already loaded
					continue;
				}

				var res = data[user_id] || undefined;
				if(!res) {
					continue;
				}
				
				var user = {
					remote_id: user_id,
					platform: wdlib.Config.CLIENT_PLATFORM,
					name: res["user_name"],
					sex: (res["sex"] == 'm') ? wdlib.api.MALE : wdlib.api.FEMALE,
					pic: res["photo_97"] || "",
					big_pic: res["photo_big"] || "",
					birthday: res["birthday"] || "",
					city: res["city_name"] || wdlib.api.DEFAULT_CITY,
					city_id: res["city_id"] || 0,
					anketa_link: res["user_link"] || "",
					is_app_user: res["installed_app"] || 0,
					is_online: res["is_online"] || 0,
					is_from_contact: 1
				};
				
				wdlib.api.fs.setUserAge(user);

				_users.set(user.remote_id, user);
				retval.push(user);
			}

			_load.call();
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
		var debug = {};

		var params = this._sig("MobEvents.buyItem", {
			name: name,
			amount: amount,
			id: extra.code,
			pic_url: pic,
//			exchange: 1,
			"return": wdlib.Config.PROJECT_URL + "/index/fs/mob?fsmethod=billing&amount=" + String(amount)
//			forward_data: extra
		}, debug);

		var url = FS_EVENT_URL + "?" + wdlib.net.Http.encode(params);

		console.log("BILLING URL : ", url);

		// log to server
		// wdlib.net.SecureHttp.report("debug", "fs-billing", {sigstr: debug.sigstr, url: url});

		callback.call(null, amount, wdlib.api.BILLING_WAIT);

		window.location = url;
	}

	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	messageSend(mess, uids, callback, extra)
	{
		// this.messageDialog(mess, uids, callback, extra);

		var params = {
			userIds: uids.join(','),
			text: mess,
			optParams: (extra && extra.extra) ? extra.extra : ""
		};

		if(extra.subType) {
			params.subType = extra.subType;
		}
		if(extra.title) {
			params.title = extra.title;
		}
		
		this._call("User.sendCustomMessage", params, function(data) {
			console.log("wdlib.api.fs.ApiMobile::messageend : ", data);

			var sended = undefined;

			if(data.response && data.response.sent) {
				sended = data.response.sent.split(',');
			}

			callback.call(undefined, sended);
		});
	}

	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	inviteDialog(mess, uids, callback, extra)
	{
		var params = this._sig("MobEvents.invite", {
			userId: uids.join(','),
			text: mess,
			params: (extra && extra.extra) ? extra.extra : "",
			"return": wdlib.Config.PROJECT_URL + "/index/fs/mob"
		});

		var url = FS_EVENT_URL + "?" + wdlib.net.Http.encode(params);

		console.log("INVITE URL : ", url);

		callback.call(undefined, undefined);

		window.location = url;
	}

	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	messageDialog(mess, uids, callback, extra)
	{
		/*
		this.FS.event("sendMessageAndInvite", function(data) {
			console.log("wdlib.api.fs.Api::messageDialog : ", data);
			
			var sended = undefined;

			if(data.invIds || data.msgIds) {
				sended = [];
				if(data.invIds) sended = sended.concat(data.invIds.split(','));
				if(data.msgIds) sended = sended.concat(data.msgIds.split(','));
			}

			callback.call(undefined, sended);
		}, {
			message: mess,
			customIds: uids.join(','),
			params: (extra && extra.extra) ? extra.extra : ""
		});
		*/
	}

	/**
	 * @param int width
	 * @param int height
	 */
	setAppSize(width, height)
	{
		/*
		this.FS.event("resize", function(data) {
			console.log("wdlib.api.fs.Api::resize : ", data);
		}, {width: width, height: height});
		*/
	}

	/**
	 * @param Function callback
	 */
	isFavourites(callback)
	{
		/*
		this.FS.event("isAppInFav", function(data) {
			// console.log("wdlib.api.fs.Api::isFavourites : ", data);
			callback.call(undefined, (data && data.isAppInFav) ? 1 : 0);
		}, {});
		*/
	}
	/**
	 * @param Function callback
	 */
	favouritesDialog(callback)
	{
		/*
		this.FS.event("addAppToFav", function(data) {
			console.log("wdlib.api.fs.Api::favouritesDialog : ", data);
			
			callback.call(undefined, (data && data.status) ? 1 : 0);
		}, {});
		*/
	}
}

exports.ApiMobile = ApiMobile;
// ============================================================================

// ============================================================================
// STATIC HELPER FUNCTIONS

exports.setUserAge = function(user)
{
	if(!user.birthday) {
		// can't determine
		return;
	}

	var day = 0;
	var mon = 0;
	var year = 0;

	var prev_idx = 0;
	var idx = 0;
	
	// get year
	idx = user.birthday.indexOf("-");
	year = wdlib.utils.intval(user.birthday.slice(prev_idx, idx));

	// get month
	if(idx != -1) {
		prev_idx = idx + 1;
		idx = user.birthday.indexOf("-", prev_idx);
		mon = wdlib.utils.intval(user.birthday.slice(prev_idx, idx));
	}

	// get day
	if(idx != -1) {
		prev_idx = idx + 1;
		day = wdlib.utils.intval(user.birthday.slice(prev_idx));
	}

	user.birthday = (day < 10 ? "0" : "") + String(day) + "." + (mon < 10 ? "0" : "") + String(mon) + "." + String(year);

	if(day && mon && year) {
		user.bdate = new Date(year, mon, day);
		var now = new Date;

		user.age = (now.getFullYear() - user.bdate.getFullYear());
		if(now.getMonth() < user.bdate.getMonth()) {
			user.age--;
		}
		if(now.getMonth() == user.bdate.getMonth() && now.getDate() < user.bdate.getDate()) {
			user.age--;
		}
	}
}
// ============================================================================

function _onError(sa)
{
	console.log("FS MOBILE API error: ", sa);
}

}, (wdlib.api = wdlib.api || {}).fs = wdlib.api.fs || {});
