wdlib.module("wdlib/model/wallet.js", [
	"wdlib/model/base.js",
	"wdlib/utils/object.js"
],
function(exports) {
"use strict";

exports.COINS = 0;
exports.ENERGY = 1;

class Wallet extends wdlib.model.Base {

	constructor(args)
	{
		super(args);

		this.items = {};
	}

	/**
	 * @param Array args
	 */
	init(args)
	{
		super.init(args);

		var w;
		for(var i=0; i<args.length; ++i) {
			if(!(w = wdlib.utils.object.isset(this.items, args[i][0]))) {
				w = new Item;
				w.type = args[i][0];
				this.items[w.type] = w;
			}
			w.init(args[i]);
		}
	}

	/**
	 * @param int type
	 * @return wdlib.model.wallet.Item
	 */
	get(type)
	{
		var w;
		if(!(w = wdlib.utils.object.isset(this.items, type))) {
			w = new Item;
			w.type = type;
			this.items[w.type] = w;
		}

		return w;
	}

	/**
	 * @param int type
	 * @param int amount
	 * @return bool
	 */
	stole(type, amount)
	{
		return this.get(type).stole(amount);
	}

	/**
	 * @param int type
	 * @param int amount
	 * @return bool
	 */
	check(type, amount)
	{
		return this.get(type).check(amount);
	}
}

class Item extends wdlib.model.Base {

	constructor(args)
	{
		super(args);

		this.type = 0;
		this.count = 0;
		this.max_count = 0;
	}

	/**
	 * @param Array args
	 */
	init(args)
	{
		this.type = args[0];
		this.count = args[1];
		this.max_count = args[2];
	}

	/**
	 * @param int amount
	 * @return bool
	 */
	stole(amount)
	{
		if(this.check(amount)) {
			this.count -= amount;
			return true;
		}
		return false;
	}

	/**
	 * @param int amount
	 * @return bool
	 */
	check(amount)
	{
		return (this.count >= amount);
	}
}

exports.Wallet = Wallet;
exports.Item = Item;

}, ((wdlib.model = wdlib.model || {}).wallet = wdlib.model.wallet || {}));
