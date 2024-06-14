wdlib.module("wdlib/api/mm.js", [
	"wdlib/api/base.js",
	"wdlib/net/http.js",
	"wdlib/utils/utils.js"
],
function(exports) {
"use strict";

var _users = new Map;

var MM_JS_SDK = "https://connect.mail.ru/js/loader.js";

var IGNORE_URL_PARAMS = ["https"];

// ============================================================================
// wdlib.api.mm.Api class

class Api extends wdlib.api.Base {

	constructor(args)
	{
		super(args);
		
		console.log("wdlib.api.mm.Api : ", args);

		this.viewer_id = args.vid || this.viewer_id;
		this.app_id = args.app_id || "";
		this.partner_url = "http://my.mail.ru";

		this.auth_params = {};
		for(var k in args) {
			if(IGNORE_URL_PARAMS.indexOf(k) != -1) continue;
			this.auth_params[k] = args[k];
		}

		wdlib.module.config({
			map: {
				"mm.api.js" : MM_JS_SDK
			}
		});
	}

	appurl(extra)
	{
		var url = this.partner_url + "/apps/" + this.app_id;

		// NO EXTRA PARAM AVAILABLE FOR MY.MAIL.RU :-(
//		if(extra) {
//			url += "/?ref=" + extra;
//		}

		return url;
	}

	init(callback, on_error)
	{
		if(this._init_state != wdlib.api.INIT_STATE_START) {
			// double call of api.init
			var error = new wdlib.model.Error({error: "API INIT ERROR : double call", error_code: wdlib.model.error.ERROR_API});
			error.file = "wdlib.api.mm.js";

			if(this.on_error_callback) {
				this.on_error_callback.call(null, error);
			}
			return;
		}

		super.init(callback, on_error);

		var self = this;
		this._init_state = wdlib.api.INIT_STATE_CALLED;

		wdlib.load([{src: "mm.api.js", type: "js", crossOrigin: null}], function() {

			if(self._init_state != wdlib.api.INIT_STATE_CALLED) {
				// double call of api.init
				var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.load_sdk", error_code: wdlib.model.error.ERROR_API});
				error.file = "wdlib.api.mm.js";

				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
				return;
			}
			self._init_state = wdlib.api.INIT_STATE_SDK_LOADED;

			try {
				mailru.loader.require("api", function() {
					if(self._init_state != wdlib.api.INIT_STATE_SDK_LOADED) {
						// double call of api.init
						var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.init_sdk", error_code: wdlib.model.error.ERROR_API});
						error.file = "wdlib.api.mm.js";

						if(self.on_error_callback) {
							self.on_error_callback.call(null, error);
						}
						return;
					}
				
					mailru.app.init(wdlib.Config.CLIENT_KEY);

					// init OK
					console.log("wdlib.api.mm.Api: init OK");
					self._init_state = wdlib.api.INIT_STATE_INIT_PASSED;
	
					self.getProfile(self.viewer_id, function(data) {
						if(self._init_state != wdlib.api.INIT_STATE_INIT_PASSED) {
							// double call of api.init
							var error = new wdlib.model.Error({error: "API INIT ERROR : double call of init.getProfile", error_code: wdlib.model.error.ERROR_API});
							error.file = "wdlib.api.mm.js";

							if(self.on_error_callback) {
								self.on_error_callback.call(null, error);
							}
							return;
						}

						self._init_state = wdlib.api.INIT_STATE_COMPLETE;
						callback.call(null, data, self.auth_params);
					});

					// add event listeners
					mailru.events.listen(mailru.app.events.friendsInvitation, function(event) {
						self.onInviteResult(event);
					});
					mailru.events.listen(mailru.app.events.friendsRequest, function(event) {
						self.onMessageResult(event);
					});
					mailru.events.listen(mailru.app.events.paymentDialogStatus, function(event) {
						self.onBillingDialogResult(event);
					});
				});
			}
			catch(e) {
				console.log("MM API INIT ERROR CATCH : ", e);
				
				var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.mm.js"});
				error.error += "; method=INIT";
				if(self.on_error_callback) {
					self.on_error_callback.call(null, error);
				}
			}
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
			is_verified: 0,
			has_mobile: 0,
			has_email: 0
		};

		mailru.common.users.getInfo(function(data) {
			//console.log("wdlib.api.mm.Api::getProfile result: ", data);

			if(!data || !data.length) {
				console.log("wdlib.api.mm.Api::getProfile ERROR : ", data);
			}
			else {
				data = data[0];

				user.name = data.first_name || user.name;
				user.sex = (data.sex == 0) ? wdlib.api.MALE : wdlib.api.FEMALE;
				user.pic = data.pic || user.pic;
				user.big_pic = data.pic_big || user.pic;
				user.age = data.age || user.age;
				user.birthday = data.birthday || user.birthday;
				user.city = (data.location ? (data.location.city ? data.location.city.name : undefined) : undefined) || user.city;
				user.city_id = (data.location ? (data.location.city ? data.location.city.id : undefined) : undefined) || user.city_id;
				user.anketa_link = data.link || user.anketa_link;

				user.is_app_user = data.app_installed || 0;
				user.is_online = data.is_online || 0;
				user.is_verified = data.is_verified || 0;
				
				wdlib.api.mm.setUserAge(user);

				_users.set(user.remote_id, user);
			}
			
			callback.call(null, user);

		}, user_id);
	}

	/**
	 * this works for currentUser only !!!
	 *
	 * @param String user_id
	 * @param Function callback
	 */
	getUserPhotos(user_id, callback)
	{
		var _photos = [];
		var _albums = [];

		var _loadPhotos = function()
		{
			if(!_albums.length) {
				callback.call(null, user_id, _photos);
				return;
			}

			var album = _albums.shift();
			mailru.common.photos.get(function(data) {
				//console.log("wdlib.api.mm.Api::photos.get result: ", data);
			
				if(!data || !data.length) {
					console.log("wdlib.api.mm.Api::photos.get ERROR : ", data);
				}
				else {
					for(var i=0; i<data.length; ++i) {
						_photos.push({
							small: data[i]["src_small"],
							medium: data[i]["src"],
							big: data[i]["src"]
						});
					}
				}
			
				_loadPhotos();

			}, album.aid);
		}

		// load albums first
		mailru.common.photos.getAlbums(function(data) {
			//console.log("wdlib.api.mm.Api::photos.getAlbums result: ", data);

			if(!data || !data.length) {
				console.log("wdlib.api.mm.Api::photos.getAlbums ERROR : ", data);
				callback.call(null, user_id, _photos);
				return;
			}

			_albums = data.slice();

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

		var online = true;

		// FUNCTIONS

		var _load = function()
		{
			mailru.common.friends.getExtended(_onFriendsLoaded);
		}

		var _onFriendsLoaded = function(data)
		{
			console.log("wdlib.api.mm.Api::friends.getExtended result: ", data);

			data = data || [];

			var count = data ? data.length : 0;
			console.log("wdlib.api.mm.Api::friends.getExtended found: ", count);
			
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
					sex: (res["sex"] == 0) ? wdlib.api.MALE : wdlib.api.FEMALE,
					pic: res["pic"],
					big_pic: res["pic_big"],
					age: res["age"],
					birthday: res["birthday"],
					city: (res["location"] ? (res["location"].city ? res["location"].city.name : undefined) : undefined) || wdlib.api.DEFAULT_CITY,
					city_id: (res["location"] ? (res["location"].city ? res["location"].city.id : undefined) : undefined) || 0,
					zodiac_name: "",
					anketa_link: res["link"],
					is_app_user: res["app_installed"] || 0,
					is_online: res["is_online"] || 0,
					is_from_contact: 1,
					is_verified: res["is_verified"] || 0
				};

				wdlib.api.mm.setUserAge(user);

				_users.set(user.remote_id, user);
				retval.push(user);
			}

			callback.call(undefined, retval);
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
		console.log("wdlib.api.mm.Api::BillingDialog : ", gold, amount, name, desc, pic, extra);
		
		this.billing_callback = callback;
		mailru.app.payments.showDialog({
			service_id: wdlib.utils.intval(extra),
			service_name: name,
			mailiki_price: amount
		});
	}
	onBillingDialogResult(event)
	{
		console.log("wdlib.api.mm.Api::onBillingDialogResult : ", event);

		if(event.status == "closed") {
			if(this.billing_callback) {
				this.billing_callback.call(undefined, 0, wdlib.api.BILLING_WAIT);
			}
			this.billing_callback = undefined;
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
		mailru.app.friends.request({
			image_url: (extra && extra.img) ? extra.img : "",
			text: mess,
			friends: uids
		});
	}
	onMessageResult(event)
	{
		console.log("wdlib.api.mm.Api::onMessageResult : ", event);

		if(event.status == "closed") {
			var sended = event.data ? event.data : undefined;

			if(this.message_callback) {
				this.message_callback.call(undefined, sended);
			}
			this.message_callback = undefined;
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
		this.invite_callback = callback;
		mailru.app.friends.invite({
			text: mess
		});
	}
	onInviteResult(event)
	{
		console.log("wdlib.api.mm.Api::onInviteResult : ", event);

		if(event.status == "closed") {
			var sended = event.data ? event.data : undefined;

			if(this.invite_callback) {
				this.invite_callback.call(undefined, sended);
			}
			this.invite_callback = undefined;
		}
	}

	/**
	 * @param int width
	 * @param int height
	 */
	setAppSize(width, height)
	{
		mailru.app.utils.setHeight(height);
	}
}

exports.Api = Api;
// ============================================================================

// ============================================================================
// wdlib.api.mm.ApiMobile class

class ApiMobile extends Api {

	constructor(args)
	{
		super(args);

		this.mobile_spec = args.mobile_spec || "";
		this.session_key = args.session_key || "";

		
		console.log("wdlib.api.mm.ApiMobile : ", args);
	}

	init(callback, on_error)
	{
		var self = this;
		var foo = function(data, auth_params)
		{
			console.log("wdlib.api.mm.ApiMobile : init OK : ", mailru);

			// retrieve canvas
			// they said method is deprecated !!!
			// i stay it here, to have a help how to use request directly through mailru js sdk
//			mailru.batcher.reqest("mobile.getCanvas", {mobile_spec: self.mobile_spec}, function(data) {
//				console.log("wdlib.api.mm.ApiMobile : init : getCanvas : ", data);
//			});
			
			callback.call(null, data, auth_params);
		}

		super.init(foo, on_error);
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
		console.log("wdlib.api.mm.ApiMobile::BillingDialog : ", gold, amount, name, desc, pic, extra);

		var params = {
			appid: this.app_id,
			session_key: this.session_key,
			service_id: wdlib.utils.intval(extra),
			service_name: name,
			mailiki_price: amount,
			mob: 1
		}

		var url = "https://m.my.mail.ru/cgi-bin/app/paymentm?" + wdlib.net.Http.encode(params);

		console.log("BILLING URL : ", url);

		callback.call(null, amount, wdlib.api.BILLING_WAIT);

		window.location = url;
	}

	/**
	 * @param int width
	 * @param int height
	 */
	setAppSize(width, height)
	{
		// do nothing
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
	
	// get day
	idx = user.birthday.indexOf(".");
	day = wdlib.utils.intval(user.birthday.slice(prev_idx, idx));

	// get month
	if(idx != -1) {
		prev_idx = idx + 1;
		idx = user.birthday.indexOf(".", prev_idx);
		mon = wdlib.utils.intval(user.birthday.slice(prev_idx, idx));
	}

	// get year
	if(idx != -1) {
		prev_idx = idx + 1;
		year = wdlib.utils.intval(user.birthday.slice(prev_idx));
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

}, (wdlib.api = wdlib.api || {}).mm = {});
