wdlib.module("wdlib/model/disposable.js", [
],
function(exports) {
"use strict";

class Disposable {

	constructor()
	{
		this.to_dispose = [];
	}

	/**
	 * @param wdlib.model.Disposable item
	 */
	addDisposable(item)
	{
		this.to_dispose.push(item);
	}
	
	dispose()
	{
		for(var i=0; i<this.to_dispose.length; ++i) {
			this.to_dispose[i].dispose();
		}
		this.to_dispose = [];
	}
}

exports.Disposable = Disposable;

}, (wdlib.model = wdlib.model || {}));
