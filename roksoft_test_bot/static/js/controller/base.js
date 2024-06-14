wdlib.module("/js/controller/base.js", [
	"wdlib/events/dispatcher.js",
	"wdlib/model/error.js"
],
function(exports) {
"use strict";

// ===============================================================================
// app.controller.Base class

exports.Base = function()
{
	// extend app.controller.Base object from wdlib.events.EventDispatcher
	wdlib.events.EventDispatcher.call(this);

	// base dummy INIT function for controllers
	this.init = function()
	{
	}
	// base dummy RUN function for controllers
	this.run = function()
	{
	}
}
// ===============================================================================

}, (this.app.controller = this.app.controller || {}));
