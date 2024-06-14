wdlib.module("wdlib/api/vk/base.js", [
	"wdlib/api/base.js",
	"wdlib/net/http.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/object.js"
],
function(exports) {
"use strict";

// PLATFORM CONSTANTS
exports.FRIEND_CALL_DELAY = 1000;
exports.USER_FIELDS = "photo_50,photo_100,photo_200,photo_200_orig,photo_400,photo_400_orig,photo_max,photo_max_orig,sex,bdate,has_photo,city,has_mobile,verified,online,is_friend";
exports.API_VERSION = "5.199";
exports.VK_URL = "https://vk.com";

exports.VK_PERM_NOTIFY = 1;
exports.VK_PERM_FRIENDS = 2;
exports.VK_PERM_PHOTOS = 4;
exports.VK_PERM_FAVOURITES = 256;

exports.VK_SCOPE_FRIENDS = "friends";
exports.VK_SCOPE_PHOTOS = "photos";

exports.VK_ERROR_ACCESS_TOKEN = 5;

var _users = new Map;
var _installed = undefined;

// ============================================================================
// wdlib.api.vk.Base class

class Base extends wdlib.api.Base {

	constructor(args)
	{
		super(args);
		
		this.partner_url = wdlib.api.vk.VK_URL;
		this.auth_key = args.auth_key || "";
		this.app_id = args.api_id || args.vk_app_id || "";
		this.viewer_id = args.viewer_id || args.vk_user_id || this.viewer_id;

		this.extra_param = args.hash || args.request_key || args.referrer || "";
		if(this.extra_param) {
			this.extra_param = this.extra_param.replace(/^ad_/, "");
		}
	}

	/**
	 * @param Object|String extra [optional, default = undefined]
	 * @return String
	 */
	appurl(extra = undefined)
	{
		var url = this.partner_url + "/app" + this.app_id;

		if(extra !== undefined) {
			if(!(typeof extra === "object")) {
				url += "?ad_id=" + extra;
			}
			else {
				url += "#" + wdlib.net.Http.encode(extra);
			}
		}

		return url;
	}

	fixurl(url)
	{
		const isMobile = !(this.api_args.platform === "web");

		if (isMobile) {
			return url.replace(/https:\/\/vk.com/g, 'https://m.vk.com');
		}

		return url;
	}

	fixurls(text)
	{
		const isMobile = !(this.api_args.platform === "web");
	
		let result = text.replace(/<a([^>]*)href/g, '<a$1 target="_blank" href');

		if(isMobile) {
			result = result.replace(/https:\/\/vk.com/g, 'https://m.vk.com');
		}

		return result;
	}

	promo_getInitialState(callback, on_error_callback = undefined)
	{
		var self = this;

		var test_history = [
			/*
			{
				"timestamp": 1701271001,
				"type": 3,
				"actor_id": 55,
				"value": 5,
				"text": "29.11 18:41 +5 за задание «Совершите платёж в игре»"
			},
			{
				"timestamp": 1701271029,
				"type": 3,
				"actor_id": 41,
				"value": 5,
				"text": "29.11 18:09 +5 за задание «Драконье логово»"
			},
			{
				"timestamp": 1701271256,
				"type": 3,
				"actor_id": 41,
				"value": 5,
				"text": "29.11 18:56 +5 за задание «Драконье логово»"
			},
			*/
		];

		this._call("gamesPromo.ny2024GetInitialState", {
			platform: this.api_args.platform
		}, function(data) {
			console.log("wdlib.api.vk.Base::promo_getInitialState result: ", data);
			
			data = data.response || {};

			let quests = wdlib.utils.object.isset(data, "quests", []);
			quests.forEach(function(item) {
				item.href = self.fixurl(item.href);
			});
			let history = wdlib.utils.object.isset(data, "history", []);
			history = history.length ? history : test_history;

			let lang_configs = wdlib.utils.object.isset(data, "lang_configs", []);
			lang_configs.forEach(function(item) {
				item.value = self.fixurls(item.value);
			});

			callback.call(undefined, quests, history, lang_configs);
		}, function() {
			callback.call(undefined, []);
		});
	}

	promo_getIntegrationEvents(callback, on_error_callback = undefined)
	{
		this._call("apps.getIntegrationEvents", {
			// no params
		}, function(data) {
//			console.log("wdlib.api.vk.Base::promo_getIntegrationEvents result: ", data);
			
			data = wdlib.utils.object.isset(data, "response", {});
			data = wdlib.utils.object.isset(data, "items", []);
			
			callback.call(undefined, data);
		}, function(error) {
			console.log("wdlib.api.vk.Base::promo_getIntegrationEvents error: ", error);
			callback.call(undefined, []);
		});
	}

	storageGet(keys, callback)
	{
		keys = Array.isArray(keys) ? keys : [keys];
		var params = {
			keys: keys,
		}
		this._send("VKWebAppStorageGet", params, function(data) {
//			console.log("wdlib.api.vk.Base::storageGet result: ", data);
			data = wdlib.utils.object.isset(data, "keys", []).map(function(item) {
				let res = item;
				try {
					res.value = JSON.parse(item.value);
				}
				catch(e) {
					res.value = undefined;
				}

				return res;
			});
			callback.call(undefined, data);
		}, function(error) {
			console.log("wdlib.api.vk.Base::storageGet error: ", error);
			callback.call(undefined, [])
		});
	}

	storageSet(key, value)
	{
		var params = {
			key: key,
			value: JSON.stringify(value)
		}
		this._send("VKWebAppStorageSet", params, function(data) {
//			console.log("wdlib.api.vk.Base::storageSet result: ", data);
		}, function(error) {
			console.log("wdlib.api.vk.Base::storageSet error: ", error);
		});
	}

	sendCustomEvent(eventName, json = undefined)
	{
		var params = {
			event: eventName
		};
		if(json !== undefined) {
			params.json = JSON.stringify(json);
		}
		this._send("VKWebAppSendCustomEvent", params, function(data) {
//			console.log("wdlib.api.vk.Base::sendCustomEvent result: ", data);
		}, function(error) {
			console.log("wdlib.api.vk.Base::sendCustomEvent error: ", error);
		});
	}

	/**
	 * @param callback
	 * @params String scope1, scope2, scope3, ...
	 */
	checkAllowedScopes()
	{
		let args = Array.prototype.slice.call(arguments);
		let callback = args.shift();
		let scopes = args.join(',');

		let allowed = true;

		if(!this.access_token || this.access_token == "") {
			// access_token'а НЕТ
			// соответственно и разрешено ничего не может быть
			// нужно в любом случае этот самый токен будет получить
			callback.call(undefined, false);
			return;
		}
	
//		console.log("wdlib.api.vk.Base::checkAllowedScopes scopes: ", scopes);
		
		this._send("VKWebAppCheckAllowedScopes", {scopes: scopes}, function(data) {
			console.log("wdlib.api.vk.Base::checkAllowedScopes result: ", data);
			if(data.result) {
				data.result.forEach(function(item) {
					allowed = allowed && item.allowed;
				});
			}
			else {
				allowed = false;
			}
			callback.call(undefined, allowed);
		}, function(error) {
			console.error("wdlib.api.vk.Base::checkAllowedScopes error: ", error);

			allowed = false;
			callback.call(undefined, allowed);
		});
	}

	getProfile(user_id, callback, on_error_callback = undefined)
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
			api_user_id: user_id,
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

//		this._call("users.get", {
//			uids: user_id,
//			fields: wdlib.api.vk.USER_FIELDS
		this._send("VKWebAppGetUserInfo", {
			user_id: wdlib.utils.intval(user_id)
		}, function(data) {
			console.log("wdlib.api.vk.Base::getProfile result: ", data);
	
			if(data.hasOwnProperty("response")) {
				data = data.response;
			}
			if(data.hasOwnProperty("items")) {
				data = data.items;
			}

			data = Array.isArray(data) ? data[0] : data;

//			console.log("wdlib.api.vk.Base::getProfile result.data: ", data);

//			if(data && data.response && data.response[0]) {
//				data = data.response[0];
				user.name = data.first_name;
				user.sex = data.sex || user.sex;
//				user.pic = data.photo_100 || user.pic;
//				user.big_pic = data.photo_200_orig || user.big_pic;
				user.pic = data.photo_100 || data.photo_200 ||user.pic;
				user.big_pic = data.photo_max_orig || user.big_pic;
				user.birthday = data.bdate || user.birthday;
				user.has_mobile = data.has_mobile || user.has_mobile;
				user.is_app_user = (_installed && _installed.has(user_id)) ? 1 : 0;
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
				user.extra = data;

				wdlib.api.vk.setUserAge(user);

				_users.set(user.api_user_id, user);
//			}

			callback.call(null, user);
		}, on_error_callback);
	}
	
	getProfiles(user_ids, callback, on_error_callback = undefined)
	{
		var self = this;
		var retval = [];

		// cache check first
		user_ids = user_ids.filter(function(uid) {
			uid = String(uid);
			let user = _users.get(uid);
			if(user) {
				retval.push(user);
				return false;
			}

			return true;
		});

		if(!user_ids.length) {
			// all users found in cache
			callback.call(undefined, retval);
			return;
		}

		this._call("users.get", {
			user_ids: user_ids.join(','),
			fields: wdlib.api.vk.USER_FIELDS
		}, function(data) {
//			console.log("wdlib.api.vk.Base::getProfiles result: ", data);

			data = data.response || [];
			if(data.hasOwnProperty("items")) {
				data = data.items;
			}

//			var count = data ? data.length : 0;
//			console.log("wdlib.api.vk.Base::getProfiles found: ", count, " items");

			for(var i=0; i<data.length; ++i) {
				var user_id = String(data[i]["id"]);
				
				if(data[i]["deactivated"]) {
					// user removed or blocked
					// do not show
					continue;
				}
	
				var user = {
					api_user_id: user_id,
					platform: wdlib.api.API_VK,
					name: data[i]["first_name"],
					sex: data[i]["sex"] || 0,
					pic: data[i]["photo_200_orig"],
					big_pic: data[i]["photo_max_orig"],
					age: 0,
					birthday: data[i]["bdate"],
					city: wdlib.api.DEFAULT_CITY,
					zodiac_name: "",
					is_app_user: (_installed && _installed.has(user_id)) ? 1 : 0,
					anketa_link: self.partner_url + "/id" + user_id,
					is_from_contact: (data[i].is_friend) ? 1 : 0,
					is_online: data[i]["online"] || 0,
					extra: data[i]
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

				_users.set(user.api_user_id, user);
				retval.push(user);
			}
			
			callback.call(undefined, retval);
		}, on_error_callback);
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
//			console.log("wdlib.api.vk.Base::getUserPhotos result: ", data);

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

		var _onFriendsLoaded = function(data, check_zero = true, once = false)
		{
//			console.log("wdlib.api.vk.Base::_onFriendsLoaded result: ", data);
			
			data = data.response || [];

			if(data.hasOwnProperty("items")) {
				data = data.items;
			}

			var count = data ? data.length : 0;
			console.log("wdlib.api.vk.Base::_onFriendsLoaded found: ", count, " friends, offset: ", offset, ", once: ", once);

			if(check_zero) {
				offset += count;
			}

			for(var i=0; i<data.length; ++i) {
				var user_id = String(data[i]["id"]);

				if(data[i]["deactivated"]) {
					// user removed or blocked
					// do not show
					continue;
				}

				var user = {
					remote_id: user_id,
					platform: wdlib.api.API_VK,
					name: data[i]["first_name"],
					sex: data[i]["sex"] || 0,
					pic: data[i]["photo_200_orig"],
					big_pic: data[i]["photo_max_orig"],
					age: 0,
					birthday: data[i]["bdate"],
					city: wdlib.api.DEFAULT_CITY,
					zodiac_name: "",
					is_app_user: (_installed.has(user_id) ? 1 : 0),
					anketa_link: self.partner_url + "/id" + user_id,
					is_from_contact: 1,
					is_online: data[i]["online"] || 0,
					extra: data[i]
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

				_users.set(user.api_user_id, user);
				retval.push(user);
			}

			if(once || (check_zero && (!count || retval.length >= limit))) {
				// all friends loaded
				//wdlib.utils.object.shuffle(retval);
				callback.call(undefined, retval);
			}
			else {
				//setTimeout(_load, wdlib.api.vk.FRIEND_CALL_DELAY);
				_load.call(undefined);
			}
		}

		var _onInstalledLoaded = function(data)
		{
//			console.log("wdlib.api.vk.Base::friends.getAppUsers result: ", data);

			data = data.response || [];

			var count = data ? data.length : 0;

//			console.log("wdlib.api.vk.Api::friends.getAppUsers found: ", count, " friends already installed app", data);

			var foo = _load;

			if(count) {
				data.forEach(function(user_id) {
					_installed.add(String(user_id));
				});

				foo = function()
				{
					/*
					self._call("friends.get", {
						fields: wdlib.api.vk.USER_FIELDS,
						count: 1000,
						order: "hints",
					}, function(data) {
						_onFriendsLoaded.call(undefined, data, false, true);
					});
					*/

//					/*
					self._call("users.get", {
						user_ids: data.slice(0,999).join(','),
						fields: wdlib.api.vk.USER_FIELDS
					}, function(data) {
//						console.log("wdlib.api.vk.Api::friends.getAppUsers : ", data);
						_onFriendsLoaded.call(undefined, data, false, true);
					});
//					*/
				}
			}

//			setTimeout(foo, wdlib.api.vk.FRIEND_CALL_DELAY);
			foo.call(undefined);
		}

		// START HERE

		if(!_installed) {
			// load installed app friend ids first
			_installed = new Set;

			this._call("friends.getAppUsers", {}, _onInstalledLoaded);
		}
		else {
			_load.call();
		}
	}

	/**
	 * @param Function callback
	 */
	isFavourites(callback)
	{
		this._call("account.getAppPermissions", {}, function(data) {
//			console.log("wdlib.api.vk.Base::isFavourites result: ", data);
			var r = wdlib.utils.intval(data && data.response ? data.response : 0);
			callback.call(undefined, (r & wdlib.api.vk.VK_PERM_FAVOURITES) ? 1 : 0);
		});
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
				upload_url: data.upload_url,
				album_id: data.album_id,
				user_id: data.user_id
			});
		}

		switch(type) {
			case "wall":
			default:
				this._call("photos.getWallUploadServer", {}, function(data) {
					console.log("wdlib.api.vk.Base::photos.getWallUploadServer result: ", data);
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
					console.log("wdlib.api.vk.Base::photos.saveWallPhoto result: ", data);
					ok.call(undefined, data.response);
				}, function(err) {
//					console.log("wdlib.api.vk.Base::photos.saveWallPhoto error: ", err);
					ok.call(undefined, false);
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
		var params = {
			owner_id: wdlib.utils.intval(user_id),
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
		
		console.log("wdlib.api.vk.Base::wall.post params: ", params);
		
		this._call("wall.post", params, function(data) {
			console.log("wdlib.api.vk.Base::wall.post result: ", data);

			var post_id = 0;
			if(data.response && data.response.post_id) {
				post_id = data.response.post_id;
			}
			callback.call(undefined, post_id);
		}, function(err) {
			console.log("wdlib.api.vk.Base::wall.post error: ", err);
			callback.call(undefined, 0);
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
			//console.log("wdlib.api.vk.Base::isLiked result: ", data);

			callback.call(undefined, (data && data.response) ? data.response : {});
		});
	}

	/**
	 * @param int counter
	 * @param Boolean increment [optional, default=false]
	 */
	setCounter(counter, increment = false)
	{
		this._call("setCounter", {counter: counter, increment: (increment ? 1 : 0)}, function(data) {
			console.log("wdlib.api.vk.Base.setCounter result: ", data);
		});
	}
}

exports.Base = Base;

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

exports.photosSort = function(a, b)
{
	return b.likes - a.likes;
}

// ============================================================================
}, (wdlib.api = wdlib.api || {}).vk = wdlib.api.vk || {});
