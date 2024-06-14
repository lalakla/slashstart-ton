wdlib.module("wdlib/model/user/moder.js", [
	"wdlib/model/base.js",
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.model.user.MODER_ constants

exports.MODER_LEVEL_ROOT = 0;
exports.MODER_LEVEL_ADMIN = 10;
exports.MODER_LEVEL_MODER = 20;
exports.MODER_LEVEL_HELPER = 50;
exports.MODER_LEVEL_TESTER = 80;
exports.MODER_LEVEL_USER = 100;

// ============================================================================
// wdlib.model.user.Moder class

class Moder extends wdlib.model.Base {

	constructor(args)
	{
		super(args);

		this.user_id = "";
		this.level = 0;
		this.active = 0;
	}

	/**
	 * @param Object args
	 */	
	init(args)
	{
		this.user_id = args.user_id || this.user_id;
		this.level = args.level || this.level;
		this.active = args.active || this.active;
	}
}

exports.Moder = Moder;
// ============================================================================

}, ((wdlib.model = wdlib.model || {}).user = wdlib.model.user || {}));
