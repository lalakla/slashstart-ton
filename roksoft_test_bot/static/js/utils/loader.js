wdlib.module("/js/utils/loader.js", [
	"wdlib/utils/flex.js"
],
function(exports) {
"use strict";

// ===============================================================================
// class app.utils.Loader

class Loader extends wdlib.utils.FlexContainer {

	/**
	 * @param jQuery displayObject
	 */
	constructor(displayObject)
	{
		super(displayObject);
	}

	text(str)
	{
		$("p", this.displayObject).text(str);
	}
}

exports.Loader = Loader;
// ===============================================================================

}, ((this.app.utils = this.app.utils || {})));
