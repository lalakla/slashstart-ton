wdlib.module("/js/model/app-config.js", [
	"wdlib/model/base.js",
	"wdlib/utils/object.js",
	"wdlib/utils/utils.js"
],
function(exports) {
"use strict";

// ============================================================================
// app.model.appConfig.Config class

class Config extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);

		this.insets = new app.model.appConfig.Insets;

		this.init(args);
	}

	/**
	 * @param Object args
	 */
	init(args)
	{
		this.insets.init(wdlib.utils.object.isset(args, "insets", {}));
	}

	toJSON()
	{
		return {
			insets: this.insets.toJSON()
		}
	}
}

exports.Config = Config;
// ============================================================================

// ============================================================================
// app.model.appConfig.Insets class

class Insets extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);

		this.top = 0;
		this.bottom = 0;
		this.left = 0;
		this.right = 0;

		this.init(args);
	}

	/**
	 * @param Object args
	 */
	init(args)
	{
		this.top = wdlib.utils.object.isset(args, "top", 0);
		this.bottom = wdlib.utils.object.isset(args, "bottom", 0);
		this.left = wdlib.utils.object.isset(args, "left", 0);
		this.right = wdlib.utils.object.isset(args, "right", 0);
	}
	
	toJSON()
	{
		return {
			top: this.top,
			bottom: this.bottom,
			left: this.left,
			right: this.right,
		}
	}

}

exports.Insets = Insets;
// ============================================================================

}, (this.app.model = this.app.model || {}).appConfig = {});
