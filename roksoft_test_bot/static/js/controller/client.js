wdlib.module("/js/controller/client.js", [
	"/js/controller/base.js",
	"/js/events/client.js"
],
function(exports) {
"use strict";

// extend app.controller.Client object from app.controller.Base
app.controller.Base.call(exports);

// ===============================================================================
// app.controller.Client interface

exports.init = function()
{
}

exports.run = function()
{
}

// ===============================================================================

}, ((this.app.controller = this.app.controller || {})).Client = {});
