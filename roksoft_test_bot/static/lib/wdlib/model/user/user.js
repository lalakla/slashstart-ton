wdlib.module("wdlib/model/user/user.js", [
	"wdlib/model/base.js",
	"wdlib/api/base.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/string.js"
],
function(exports) {
"use strict";

var PIC_TEST_PTRN = /^https{0,1}:\/\//i;

// ============================================================================
// wdlib.model.user.User class

class User extends wdlib.model.Base {

	constructor(args)
	{
		super(args);

		this.id = "";
		this.remote_id = "";
		this.api_user_id = "";
		this.platform = 0;
		this.name = "";
		this.age = 0;
		this.birthday = "";
		this.sex = wdlib.api.MALE;
		this.pic = wdlib.utils.appurl("/img/empty.png");
		this.big_pic = wdlib.utils.appurl("/img/empty.png");
		this.city = "";
		this.anketa_link = "";
		this.level = 0;
		this.zodiac = 0;
		this.extra = {};

		this.is_app_user = 0;
		this.is_from_contact = false;
		this.is_from_search = false;
		this.is_online = false;
		this.is_bot = false;
		this.is_to_return = false;

		this.is_loaded = false;

		if(args) {
			this.init(args);
		}
	}

	/**
	 * @param Object args
	 */	
	init(args)
	{
		this.id = String(args.id || this.id);

		this.remote_id = String(args.remote_id || this.remote_id);
		this.api_user_id = String(args.api_user_id || this.api_user_id);
		if(!this.api_user_id && this.remote_id) {
			this.api_user_id = this.remote_id;
		}
		if(this.api_user_id) {
			this.remote_id = this.api_user_id;
		}

		this.platform = wdlib.utils.intval(args.platform || this.platform);
		this.name = args.name || this.name;
		this.age = wdlib.utils.intval(args.age);
		this.birthday = args.birthday || this.birthday;
		this.sex = wdlib.utils.intval(args.sex || this.sex);
		this.city = args.city || this.city;
		this.anketa_link = args.anketa_link || this.anketa_link;
		this.level = wdlib.utils.intval(args.level || this.level);
		this.zodiac = wdlib.utils.intval(args.zodiac || this.zodiac);
		this.is_app_user = wdlib.utils.intval(args.is_app_user || this.is_app_user);

		this.is_from_contact = args.is_from_contact ? true : false;
		this.is_from_search = args.is_from_search ? true : false;
		this.is_online = args.is_online ? true : false;
		this.is_bot = args.is_bot ? true : false;
		this.is_to_return = args.is_to_return ? true : false;

		if(args.pic && PIC_TEST_PTRN.test(args.pic)) {
			this.pic = args.pic;
		}
		else {
			this.pic = wdlib.utils.appurl("/img/" + (this.sex == wdlib.api.FEMALE ? "woman-shadow.png" : "man-shadow.png") + "");
		}
		if(args.big_pic && PIC_TEST_PTRN.test(args.big_pic)) {
			this.big_pic = args.big_pic;
		}
		else {
			this.big_pic = this.pic;
		}
		// this for older cpp version compatible
		if(args.bigPic && PIC_TEST_PTRN.test(args.bigPic)) {
			this.big_pic = args.bigPic;
		}

		// PHOTO hack
		this.pic = wdlib.utils.https(this.pic);
		if(this.pic.includes("&amp;")) {
			this.pic = wdlib.utils.string.htmlDecode(this.pic);
		}
		this.big_pic = wdlib.utils.https(this.big_pic);
		if(this.big_pic.includes("&amp;")) {
			this.big_pic = wdlib.utils.string.htmlDecode(this.big_pic);
		}

		this.extra = args.extra || {};
	}

	/**
	 * @return Boolean
	 */
	isMale()
	{
		return this.sex == wdlib.api.MALE;
	}
	/**
	 * @return Boolean
	 */
	isFemale()
	{
		return this.sex == wdlib.api.FEMALE;
	}

	/**
	 * @param wdlib.model.user.User user
	 * @return Boolean
	 */
	isEqual(user)
	{
		return this.platform == user.platform && this.api_user_id == user.api_user_id;
	}

	isEmpty()
	{
		return this.id == "" || this.id === "0" || this.id === 0;
	}
}

exports.User = User;
exports.currentUser = null;

// ============================================================================

var _users = new Map;
var _remote_users = new Map;

// ============================================================================
// wdlib.model.user static functions

exports.emptyUser = function(data)
{
	var user = new User(data);
	return user;
}

exports.getUser = function(user_id)
{
	var u = undefined;
	user_id = String(user_id);

	if(user_id == '0' || !user_id.length) {
		return u;
	}

	if(wdlib.model.user.currentUser && wdlib.model.user.currentUser.id == user_id) {
		u = wdlib.model.user.currentUser;
	}
	else {
		u = _users.get(user_id);
	}

	return u;
}
exports.getUserByRemoteId = function(platform, user_id)
{
	var u = undefined;
	user_id = String(user_id);

	if(user_id == '0' || !user_id.length) {
		return u;
	}

	if(wdlib.model.user.currentUser && wdlib.model.user.currentUser.platform == platform && wdlib.model.user.currentUser.api_user_id == user_id) {
		u = wdlib.model.user.currentUser;
	}
	else {
		var k = String(platform) + '_' + user_id;
		u = _remote_users.get(k);
	}

	return u;
}
exports.setUser = function(user)
{
	// --------------------------------------------------------------------------------
	// check if already loaded

	// check remote first
	var uu = wdlib.model.user.getUserByRemoteId(user.platform, user.api_user_id);
	if(uu) {
		user.is_app_user = (uu.is_app_user) ? uu.is_app_user : user.is_app_user;
		if(uu.id && uu.id.length && uu.id != '0' && (!user.id || !user.id.length || user.id == '0')) {
			user.id = uu.id;
		}
		user.is_from_contact = (uu.is_from_contact) ? uu.is_from_contact : user.is_from_contact;
		user.is_from_search = (uu.is_from_search) ? uu.is_from_search : user.is_from_search;
		user.is_online = (uu.is_online) ? uu.is_online : user.is_online;
		user.is_bot = (uu.is_bot) ? uu.is_bot : user.is_bot;
		user.is_to_return = (uu.is_to_return) ? uu.is_to_return : user.is_to_return;
		user.is_loaded = (uu.is_loaded) ? uu.is_loaded : user.is_loaded;
	}
	
	// check by internal 
	uu = wdlib.model.user.getUser(user.id);
	if(uu) {
		user.is_loaded = (uu.is_loaded) ? uu.is_loaded : user.is_loaded;
	}
	// --------------------------------------------------------------------------------

	//console.log("wdlib.model.user : setUser : ", user.id, user);

	_users.set(String(user.id), user);

	var k = String(user.platform) + '_' + String(user.api_user_id);
	_remote_users.set(k, user);
}

// ============================================================================

}, ((wdlib.model = wdlib.model || {}).user = wdlib.model.user || {}));
