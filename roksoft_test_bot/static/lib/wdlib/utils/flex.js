wdlib.module("wdlib/utils/flex.js", [
	"wdlib/model/disposable.js"
],
function(exports) {
"use strict";

// ===============================================================================
// class wdlib.utils.FlexContainer

class FlexContainer extends wdlib.model.Disposable {

	/**
	 * @param jQuery displayObject
	 */
	constructor(displayObject)
	{
		super();
		
		this.displayObject = displayObject;
	}

	remove()
	{
		if(this.displayObject) {
			this.displayObject.remove();
		}
	}
	empty()
	{
		if(this.displayObject) {
			this.displayObject.empty();
		}
	}

	show()
	{
		this.displayObject.css("display", "flex");
	}

	hide()
	{
		this.displayObject.css("display", "none");
	}
}

exports.FlexContainer = FlexContainer;
// ===============================================================================

}, ((wdlib.utils = wdlib.utils || {})));
