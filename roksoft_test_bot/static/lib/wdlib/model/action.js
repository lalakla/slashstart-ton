wdlib.module("wdlib/model/action.js", [
	"wdlib/model/base.js"
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.model.action constants

exports.STATUS_OPEN = 0;
exports.STATUS_ACTIVE = 1;
exports.STATUS_CLOSE = 100;

// ============================================================================
// wdlib.model.action.Item class

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
		this.name = "";
		this.desc = "";
		this.data = {};
		this.udata = {};
		this.bonus = {};
		
		this.status = 0;
		this.date = 0;

		this.init(args);
	}
	/**
	 * @param Object args
	 */
	init(args)
	{
		this.id = wdlib.utils.intval(args.id || this.id);
		this.type = args.type || this.type;
		this.name = args.name || this.name;
		this.desc = args.desc || this.desc;
		this.data = args.data || this.data;
		this.udata = args.udata || this.udata;
		this.bonus = args.bonus || this.bonus;

		this.status = args.status || this.status;
		this.date = args.date || this.date;
	}
}

exports.Item = Item;
// ============================================================================

// ============================================================================
// wdlib.model.action.UserItem class

class UserItem extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);

		this.user_id = "";
		this.action_id = 0;
		this.status = 0;
		this.date = 0;
		this.data = {};

		this.init(args);
	}
	/**
	 * @param Object args
	 */
	init(args)
	{
		this.user_id = String(args.user_id || "");
		this.action_id = wdlib.utils.intval(args.action_id || 0);
		this.status = wdlib.utils.intval(args.status || 0);
		this.date = wdlib.utils.intval(args.date || 0);
		this.data = args.data || {};
	}
}

exports.UserItem = UserItem;
// ============================================================================

// ============================================================================
// wdlib.model.action static functions

var _map = {};

/**
 * @param Array data
 */
exports.init = function(data)
{
	for(var i=0; i<data.length; ++i) {
		var item = new Item;
		item.init(data[i]);
		_map[item.id] = item;
	}
}

/**
 * @param wdlib.model.action.Item
 */
exports.set = function(item)
{
	return _map[item.id] = item;
}

/**
 * @param int id
 * @return wdlib.model.action.Item
 */
exports.get = function(id)
{
	return _map[id];
}
// ============================================================================

}, (wdlib.model = wdlib.model || {}).action = {});
