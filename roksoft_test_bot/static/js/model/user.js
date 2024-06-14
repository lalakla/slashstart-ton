wdlib.module("/js/model/user.js", [
	"wdlib/model/base.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/string.js"
],
function(exports) {
"use strict";

var _users = new Map;

var PIC_TEST_PTRN = /^https{0,1}:\/\//i;

// ============================================================================
// app.model.user constants

exports.FLAG_CONFIRMED = 0x1;
exports.FLAG_ASSISTANT = 0x2;
exports.FLAG_MODER = 0x8;
exports.FLAG_ADMIN = 0x10;
exports.FLAG_SUPER_ADMIN = 0x20;
exports.FLAG_NOTIFY_ALLOWED = 0x40;

// ============================================================================
// app.model.user.User class

class User extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);

		this.id = "";
		this.name = "";
		this.sex = 0;
		this.age = 0;
		this.pic = wdlib.utils.appurl("/img/empty.png");
		this.flags = 0;
		this.level = 0;

		this.init(args);
	}

	/**
	 * @param Object args
	 */
	init(args)
	{
		this.id = String(args.id || this.id);
		this.name = String(args.name || this.name);
		this.sex = wdlib.utils.intval(args.sex || this.sex);
		this.age = wdlib.utils.intval(args.age || this.age);
		this.flags = wdlib.utils.intval(args.flags || this.flags);
		this.level = wdlib.utils.intval(args.level || this.level);

		if(args.pic && PIC_TEST_PTRN.test(args.pic)) {
			this.pic = args.pic;
		}
		// PHOTO hack
		this.pic = wdlib.utils.https(this.pic);
		if(this.pic.includes("&amp;")) {
			this.pic = wdlib.utils.string.htmlDecode(this.pic);
		}
	}
}

exports.User = User;
// ============================================================================

// ============================================================================
// app.model.user.ApiAuth class

class ApiAuth extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);

		this.platform = 0;
		this.api_user_id = "";
		this.user_id = "";
		this.is_app_user = 0;

		this.init(args);
	}

	/**
	 * @param Object args
	 */
	init(args)
	{
		this.platform = wdlib.utils.intval(args.platform || this.platform);
		this.api_user_id = String(args.api_user_id || this.api_user_id);
		this.user_id = String(args.user_id || this.user_id);
		this.is_app_user = wdlib.utils.intval(args.is_app_user || this.is_app_user);
	}
}

exports.ApiAuth = ApiAuth;
// ============================================================================

// ============================================================================
// app.model.user static functions

exports.getUser = function(user_id)
{
	var u = undefined;
	user_id = String(user_id);

	if(user_id == '0' || !user_id.length) {
		return u;
	}

	if(app.Main.currentUser && app.Main.currentUser.id == user_id) {
		u = app.Main.currentUser;
	}
	else {
		u = _users.get(user_id);
	}

	return u;
}

exports.setUser = function(user)
{
	_users.set(String(user.id), user);
}
 
// ============================================================================

}, (this.app.model = this.app.model || {}).user = {});
