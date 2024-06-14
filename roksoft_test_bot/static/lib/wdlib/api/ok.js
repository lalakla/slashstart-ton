wdlib.module("wdlib/api/ok.js", [
	"wdlib/api/base.js",
	"wdlib/model/error.js"
],
function(exports) {
"use strict";

var _users = new Map;
var _installed = undefined;

var FRIEND_CALL_DELAY = 1000;
var USER_FIELDS = "first_name,age,url_profile,gender,birthday,pic180min,pic320min,location,online,has_phone,has_email";

var PERMISSION_PHOTO = "PHOTO_CONTENT";
var PERMISSION_PUBLISH = "PUBLISH_TO_STREAM";
var NEED_PERMISSIONS = [PERMISSION_PHOTO, PERMISSION_PUBLISH];

// ============================================================================
// wdlib.api.ok.Api class

class Api extends wdlib.api.Base {

	constructor(args)
	{
		super(args);
		
		console.log("wdlib.api.ok.Api : ", args);

		// log to server
		// wdlib.net.Http.report("/debug/trace", {args: args});

		this.viewer_id = args.logged_user_id || this.viewer_id;
		this.partner_url = "http://ok.ru";
		
		this.auth_params = {
			"session_key": args.session_key || "",
			"auth_sig": args.auth_sig || ""
		};

		this.api_server = args.api_server || "https://api.ok.ru";
		this.apiconnection = args.apiconnection || "";

		this.app_id = "";
		var r = this.apiconnection.match(/^([0-9]+)/);
		if(r) {
			this.app_id = r[1];
		}
		
		this.extra_param = args.custom_args || "";
		if(this.extra_param) {
			this.extra_param = decodeURIComponent(this.extra_param);
			this.extra_param = this.extra_param.replace(/^ref=/, "");
		}

		this.permissions = new Map;
		this.permissions_callback = undefined;

		this.wallpost_callback = undefined;

		wdlib.module.config({
			map: {
				"ok.api.js" : this.api_server + "/js/fapi5.js"
			}
		});

		var self = this;

		// GLOBAL OK_API Event Callback function
		// fucking hack for OK :-(
		window.API_callback = function(method, status, data)
		{
			console.log("wdlib.api.ok.GlobalEventHandler : ", method, status, data);

			switch(method) {
				case "showPayment":
					self.onBillingResult(status, data);
					break;
				case "showInvite":
					self.onInviteResult(status, data);
					break;
				case "showNotification":
					self.onMessageResult(status, data);
					break;
				case "showPermissions":
					self.onShowPermissionResult(status, data);
					break;
				case "postMediatopic":
					self.onWallPostResult(status, data);
					break;
			}
		}
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
			error.file = "wdlib.api.ok.js";

			if(this.on_error_callback) {
//				this.on_error_callback.call(null, error);
			}
			return;
		}

		super.init(callback, on_error);

		var self = this;
		this._init_state = wdlib.api.INIT_STATE_CALLED;

		wdlib.load([{src: "ok.api.js", type: "js", crossOrigin: null}], function() {
			
			if(self._init_state != wdlib.api.INIT_STATE_CALLED) {
				// double call of api.init
				var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.load_sdk", error_code: wdlib.model.error.ERROR_API});
				error.file = "wdlib.api.ok.js";

				if(self.on_error_callback) {
//					self.on_error_callback.call(null, error);
				}
				return;
			}
			self._init_state = wdlib.api.INIT_STATE_SDK_LOADED;

			try {
				FAPI.init(self.api_server, self.apiconnection, function() {

					if(self._init_state != wdlib.api.INIT_STATE_SDK_LOADED) {
						// double call of api.init
						var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.init_sdk", error_code: wdlib.model.error.ERROR_API});
						error.file = "wdlib.api.ok.js";

						if(self.on_error_callback) {
//							self.on_error_callback.call(null, error);
						}
						return;
					}

					// init OK
					console.log("wdlib.api.ok.Api: init OK");
					self._init_state = wdlib.api.INIT_STATE_INIT_PASSED;
				
					self.getProfile(self.viewer_id, function(data) {

						if(self._init_state != wdlib.api.INIT_STATE_INIT_PASSED) {
							// double call of api.init
							var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.getProfile", error_code: wdlib.model.error.ERROR_API});
							error.file = "wdlib.api.ok.js";

							if(self.on_error_callback) {
//								self.on_error_callback.call(null, error);
							}
							return;
						}

						self._init_state = wdlib.api.INIT_STATE_COMPLETE;
						callback.call(null, data, self.auth_params);
					});

					// check permissions

					var i = 0;
					var check = function()
					{
						if(i >= NEED_PERMISSIONS.length) return;

						var perm = NEED_PERMISSIONS[i];
						self._call("users.hasAppPermission", {
							ext_perm: perm
						}, function(status, result, err) {
							console.log("wdlib.api.ok.Api::users.hasAppPermission '" + perm + "' result: ", status, result, err);
	
							if(result) {
								// permissions OK
								self.permissions.set(perm, 1);
							}
	
							++i;
							check.call(undefined);
						});
					}
					check.call(undefined);
				}, function(err) {
					// init ERROR
					console.log("wdlib.api.ok.Api: init ERROR : ", err);
	
					// some error occured
					var error = new wdlib.model.Error({error: "API INIT ERROR : FAPI.init error called", error_code: wdlib.model.error.ERROR_API});
					error.file = "wdlib.api.ok.js";
					error.error += "; err=" + err.toString();
	
					if(self.on_error_callback) {
						self.on_error_callback.call(null, error);
					}
				});
			}
			catch(e) {
				console.log("OK API INIT ERROR CATCH : ", e);
				
				var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.ok.js"});
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
			FAPI.Client.call(params, function(status, data, err) {
				callback.call(undefined, status, data, err);
			});
		}
		catch(e) {
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
			city: wdlib.api.DEFAULT_CITY,
			city_id: 0,
			anketa_link: "",
			has_mobile: 0,
			has_email: 0
		};

		this._call("users.getInfo", {
			uids: user_id,
			fields: USER_FIELDS
		}, function(status, data, err) {
			//console.log("wdlib.api.ok.Api::getProfile result: ", status, data, err);

			if(status != "ok" || err || !data || !data.length) {
				console.log("wdlib.api.ok.Api::getProfile ERROR : ", status, data, err);
			}
			else {
				data = data[0];
				
				user.name = data.first_name || user.name;
				user.sex = (data.gender == 'male') ? wdlib.api.MALE : wdlib.api.FEMALE;
				user.pic = data.pic180min || user.pic;
				user.big_pic = data.pic320min || user.pic;
				user.age = data.age || user.age;
				user.birthday = data.birthday || user.birthday;
				user.city = (data.location ? (data.location.city) : undefined) || user.city;
				user.anketa_link = data.url_profile || user.anketa_link;

				user.has_mobile = data.has_phone || user.has_mobile;
				user.has_email = data.has_email || user.has_email;
				
				user.is_online = (data.online && data.online == 'web') || 0;
				
				wdlib.api.ok.setUserAge(user);

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
		this._call("photos.getPhotos", {
			fid: user_id,
			fields: "photo.pic50x50,photo.pic320min,photo.pic1024max,photo.pic_max,photo.mark_avg,photo.mark_count,photo.like_summary",
			count: 100
		}, function(status, data, err) {
			//console.log("wdlib.api.ok.Api::getUserPhotos result: ", status, data, err);
			
			var photos = [];

			if(status != "ok" || err || !data || !data.photos || !data.photos.length) {
				console.log("wdlib.api.ok.Api::getUserPhotos ERROR : ", status, data, err);
			}
			else {
				for(var i=0; i<data.photos.length; ++i) {
					var photo = {
						small: data.photos[i]["pic50x50"],
						medium: data.photos[i]["pic320min"],
						big: data.photos[i]["pic320min"],
						mark: parseFloat(data.photos[i].mark_avg.replace(/[^0-9\.]+/, "")),
						mark_count: parseInt(data.photos[i].mark_count),
						likes: parseInt((data.photos[i]["like_summary"]) ? (data.photos[i]["like_summary"]["count"]) : 0)
					};

					photo.big = data.photos[i]["pic1024max"] || photo.big;
					photo.big = data.photos[i]["pic_max"] || photo.big;

					photos.push(photo);
				}
				
				photos.sort(_photosSort);
			}

			callback.call(null, user_id, photos);
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

		var online = true;

		// FUNCTIONS

		var _load = function()
		{
			// load online friends
			self._call(online ? "friends.getOnline" : "friends.get", {
			}, _onFriendsLoaded);
		}

		var _loadUids = function(uids)
		{
			if(!uids.length) {
				if(online) {
					// call _load without online flag
					online = false;
					_load.call();
				}
				else {
					// all friends loaded
					//wdlib.utils.object.shuffle(retval);
					callback.call(undefined, retval);
				}
				return;
			}

			var _uids = uids.splice(0, 100);
			self._call("users.getInfo", {
				uids: _uids.join(','),
				fields: USER_FIELDS
			}, function(status, data, err) {
				/*var cnt = */_initFriends.call(undefined, status, data, err);
				
				setTimeout(function() {
					_loadUids.call(undefined, uids);
				}, FRIEND_CALL_DELAY);
			});
		}

		var _initFriends = function(status, data, err)
		{
			console.log("wdlib.api.ok.Api::users.getInfo result: ", status, data, err);

			data = data || [];

			var count = data ? data.length : 0;
			console.log("wdlib.api.ok.Api::users.getInfo found: ", count);

			if(count) {
				for(var i=0; i<data.length; ++i) {
					var res = data[i];
					if(!res) {
						continue;
					}
					
					var user_id = String(res["uid"]);

					// check internal cache
					if(_users.get(user_id)) {
						// already loaded
						continue;
					}
				
					var user = {
						remote_id: user_id,
						platform: wdlib.Config.CLIENT_PLATFORM,
						name: res["first_name"],
						sex: (res["gender"] == "male") ? wdlib.api.MALE : wdlib.api.FEMALE,
						pic: res["pic180min"],
						big_pic: res["pic320min"],
						age: 0,
						birthday: res["birthday"],
						city: (res["location"] ? (res["location"].city) : undefined) || wdlib.api.DEFAULT_CITY,
						zodiac_name: "",
						is_app_user: (_installed.has(user_id) ? 1 : 0),
						anketa_link: res["url_profile"],
						is_from_contact: 1,
						is_online: (res["online"] && res["online"] == "web") ? 1 : 0,
						has_mobile: res.has_phone || 0,
						has_email: res.has_email || 0
					};
				
					wdlib.api.ok.setUserAge(user);

					_users.set(user.remote_id, user);
					retval.push(user);
				}
			}

			return count;
		}
	
		var _onFriendsLoaded = function(status, data, err)
		{
			console.log("wdlib.api.ok.Api::friends.get" + (online ? "Online" : "") + " result: ", status, data, err);

			data = data || [];

			var count = data ? data.length : 0;
			console.log("wdlib.api.ok.Api::friends.get" + (online ? "Online" : "") + " found: ", count, " online: ", online);

			online = false;

			setTimeout(function() {
				_loadUids.call(undefined, data);
			}, FRIEND_CALL_DELAY);
		}

		var _onInstalledLoaded = function(status, data, err)
		{
			console.log("wdlib.api.ok.Api::friends.getAppUsers result: ", status, data, err);

			data = data && data.uids || [];

			var count = data ? data.length : 0;
			console.log("wdlib.api.ok.Api::friends.getAppUsers found: ", count, " friends already installed app");
			
			var foo = _load;

			if(count) {
				for(var i=0; i<data.length; ++i) {
					_installed.add(String(data[i]));
				}

				foo = function() {
					_loadUids(data);
				}
			}

			setTimeout(foo, FRIEND_CALL_DELAY);
		}

		// START HARE

		if(!_installed) {
			// load installed app friend ids first
			_installed = new Set;

			self._call("friends.getAppUsers", {
			}, _onInstalledLoaded);
		}
		else {
			_load.call();
		}
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
		this.billing_callback = callback;

		var code = gold;
		var options = "";

//		console.log("EXTRA: ", extra, typeof extra );

		if(!(typeof extra == "string")) {
			code = extra.code || code;
			options = extra.extra;
		}
		else {
			options = extra;
		}

//		console.log("OPTIONS: ", options);

		try {
			FAPI.UI.showPayment(name, desc, code, amount, null, options, "ok", "true");
		}
		catch(e) {
			this._ui_error(e, "billingDialog");
		}
	}
	onBillingResult(status, data)
	{
		console.log("wdlib.api.ok.Api::onBillingResult : ", status, data);
		
		if(status == 'ok') {
			try {
				var dd = JSON.parse(String(data));
				this.billing_callback.call(null, parseInt(dd.amount), wdlib.api.BILLING_TRUE);
			}
			catch(err) {
				console.log("wdlib.api.ok.Api::onBillingResult ERROR : ", status, data, err);
				this.billing_callback.call(null, 0, wdlib.api.BILLING_FALSE);
			}
		}
		else {
			console.log("wdlib.api.ok.Api::onBillingResult ERROR : ", status, data);
			this.billing_callback.call(null, 0, wdlib.api.BILLING_FALSE);
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
		this.message_callback = callback;

		try {
			FAPI.UI.showNotification(mess, (extra && extra.extra) ? extra.extra : "", uids.join(';'));
		}
		catch(e) {
			this._ui_error(e, "messageDialog");
		}
	}
	onMessageResult(status, data)
	{
		console.log("wdlib.api.ok.Api::onMessageResult : ", status, data);

		var sended = undefined;

		if(status == "ok") {
			data = data || "";
			sended = data.split(',');
		}

		if(this.message_callback) {
			this.message_callback.call(undefined, sended);
		}
		this.message_callback = undefined;
	}

	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	inviteDialog(mess, uids, callback, extra)
	{
		this.invite_callback = callback;

		try {
			FAPI.UI.showInvite(mess, (extra && extra.extra) ? extra.extra : "", uids.join(';'));
		}
		catch(e) {
			this._ui_error(e, "inviteDialog");
		}
	}
	onInviteResult(status, data)
	{
		console.log("wdlib.api.ok.Api::onInviteResult : ", status, data);

		var sended = undefined;

		if(status == "ok") {
			data = data || "";
			sended = data.split(',');
		}

		if(this.invite_callback) {
			this.invite_callback.call(undefined, sended);
		}
//		this.invite_callback = undefined;
	}

	/**
	 * @param int width
	 * @param int height
	 */
	setAppSize(width, height)
	{
		try {
			FAPI.UI.setWindowSize(width, height);
		}
		catch(e) {
			this._ui_error(e, "setAppSize");
		}
	}

	/**
	 * @param String group_id
	 * @param String user_id
	 * @param Function callback
	 */
	isGroupMember(group_id, user_id, callback)
	{
		this._call("group.getUserGroupsByIds", {
			group_id: group_id,
			uids: user_id
		}, function(status, data, err) {
			//console.log("wdlib.api.ok.Api::isGroupMember result: ", status, data, err);

			var r = 0;
		
			if(status == "ok") {
				r = data && data.length ? 1 : 0;
			}

			callback.call(undefined, r);
		});
	}
	
	onShowPermissionResult(status, data)
	{
		console.log("wdlib.api.ok.Api::onShowPermissionResult : ", status, data);

		if(this.permissions_callback) {
			var callback = this.permissions_callback;
			
			this.permissions_callback = undefined;

			callback.call(undefined);
		}
	}

	/**
	 * @param String type
	 * @param Function callback
	 */
	getUploadServer(type, callback)
	{
		var self = this;

		var foo = function()
		{
			self._call("photosV2.getUploadUrl", {
			}, function(status, data, err) {
				console.log("wdlib.api.ok.Api::photosV2.getUploadUrl result: ", status, data, err);
			
				if(data && data.upload_url) {	
					callback.call(undefined, {
						upload_url: data.upload_url
					});
				}
			});
		}

		// first need to check permissions
		if(!this.permissions.get(PERMISSION_PHOTO) /*|| !this.permissions.get(PERMISSION_PUBLISH)*/) {
			// need to ask permissions
			this.permissions_callback = foo;

			try {
				FAPI.UI.showPermissions(JSON.stringify([PERMISSION_PHOTO/*, PERMISSION_PUBLISH*/]));
			}
			catch(e) {
				this._ui_error(e, "showPermissions");
			}
			
			/*
			FAPI.Client.call({
				method: "users.hasAppPermission",
				ext_perm: PERMISSION_PHOTO
			}, function(status, result, err) {
				console.log("wdlib.api.ok.Api::users.hasAppPermission result: ", status, result, err);

				if(result) {
					// permissions OK
					self.permissions.set(PERMISSION_PHOTO, 1);
					foo.call(undefined);
				}
				else {
					// need to ask permissions
					FAPI.UI.showPermissions(JSON.stringify([PERMISSION_PHOTO, PERMISSION_PUBLISH]));
				}
			});
			*/
		}
		else {
			foo.call(undefined);
		}
	}

	/**
	 * @param String user_id
	 * @param String type
	 * @param Object data
	 * @param Function callback
	 */
	saveUploadedData(user_id, type, data, callback)
	{
		var photos = data.photos;
		var photo_id = undefined;
		var token = undefined;
		for(var prop in photos) {
			photo_id = prop;
			token = photos[photo_id]["token"];
		}

		switch(type) {
			case "wall":
				// just call ok, cause already uploaded
				callback.call(undefined, [{photo_id: photo_id, token: token}]);
				break;
			case "user":
				// save image to user's album !!!
//				FAPI.Client.call({
//					method: "photosV2.commit",
//					photo_id: photo_id,
//					token: token,
//					comment: data.comment
				this._call("photosV2.commit", {
					photo_id: photo_id,
					token: token,
					comment: data.comment
				}, function(status, result, err) {
					console.log("wdlib.api.ok.Api::photosV2.commit result: ", status, result, err);
				});
				break;
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
		var media = [];
		if(mess) {
			media.push({
				type: "text",
				text: mess
			});
		}
		if(data.image) {
			media.push({
				type: "photo",
				list: [
					{id: data.image.token}
				//	{id: data.image.photo_id}
				]
			});
		}
//		if(data.url) {
//			media.push({
//				type: "link",
//				url: data.url
//			});
//		}

		// add app block
		if(data.app) {
			media.push({
				type: "app",
				images: [
					{url: data.app.image, mark: data.app.mark, title: data.app.text}
				],
				actions: [
					{text: data.app.text, mark: data.app.mark}
				]
			});
		}

		var attach = {media: media};
		//console.log("ATTACH : ", attach);
		

		var foo = function(status, data)
		{
			var post_id = 0;
			if(data) {
				post_id = wdlib.utils.intval(data);
			}
			callback.call(undefined, post_id);
		}

		this.wallpost_callback = foo;

		try {
			FAPI.UI.postMediatopic(attach, false);
		}
		catch(e) {
			this._ui_error(e, "postMediatopic");
		}

	}
	onWallPostResult(status, data)
	{
		console.log("wdlib.api.ok.Api::onWallPostResult : ", status, data);

		if(this.wallpost_callback) {
			var callback = this.wallpost_callback;
			
			this.wallpost_callback = undefined;

			callback.call(undefined, status, data);
		}
	}

	/**
	 * @param String user_id
	 * @param Object data
	 * @param Function callback
	 */
	isLiked(user_id, data, callback)
	{
		this._call("mediatopic.getByIds", {
			topic_ids: data.item_id, fields: "like_summary.self,reshare_summary.self,media_topic.like_summary,media_topic.reshare_summary"
		}, function(status, data, err) {
			// console.log("wdlib.api.ok.Api::isLiked result: ", status, data, err);

			var r = {};
		
			if(status == "ok") {
				var media_topics = data.media_topics || [];
				var media_topic = media_topics[0] || {};
				r.liked = media_topic.like_summary ? (media_topic.like_summary.self ? 1 : 0) : 0;
				r.copied = media_topic.reshare_summary ? (media_topic.reshare_summary.self ? 1 : 0) : 0;
			}

			callback.call(undefined, r);
		});
	}

	checkFlag(flag, callback)
	{
		this._call("users.checkFlag", {flag: flag}, function(status, data, err) {
			console.log("wdlib.api.ok.Api::checkFlag result: ", status, data, err);
			callback.call(undefined, (data && data.success) ? true : false);
		});
	}
	resetFlag(flag, callback)
	{
		this._call("users.resetFlag", {flag: flag}, function(status, data, err) {
			console.log("wdlib.api.ok.Api::resetFlag result: ", status, data, err);
		});
	}
}

exports.Api = Api;
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
	year = parseInt(user.birthday.slice(prev_idx, idx));

	// get month
	if(idx != -1) {
		prev_idx = idx + 1;
		idx = user.birthday.indexOf("-", prev_idx);
		mon = parseInt(user.birthday.slice(prev_idx, idx));
	}

	// get day
	if(idx != -1) {
		prev_idx = idx + 1;
		day = parseInt(user.birthday.slice(prev_idx));
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

function _photosSort(a, b)
{
	var r = b.likes - a.likes;

	if(r == 0) {
		r = b.mark - a.mark;
	}

	if(r == 0) {
		r = b.mark_count - a.mark_count;
	}

	return r;
}

}, (wdlib.api = wdlib.api || {}).ok = wdlib.api.ok || {});
