wdlib.module("wdlib/button/surface.js", [
	"jquery",
	"wdlib/button/base.js"
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.button.Surface class

class Surface extends wdlib.button.Base {

	/**
	 * @param jQuery displayObject
	 * @param Object {context, data} args
	 * @param Function click
	 */
	constructor(displayObject, args, click)
	{
		super(displayObject, args, click);

		this._context = this.context;
		this.context = this;

		this._click = this.click;
		this.click = this.onClick;
	}

	onClick(btn, event)
	{
//		event.stopPropagation();
//		event.preventDefault();

		var target = $(event.target);
		this._click.call(this.context, this, target, event);
	}

}

exports.Surface = Surface;
// ============================================================================

}, (wdlib.button = wdlib.button || {}));
