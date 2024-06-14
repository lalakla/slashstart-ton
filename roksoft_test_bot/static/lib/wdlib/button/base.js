wdlib.module("wdlib/button/base.js", [
	"jquery",
	"wdlib/model/disposable.js"
],
function(exports) {
"use strict";

class Base extends wdlib.model.Disposable {

	/**
	 * @param jQuery displayObject
	 * @param Object {context, data} args
	 * @param Function click
	 */
	constructor(displayObject, args, click)
	{
		super();

		args = args || {};

		this.displayObject = displayObject;
		this.data = args.data || {};
		this.context = args.context;
		this.click = click;

		this._enabled = true;

		var self = this;
		this.displayObject.on("click", function(event) {
			self.click.call(self.context, self, event);
		});
	}

	/**
	 * @param Boolean val [optional]
	 * @return Boolean
	 */
	enabled()
	{
		if(arguments.length) {
			var _enabled = arguments[0];

			if(this._enabled != _enabled) {
				this._enabled = _enabled;
			}

			if(!this._enabled && !(this.displayObject.hasClass("disabled"))) {
				this.displayObject.addClass("disabled");
			}
			if(this._enabled && this.displayObject.hasClass("disabled")) {
				this.displayObject.removeClass("disabled");
			}
			if(!this._enabled) {
				this.displayObject.blur(); // move focus out from button
			}
		}

		return this._enabled;
	}

	dispose()
	{
		super.dispose();

		this.displayObject.off("click");
	}

}

exports.Base = Base;

}, (wdlib.button = wdlib.button || {}));
