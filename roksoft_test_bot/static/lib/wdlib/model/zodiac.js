wdlib.module("wdlib/model/zodiac.js", [
	"wdlib/model/base.js",
	"wdlib/utils/utils.js",
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.model.zodiac constants

// ============================================================================
// wdlib.model.zodiac.Item class

class Item extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);
		
		this.id = 0;
		this.name = "";
		this.date_from = "";
		this.date_to = "";
		
		this.init(args);
	}
	/**
	 * @param Object args
	 */
	init(args)
	{
		this.id = wdlib.utils.intval(args.id || 0);
		this.name = args.name || "";
		this.date_from = args.date_from || "";
		this.date_to = args.date_to || "";
	}

	/**
	 * @return String
	 */
	getPicUrl()
	{
		var retval = wdlib.Config.APP_URL + "/img/zodiac/zodiac_" + String(this.id) + ".png";
		return retval;
	}
}

exports.Item = Item;
// ============================================================================

// ============================================================================
// wdlib.model.zodiac static functions

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
		if(routine(item)) {
			retval.push(item);
		}
	}

	return retval;
}
// ============================================================================

}, (wdlib.model = wdlib.model || {}).zodiac = {});
