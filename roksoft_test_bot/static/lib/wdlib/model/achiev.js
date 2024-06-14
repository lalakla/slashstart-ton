wdlib.module("wdlib/model/achiev.js", [
	"wdlib/model/base.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/math.js"
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.model.achiev constants

exports.STATUS_OPEN = 0;
exports.STATUS_AWARD = 10;
exports.STATUS_CLOSE = 100;

exports.TYPE_DEFAULT = 0;
exports.TYPE_HIDDEN = 1;
exports.TYPE_DAILY = 2;

// ============================================================================
// wdlib.model.achiev.Item class

class Item extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);
		
		this.id = 0;
		this.type = 0;
		this.active = 0;
		this.name = "";
		this.desc = "";
		this.data = {};
		this.award = {};
		
		this.init(args);
	}
	/**
	 * @param Object args
	 */
	init(args)
	{
		this.id = wdlib.utils.intval(args.id || 0);
		this.type = wdlib.utils.intval(args.type || 0);
		this.active = wdlib.utils.intval(args.active || 0);
		this.name = args.name || "";
		this.desc = args.desc || "";
		this.data = args.data || {};
		this.award = args.award || {};
	}

	/**
	 * @param int level
	 * @return int
	 */
	getMax(level)
	{
		var retval = 0;

		var formula = wdlib.utils.object.isset(this.data, "level");
		if(formula) {
			retval = wdlib.utils.math.execute(formula, {level: level});
			retval = Math.round(retval);
		}

		return retval;
	}

	/**
	 * @return String
	 */
	getPicUrl()
	{
		var retval = wdlib.Config.APP_URL + "/img/achievs/achiev_" + String(this.id) + ".png";
		return retval;
	}
	/**
	 * @return String
	 */
	getBigPicUrl()
	{
		var retval = wdlib.Config.APP_URL + "/img/achievs/achiev_" + String(this.id) + "_big.png";
		return retval;
	}
}

exports.Item = Item;
// ============================================================================

// ============================================================================
// wdlib.model.achiev.UserItem class

class UserItem extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);

		this.user_id = "";
		this.achiev_id = 0;
		this.status = 0;
		this.level = 0;
		this.count = 0;

		this.is_new_level = 0;

		this.achiev = undefined;
		this.max = 0;

		this.init(args);
	}
	/**
	 * @param Object args
	 */
	init(args)
	{
		this.user_id = String(args.user_id || "");
		this.achiev_id = wdlib.utils.intval(args.achiev_id || 0);
		this.status = wdlib.utils.intval(args.status || 0);
		this.level = wdlib.utils.intval(args.level || 0);
		this.count = wdlib.utils.intval(args.count || 0);
		
		this.is_new_level = wdlib.utils.intval(args.is_new_level || 0);

		this.achiev = undefined;
		this.max = 0;
	}

	getPercent()
	{
		var max = this.getMax();
		var percent = Math.floor((this.count / max) * 100);
		return percent;
	}

	getMax()
	{
		if(this.max) {
			return this.max;
		}

		var retval = 0;

		this.achiev = this.achiev || wdlib.model.achiev.get(this.achiev_id);
		if(this.achiev) {
			retval = this.achiev.getMax(this.level + 1);
		}

		this.max = retval;

		return retval;
	}
}

exports.UserItem = UserItem;
// ============================================================================

// ============================================================================
// wdlib.model.achiev static functions

var _map = new Map;

/**
 * @param Array data
 */
exports.init = function(data)
{
	for(var i=0; i<data.length; ++i) {
		var item = new Item(data[i]);
		_map.set(item.id, item);
	}
}

/**
 * @param int id
 * @return wdlib.model.achiev.Item
 */
exports.get = function(id)
{
	id = wdlib.utils.intval(id);
	return _map.get(id);
}

/**
 * selects a list of active achievs by user function
 * @param Function routine
 * @return Array<wdlib.model.achiev.Item>
 */
exports.select = function(routine)
{
	var retval = [];

	for(var it of _map) {
		var item = it[1];
		if(item.active && routine(item)) {
			retval.push(item);
		}
	}

	return retval;
}
// ============================================================================

}, (wdlib.model = wdlib.model || {}).achiev = {});
