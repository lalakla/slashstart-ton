wdlib.module("wdlib/api/vk/open.js", [
	"wdlib/api/vk/base.js",
	"wdlib/utils/date.js"
],
function(exports) {
"use strict";

var _users = new Map;
var _installed = undefined;

var VK_JS_SDK = "https://vk.com/js/api/openapi.js?168";
var VK_SETTINGS = /*wdlib.api.vk.VK_PERM_NOTIFY | */wdlib.api.vk.VK_PERM_FRIENDS | wdlib.api.vk.VK_PERM_PHOTOS;

// ============================================================================
// wdlib.api.vk.ApiOpen class

class ApiOpen extends wdlib.api.vk.Base {

	constructor(args)
	{
		super(args);
		
		console.log("VK OPEN API : ", args);

		this.viewer_id = args.viewer_id || this.viewer_id;
		this.app_id = args.app_id | "";

		this.extra_param = args.referrer || "";
		if(this.extra_param) {
			this.extra_param = this.extra_param.replace(/^ad_/, "");
		}

		wdlib.module.config({
			map: {
				"vk-open.api.js" : VK_JS_SDK
			}
		});
	}

	init(callback)
	{
		super.init(callback);

		var self = this;

		wdlib.load([{src: "vk-open.api.js", type: "js", crossOrigin: null}], function() {

			console.log("VK Open API : loaded : ", VK);

			VK.init({
				apiId: self.app_id
			});

			callback.call();
		});
	}

	login(callback)
	{
		var self = this;

		VK.Auth.login(function(data) {
			console.log("VK Open API : login : ", data);

			if(data.status != "connected" || !data.session) {
				// auth error
				callback.call(undefined, false);
				return;
			}

			self.session = data.session;
			self.viewer_id = self.session.mid;

			var now = new Date;
			var exp = new Date(self.session.expire * 1000);

			console.log("VK Open API : login : viewer_id ", self.viewer_id, " expire in ", wdlib.utils.date.timeout2str((exp.getTime() - now.getTime()) / 1000));

			self.getProfile(self.viewer_id, function(data) {
				// console.log("VK Open API : currentUser : ", data);

				self._init_state = wdlib.api.INIT_STATE_COMPLETE;
				callback.call(null, data, {
					expire: self.session.expire,
					mid: self.session.mid,
					secret: self.session.secret,
					sid: self.session.sid,
					sig: self.session.sig
				});
			});
		}, VK_SETTINGS);
	}

	_call(method, params, callback)
	{
		params = Object.assign(params, {
			v: wdlib.api.vk.API_VERSION
		});
		try {
			VK.Api.call(method, params, function(data) {
				callback.call(undefined, data);
			});
		}
		catch(e) {
			console.log("wdlib.api.vk.ApiOpen : _call : error catch : ", e);

			var error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR_API, stack: e.stack, file: e.fileName || "wdlib.api.vk.open.js"});
			error.error += "; method=" + method;
			error.error += "; params=" + JSON.stringify(params);
			if(this.on_error_callback) {
				this.on_error_callback.call(null, error);
			}
		}
	}

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
			platform: wdlib.api.API_VK,
			name: "",
			sex: 0,
			pic: "",
			big_pic: "",
			age: 0,
//			birthday: "",
			city: wdlib.api.DEFAULT_CITY,
			city_id: 0,
			anketa_link: this.partner_url + "/id" + user_id,
			has_mobile: 0
		};

		this._call("users.get", {
			uids: user_id,
			fields: wdlib.api.vk.USER_FIELDS
		}, function(data) {
//			console.log("wdlib.api.vk.ApiOpen::getProfile result: ", data);

			if(data && data.response && data.response[0]) {
				data = data.response[0];
				user.name = data.first_name;
				user.sex = data.sex || user.sex;
				user.pic = data.photo_100 || user.pic;
				user.big_pic = data.photo_200_orig || user.big_pic;
				user.birthday = data.bdate || user.birthday;
				user.has_mobile = data.has_mobile || user.has_mobile;
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

				wdlib.api.vk.setUserAge(user);

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
		this._call("photos.getAll", {
			owner_id: user_id,
			extended: 1,
			no_service_albums: 1,
			count: 100
		}, function(data) {
//			console.log("wdlib.api.vk.ApiOpen::getUserPhotos result: ", data);

			var photos = [];

			if(data && data.response && data.response.hasOwnProperty("items") && data.response.items.length) {
				for(var i=0; i<data.response.items.length; ++i) {
					var p = data.response.items[i];

					var photo = {
						//small: p["photo_75"],
						//medium: p["photo_130"],
						//big: p["photo_604"],
						likes: (p["likes"]) ? (p["likes"]["count"] + p["likes"]["user_likes"]) : 0
					};

					if(p.sizes && p.sizes.length) {
						for(var j=0; j<p.sizes.length; ++j) {
							switch(p.sizes[j].type) {
								case "s":
									photo.small = p.sizes[j].url;
									break;
								case "m":
									photo.medium = p.sizes[j].url;
									break;
								case "x":
									photo.big = p.sizes[j].url;
									break;
							}
						}
					}

					if(photo.small && photo.big) {
						photos.push(photo);
					}
				}

				photos.sort(wdlib.api.vk.photosSort);
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

		var offset = 0;

		// FUNCTIONS

		var _load = function()
		{
			self._call("friends.get", {
				fields: wdlib.api.vk.USER_FIELDS,
				count: 150,
				offset: offset
			}, _onFriendsLoaded);
		}

		var _onFriendsLoaded = function(data, check_zero = true)
		{
//			console.log("wdlib.api.vk.Api::_onFriendsLoaded result: ", data);
			
			data = data.response || [];

			if(data.hasOwnProperty("items")) {
				data = data.items;
			}

			var count = data ? data.length : 0;
//			console.log("wdlib.api.vk.Api::_onFriendsLoaded found: ", count, " friends, offset: ", offset);

			if(check_zero) {
				offset += count;
			}

			for(var i=0; i<data.length; ++i) {
				var user_id = String(data[i]["id"]);

				// check internal cache
				if(_users.get(user_id)) {
					// already loaded
					continue;
				}

				if(data[i]["deactivated"]) {
					// user removed or blocked
					// do not show
					continue;
				}

				var user = {
					remote_id: user_id,
					platform: wdlib.Config.CLIENT_PLATFORM,
					name: data[i]["first_name"],
					sex: data[i]["sex"] || 0,
					pic: data[i]["photo_100"],
					big_pic: data[i]["photo_200_orig"],
					age: 0,
					birthday: data[i]["bdate"],
					city: wdlib.api.DEFAULT_CITY,
					zodiac_name: "",
					is_app_user: (_installed.has(user_id) ? 1 : 0),
					anketa_link: self.partner_url + "/id" + user_id,
					is_from_contact: 1,
					is_online: data[i]["online"] || 0
				};
				if(data[i].hasOwnProperty("city")) {
					if(data[i].city instanceof Object) {
						user.city = data[i].city.title;
						user.city_id = data[i].city.id;
					}
					else {
						user.city = "";
						user.city_id = wdlib.utils.intval(data.city);
					}
				}

				wdlib.api.vk.setUserAge(user);

				_users.set(user.remote_id, user);
				retval.push(user);
			}

			if(check_zero && (!count || retval.length >= limit)) {
				// all friends loaded
				//wdlib.utils.object.shuffle(retval);
				callback.call(undefined, retval);
			}
			else {
				setTimeout(_load, wdlib.api.vk.FRIEND_CALL_DELAY);
			}
		}

		var _onInstalledLoaded = function(data)
		{
//			console.log("wdlib.api.vk.Api::friends.getAppUsers result: ", data);

			data = data.response || [];

			var count = data ? data.length : 0;
//			console.log("wdlib.api.vk.Api::friends.getAppUsers found: ", count, " friends already installed app");

			var foo = _load;

			if(count) {
				for(var i=0; i<data.length; ++i) {
					_installed.add(String(data[i]));
				}

				foo = function()
				{
					self._call("users.get", {
						user_ids: data.slice(0,100).join(','),
						fields: wdlib.api.vk.USER_FIELDS
					}, function(data) {_onFriendsLoaded.call(undefined, data, false);});
				}
			}

			setTimeout(foo, wdlib.api.vk.FRIEND_CALL_DELAY);
		}

		// START HARE

		if(!_installed) {
			// load installed app friend ids first
			_installed = new Set;

			this._call("friends.getAppUsers", {}, _onInstalledLoaded);
		}
		else {
			_load.call();
		}
	}

	billingDialog(gold, amount, callback, name, desc, pic, extra)
	{
		// temporary switch payments off
		callback.call(null, 0, wdlib.api.BILLING_FALSE);
		return;

		/*
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
		*/

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
		
		setTimeout(function() {
			callback.call(null, undefined);
		}, 500);

		/*
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
		*/
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
			/*
			try {
				VK.callMethod("showInviteBox");
			}
			catch(e) {
				this._ui_error(e, "inviteDialog");
			}
			*/

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
		/*
		try {
			VK.callMethod("resizeWindow", width, height);
		}
		catch(e) {
			this._ui_error(e, "setAppSize");
		}
		*/
	}

	/**
	 * @param Function callback
	 */
	isFavourites(callback)
	{
		/*
		this._call("account.getAppPermissions", {}, function(data) {
			//console.log("wdlib.api.vk.Api::isFavourites result: ", data);
			var r = wdlib.utils.intval(data && data.response ? data.response : 0);
			callback.call(undefined, (r & VK_PERM_FAVOURITES) ? 1 : 0);
		});
		*/
	}
	/**
	 * @param Function callback
	 */
	favouritesDialog(callback)
	{
		/*
		var _handler = function(result)
		{
			VK.removeCallback("onSettingsChanged", _handler);
			VK.removeCallback("onSettingsCancel", _handler);
			
			// console.log("wdlib.api.vk.Api::favouritesDialog result: ", result);
			
			var r = wdlib.utils.intval(result ? result : 0);
			callback.call(undefined, (r & VK_PERM_FAVOURITES) ? 1 : 0);
		}
		
		VK.addCallback("onSettingsChanged", _handler);
		VK.addCallback("onSettingsCancel", _handler);

		try {
			VK.callMethod("showSettingsBox", VK_PERM_FAVOURITES);
		}
		catch(e) {
			this._ui_error(e, "favouritesDialog");
		}
		*/
	}

	/**
	 * @param String group_id
	 * @param String user_id
	 * @param Function callback
	 */
	isGroupMember(group_id, user_id, callback)
	{
		this._call("groups.isMember", {group_id: group_id, user_id: user_id}, function(data) {
			//console.log("wdlib.api.vk.Api::isGroupMember result: ", data);
			callback.call(undefined, (data && data.response) ? 1 : 0);
		});
	}

	/**
	 * @param String type
	 * @param Function callback
	 */
	getUploadServer(type, callback)
	{
		var ok = function(data)
		{
			callback.call(undefined, {
				upload_url: data.upload_url
			});
		}

		switch(type) {
			case "wall":
			default:
				this._call("photos.getWallUploadServer", {}, function(data) {
					console.log("wdlib.api.vk.Api::photos.getWallUploadServer result: ", data);
					if(data && data.response) {
						ok.call(undefined, data.response);
					}
				});
				break;
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
		var ok = function(data)
		{
			callback.call(undefined, data);
		}

		switch(type) {
			case "wall":
			default:
				this._call("photos.saveWallPhoto", {user_id: user_id, photo: data.photo, hash: data.hash, server: data.server}, function(data) {
					console.log("wdlib.api.vk.Api::photos.saveWallPhoto result: ", data);
					ok.call(undefined, data.response);
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
		var attach = "";
		if(data.image) {
			attach += (attach.length ? "," : "") + "photo" + String(data.image.owner_id) + "_" + String(data.image.id);
		}
		if(data.url) {
			attach += (attach.length ? "," : "") + data.url;
		}
		// console.log("ATTACH : ", attach);
		this._call("wall.post", {owner_id: user_id, message: mess, attachments: attach}, function(data) {
			console.log("wdlib.api.vk.Api::wall.post result: ", data);

			var post_id = 0;
			if(data.response && data.response.post_id) {
				post_id = data.response.post_id;
			}
			callback.call(undefined, post_id);
		});
	}

	/**
	 * @param String user_id
	 * @param Object data
	 * @param Function callback
	 */
	isLiked(user_id, data, callback)
	{
		this._call("likes.isLiked", {user_id: user_id, type: data.type, owner_id: data.owner_id, item_id: data.item_id}, function(data) {
			//console.log("wdlib.api.vk.Api::isLiked result: ", data);

			callback.call(undefined, (data && data.response) ? data.response : {});
		});
	}

	/**
	 * @param int counter
	 * @param Boolean increment [optional, default=false]
	 */
	setCounter(counter, increment = false)
	{
		/*
		this._call("setCounter", {counter: counter, increment: (increment ? 1 : 0)}, function(data) {
			console.log("wdlib.api.vk.setCounter result: ", data);
		});
		*/
	}
}

exports.ApiOpen = ApiOpen;
// ============================================================================

}, (wdlib.api = wdlib.api || {}).vk = wdlib.api.vk || {});
