wdlib.module("wdlib/model/user/banned.js", [
	"wdlib/model/base.js",
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.model.user.BANNED_ constants

exports.BANNED_STATUS_FREE = 0;
exports.BANNED_STATUS_TMP_CHAT_BAN = 2;
exports.BANNED_STATUS_PMNT_CHAT_BAN = 10;
exports.BANNED_STATUS_DISABLE = 100;

// ============================================================================
// wdlib.model.user.Banned class

class Banned extends wdlib.model.Base {

	constructor(args)
	{
		super(args);

		this.user_id = "";
		this.moder_id = "";
		this.status = 0;
		this.date = 0;
		this.comment = "";
	}

	/**
	 * @param Object args
	 */	
	init(args)
	{
		this.user_id = args.user_id || this.user_id;
		this.moder_id = args.moder_id || this.moder_id;
		this.status = args.status || this.status;
		this.date = args.date || this.date;
		this.comment = args.comment || this.comment;
	}
}

exports.Banned = Banned;
// ============================================================================

}, ((wdlib.model = wdlib.model || {}).user = wdlib.model.user || {}));
