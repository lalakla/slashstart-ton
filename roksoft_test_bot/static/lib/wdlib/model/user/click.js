wdlib.module("wdlib/model/user/click.js", [
	"wdlib/model/base.js",
],
function(exports) {
"use strict";

class Click extends wdlib.model.Base {

	constructor(args)
	{
		args = args || {};
		super(args);

		this.user_id = "";
		this.user_click_id = "";
		this.value = 0;
		this.date = 0;

		this.init(args);
	}

	/**
	 * @param Object args
	 */	
	init(args)
	{
		this.user_id = args.user_id || this.user_id;
		this.user_click_id = args.user_click_id || this.user_click_id;
		this.value = args.value || this.value;
		this.date = args.date || this.date;
	}
}
exports.Click = Click;

}, ((wdlib.model = wdlib.model || {}).user = wdlib.model.user || {}));
