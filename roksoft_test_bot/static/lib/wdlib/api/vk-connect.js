wdlib.module("wdlib/api/vk-connect.js", [
	"wdlib/api/base.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/object.js"
],
function(exports) {
"use strict";

var _users = new Map;
var _installed = undefined;

//var VK_JS_SDK = "https://vk.com/js/api/mobile_sdk.js";
//var VK_JS_SDK = "https://static2.lalakla.ru/libs/vkui-connect/1.5.0/index.js";
var VK_API_VERSION = "5.85";

var FRIEND_CALL_DELAY = 1000;
var USER_FIELDS = "photo_100,photo_200_orig,sex,bdate,city,has_mobile,verified,online";

var VK_PERM_FAVOURITES = 256;

// ============================================================================
// wdlib.api.vk.ApiConnect class

class ApiConnect extends wdlib.api.Base {

	constructor(args)
	{
		super(args);
		
		console.log("VK CONNECT API : ", args);

		this.viewer_id = args.viewer_id || this.viewer_id;
		this.auth_key = args.auth_key || "";
		this.app_id = args.api_id | "";
		this.partner_url = "http://vk.com";

		this.extra_param = args.referrer || "";
		if(this.extra_param) {
			this.extra_param = this.extra_param.replace(/^ad_/, "");
		}

		/*
		wdlib.module.config({
			map: {
				"vk.api.js" : VK_JS_SDK
			}
		});
		*/
	}

	appurl(extra)
	{
		var url = this.partner_url + "/app" + this.app_id;
		if(extra) {
			url += "?ad_id=" + extra;
		}
		return url;
	}

	init(callback)
	{
		super.init(callback);

		var self = this;

		wdlib.load([{src: "wdlib/api/vk-connect-sdk.js", type: "js"/*, crossOrigin: null*/}], function() {

			//alert("VK CONNECT LOADED : " + vkuiConnect);
			console.log("VK Connect API : loaded : ", vkuiConnect);

			vkuiConnect.send("VKWebAppInit", {});
			vkuiConnect.subscribe(function(e) {
				console.log("EVENT : ", e);
			});

			/*
			// get auth token
			var _release = function(e)
			{
				console.log("VK Connect API : _release : ", e);
				alert("AUTH TOKEN :: _release : " + e);
			}
			vkuiConnect.subscribe(_release);
			vkuiConnect.send("VKWebAppGetAuthToken", {
				app_id: self.app_id,
				scope: "friends,photos"
			});
			*/

			self.getProfile(self.viewer_id, function(data) {
				callback.call(null, data, self.auth_key);
			});

			/*
			VK.init(function() {
				// API INIT OK
				console.log("VK Mobile API: init ok : ", VK);

				self.getProfile(self.viewer_id, function(data) {
					callback.call(null, data, self.auth_key);
				});

			}, function() {
				console.log("VK Mobile API: init FALSE");
			}, VK_API_VERSION);
			*/
		});
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
			platform: wdlib.Config.CLIENT_PLATFORM,
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

		try {

			var _release = function(e)
			{
				console.log("wdlib.api.vk.ApiConnect::getProfile::_release ", e);
				alert(e);
			}

			vkuiConnect.subscribe(_release);
			vkuiConnect.send("VKWebAppCallAPIMethod", {
				method: "users.get",
				request_id: "lalakla",
				params: {
					user_ids: user_id,
					fields: USER_FIELDS,
					access_token: "sdfsdf"
				}
			});
			
			/*
			VK.api("users.get", {
				uids: user_id,
				fields: USER_FIELDS 
			}, function(data) {
				console.log("wdlib.api.vk.ApiMobile::getProfile result: ", data);

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

			*/
		}
		catch(e) {
			console.log("wdlib.api.vk.ApiConnect::getProfile : Error: ", e);
		}
	}
	
	/**
	 * @param String user_id
	 * @param Function callback
	 */
	getUserPhotos(user_id, callback)
	{
		VK.api("photos.getAll", {
			owner_id: user_id,
			extended: 1,
			no_service_albums: 1,
			count: 100
		}, function(data) {
			//console.log("wdlib.api.vk.Api::getUserPhotos result: ", data);

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

		var offset = 0;

		// FUNCTIONS

		var _load = function()
		{
			VK.api("friends.get", {
				fields: USER_FIELDS,
				count: 150,
				offset: offset
			}, _onFriendsLoaded);
		}

		var _onFriendsLoaded = function(data, check_zero = true)
		{
			console.log("wdlib.api.vk.Api::_onFriendsLoaded result: ", data);
			
			data = data.response || [];

			if(data.hasOwnProperty("items")) {
				data = data.items;
			}

			var count = data ? data.length : 0;
			console.log("wdlib.api.vk.Api::_onFriendsLoaded found: ", count, " friends, offset: ", offset);

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
				setTimeout(_load, FRIEND_CALL_DELAY);
			}
		}

		var _onInstalledLoaded = function(data)
		{
			console.log("wdlib.api.vk.Api::friends.getAppUsers result: ", data);

			data = data.response || [];

			var count = data ? data.length : 0;
			console.log("wdlib.api.vk.Api::friends.getAppUsers found: ", count, " friends already installed app");

			var foo = _load;

			if(count) {
				for(var i=0; i<data.length; ++i) {
					_installed.add(String(data[i]));
				}

				foo = function()
				{
					VK.api("users.get", {
						user_ids: data.slice(0,100).join(','),
						fields: USER_FIELDS
					}, function(data) {_onFriendsLoaded.call(undefined, data, false);});
				}
			}

			setTimeout(foo, FRIEND_CALL_DELAY);
		}

		// START HARE

		if(!_installed) {
			// load installed app friend ids first
			_installed = new Set;

			VK.api("friends.getAppUsers", {}, _onInstalledLoaded);
		}
		else {
			_load.call();
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

		VK.callMethod("showOrderBox", {
			type: "item",
			item: (amount) ? String(amount) : extra
		});
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

		VK.callMethod("showRequestBox", user_id, mess, (extra && extra.extra) ? extra.extra : "");
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
			VK.callMethod("showInviteBox");

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
//		VK.callMethod("resizeWindow", width, height);
	}

	/**
	 * @param Function callback
	 */
	isFavourites(callback)
	{
		VK.api("account.getAppPermissions", {}, function(data) {
			//console.log("wdlib.api.vk.Api::isFavourites result: ", data);
			var r = wdlib.utils.intval(data && data.response ? data.response : 0);
			callback.call(undefined, (r & VK_PERM_FAVOURITES) ? 1 : 0);
		});
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
			callback.call(undefined, (r & VK_PERM_FAVOURITES) ? 1 : 0);
		}
		
		VK.addCallback("onSettingsChanged", _handler);
		VK.addCallback("onSettingsCancel", _handler);
		VK.callMethod("showSettingsBox", VK_PERM_FAVOURITES);
	}

	/**
	 * @param String group_id
	 * @param String user_id
	 * @param Function callback
	 */
	isGroupMember(group_id, user_id, callback)
	{
		VK.api("groups.isMember", {group_id: group_id, user_id: user_id}, function(data) {
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
				VK.api("photos.getWallUploadServer", {}, function(data) {
					console.log("wdlib.api.vk.Api::photos.getWallUploadServer result: ", data);
					ok.call(undefined, data.response);
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
				VK.api("photos.saveWallPhoto", {user_id: user_id, photo: data.photo, hash: data.hash, server: data.server}, function(data) {
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
		VK.api("wall.post", {owner_id: user_id, message: mess, attachments: attach}, function(data) {
			console.log("wdlib.api.vk.Api::wall.post result: ", data);

			var post_id = 0;
			if(data.response && data.response.post_id) {
				post_id = data.response.post_id;
			}
			callback.call(undefined, post_id);
		});
	}
}

exports.ApiConnect = ApiConnect;
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

function _photosSort(a, b)
{
	return b.likes - a.likes;
}

}, (wdlib.api = wdlib.api || {}).vk = wdlib.api.vk || {});
