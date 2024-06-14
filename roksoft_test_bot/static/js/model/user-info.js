wdlib.module("/js/model/user-info.js", [
	"wdlib/model/base.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/string.js"
],
function(exports) {
"use strict";

// ============================================================================
// app.model.userInfo constants

exports.USER_LAST_PLATFORM = 1;

// ============================================================================
// app.model.userInfo.UserInfo class

class UserInfo extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);

		this.last_platform = 0;

		this.init(args);
	}

	/**
	 * @param Object args
	 */
	init(args)
	{
		var p = undefined;

		this.last_platform = ((p = args[app.model.userInfo.USER_LAST_PLATFORM]) && p !== undefined) ? p : this.last_platform;
	}
}

exports.UserInfo = UserInfo;
// ============================================================================

}, (this.app.model = this.app.model || {}).userInfo = {});
