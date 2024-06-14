wdlib.module("wdlib/api/ok-mobile.js", [
	"wdlib/api/base.js"
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

var OK_PAYMENT_FRAME = "okPaymentFrame__INLOVE";

// INIT PARAMS EXAMPLE
// container=true; sig=653e64779a6f112ea45cf126e531a958; mob=true; refplace=user_apps; session_key=-s-3ztxMmE8HYs1mosCNztwmCGAH.u1nBKAHzQ-rpJBMZRxOpp8M0UWJmIju1-0LGJ7H5QwrFqDuxuUpFMjGyuULKHCIbQxJJG8NWtwM7; session_secret_key=88de3193718bc605adf43ea6bf758bf2; auth_sig=e4961919ee6fd193316cbfb345555115; api_server=https://api.ok.ru/; lang=ru; application_key=CBABBDEDEBABABABA; logged_user_id=576240183981;

// ============================================================================
// wdlib.api.ok.ApiMobile class

class ApiMobile extends wdlib.api.Base {

	constructor(args)
	{
		super(args);
		
		console.log("wdlib.api.ok.ApiMobile : ", args);

		this.viewer_id = args.logged_user_id || this.viewer_id;
		this.partner_url = "http://ok.ru";
		
		this.auth_params = {
			"session_key": args.session_key || "",
			"auth_sig": args.auth_sig || ""
		};

		this.api_server = args.api_server || "https://api.ok.ru";

		this.app_id = wdlib.Config.APP_ID;
		
		this.extra_param = args.custom_args || "";
		if(this.extra_param) {
			this.extra_param = this.extra_param.replace(/^ref=/, "");
		}

		this.permissions = new Map;
		this.permissions_callback = undefined;

		this.wallpost_callback = undefined;

		wdlib.module.config({
			map: {
				"oksdk.js" : "https://static2.lalakla.ru/libs/oksdk/1/oksdk.js"
			}
		});

		this.post_message_callback = undefined;
		var self = this;

		// GLOBAL OK_API Event Callback function
		// fucking hack for OK :-(
		window.addEventListener("message", function(mess) {
			if(mess.origin.match(/ok\.ru$/)) {
				//alert("MESS : " + mess.data);
				//alert("MESS.origin : " + mess.origin);
				var data = JSON.stringify(mess.data);
				console.log("wdlib.api.ok.ApiMobile.onMessage : ", data);

				if(self.post_message_callback) {
					self.post_message_callback.call(self, data);
				}
				if(self.payment_callback) {
					self.payment_callback.call(self, data);
				}
			}
		}, false);
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
		super.init(callback, on_error);

		var self = this;
		
		wdlib.load([{src: "oksdk.js", type: "js", crossOrigin: null}], function() {
			OKSDK.init({
				app_id: self.app_id,
				app_key: wdlib.Config.CLIENT_KEY,
				oauth: {
					scope: PERMISSION_PHOTO
				}
			}, function() {
				// init OK
				console.log("wdlib.api.ok.ApiMobile: init OK");

				self.getProfile(self.viewer_id, function(data) {
					callback.call(null, data, self.auth_params);
				});

				// check permissions

				var i = 0;
				var check = function()
				{
					if(i >= NEED_PERMISSIONS.length) return;

					var perm = NEED_PERMISSIONS[i];
					OKSDK.REST.call("users.hasAppPermission", {
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
				console.log("wdlib.api.ok.ApiMobile: init ERROR : ", err);
			})
		});
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

		var user = {
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

		OKSDK.REST.call("users.getInfo", {
			uids: user_id,
			fields: USER_FIELDS
		}, function(status, data, err) {
			//console.log("wdlib.api.ok.Api::getProfile result: ", status, data, err);
			//alert("users.getInfo OK : " + status + ", " + OKSDK.Util.toString(data) + ", " + OKSDK.Util.toString(err));

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
				
				user.is_online = (data.online && data.online == 'mobile') || 0;
			
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
		OKSDK.REST.call("photos.getPhotos", {
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
			OKSDK.REST.call(online ? "friends.getOnline" : "friends.get", {}, _onFriendsLoaded);
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
			OKSDK.REST.call("users.getInfo", {
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
			//console.log("wdlib.api.ok.Api::users.getInfo result: ", status, data, err);

			data = data || [];

			var count = data ? data.length : 0;
			//console.log("wdlib.api.ok.Api::users.getInfo found: ", count);

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

			OKSDK.REST.call("friends.getAppUsers", {}, _onInstalledLoaded);
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
		var self = this;

		this.payment_callback = function(data)
		{
			console.log("PAYMENT CALLBACK : ", data);
			
			self.payment_callback = undefined;

			if(!wdlib.Config.IS_WEB_VIEW) {
				OKSDK.Payment.closePaymentFrame(OK_PAYMENT_FRAME);
			}
		}

		var code = gold;
		var options = "";

		if(!(extra instanceof String)) {
			code = extra.code || code;
			options = extra.extra;
		}
		else {
			options = extra;
		}

//		alert("PAYMENT : extra : " + OKSDK.Util.toString(extra));
//		extra = JSON.parse(extra);
//		alert("PAYMENT : extra : json : " + OKSDK.Util.toString(extra));
		if(wdlib.Config.IS_WEB_VIEW) {
			OKSDK.Payment.show(name, amount, code, options);
		}
		else {
			OKSDK.Payment.showInFrame(name, amount, code, options, OK_PAYMENT_FRAME);
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
		var self = this;

		this.post_message_callback = function(data) 
		{
			var sended = undefined;
			if(data.code == "OK" && data.selected) {
				sended = data.selected;
			}

			self.post_message_callback = undefined;
			
			if(callback) {
				callback.call(undefined, sended);
			}
		}
		OKSDK.Widgets.suggest(null, {
			comment: mess,
			custom_args: (extra && extra.extra) ? extra.extra : "",
			target: uids.join(','),
			nohead: false,
			popup: true
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
		var self = this;

		this.post_message_callback = function(data) 
		{
			var sended = undefined;
			if(data.code == "OK" && data.selected) {
				sended = data.selected;
			}
			
			self.post_message_callback = undefined;

			if(callback) {
				callback.call(undefined, sended);
			}
		}
		OKSDK.Widgets.invite(null, {
			comment: mess,
			custom_args: (extra && extra.extra) ? extra.extra : "",
			target: uids.join(','),
			nohead: false,
			popup: true
		});
	}

	/**
	 * @param int width
	 * @param int height
	 */
	setAppSize(width, height)
	{
	//	FAPI.UI.setWindowSize(width, height);
	}

	/**
	 * @param String group_id
	 * @param String user_id
	 * @param Function callback
	 */
	isGroupMember(group_id, user_id, callback)
	{
		OKSDK.REST.call("group.getUserGroupsByIds", {
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
			OKSDK.REST.call("photosV2.getUploadUrl", {}, function(status, data, err) {
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
//			this.permissions_callback = foo;
//			FAPI.UI.showPermissions(JSON.stringify([PERMISSION_PHOTO/*, PERMISSION_PUBLISH*/]));
			
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
				OKSDK.REST.call("photosV2.commit", {
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
		//alert("CALL WALLPOST");
		
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
		
		var self = this;

		this.post_message_callback = function(data) 
		{
			var post_id = 0;
			if(data.id) {
				post_id = wdlib.utils.intval(data.id);
			}
			
			self.post_message_callback = undefined;

			if(callback) {
				callback.call(undefined, post_id);
			}
		}
		
		OKSDK.Widgets.post(null, JSON.stringify(attach));
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
