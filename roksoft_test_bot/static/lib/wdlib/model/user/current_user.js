wdlib.module("wdlib/model/user/current_user.js", [
	"wdlib/model/user/user.js",
	"wdlib/model/wallet.js",
	"wdlib/utils/object.js"
],
function(exports) {
"use strict";

class CurrentUser extends wdlib.model.user.User {

	constructor(args)
	{
		super(args);

		this.experience = 0;
		this.days_running = 0;

		this.is_new_user_flag = 0;
		this.is_new_day_flag = 0;
		this.is_new_level_flag = 0;

		this.wallet = new wdlib.model.wallet.Wallet;

		this.inited = false;
	}
	init(args)
	{
		super.init(args);

		this.experience = args.experience || this.experience;
		this.days_running = args.days_running || this.days_running;

		this.is_new_user_flag = args.is_new_user_flag || this.is_new_user_flag;
		this.is_new_day_flag = args.is_new_day_flag || this.is_new_day_flag;
		this.is_new_level_flag = args.is_new_level_flag || this.is_new_level_flag;

		var w;
		if((w = wdlib.utils.object.isset(args, "wallet"))) {
			this.wallet.init(w);
		}
	}

	/**
	 * @return Boolean
	 */
	is_new_day()
	{
		return Boolean(this.is_new_day_flag);
	}
	/**
	 * @return Boolean
	 */
	is_new_user()
	{
		return Boolean(this.is_new_user_flag);
	}
	/**
	 * @return Boolean
	 */
	is_new_level()
	{
		return Boolean(this.is_new_level_flag);
	}
}

exports.CurrentUser = CurrentUser;

}, ((wdlib.model = wdlib.model || {}).user = wdlib.model.user || {}));
