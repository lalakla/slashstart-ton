wdlib.module("/js/controller/user.js", [
	"wdlib/api/base.js",
	"/js/controller/base.js",
	"/js/model/user.js",
],
function(exports) {
"use strict";

// extend app.controller.User object from app.controller.Base
app.controller.Base.call(exports);

// ===============================================================================
// app.controller.User interface

exports.init = function()
{
}
exports.run = function()
{
}

exports.isNotifyAllowed = function()
{
	return (app.Main.currentUser.flags & app.model.user.FLAG_NOTIFY_ALLOWED) ? true : false;
}

exports.notifyCheck = function()
{
	if((app.Main.currentUser.flags & app.model.user.FLAG_NOTIFY_ALLOWED)) {
		// notify allowed
		// all ok, do nothing
		return;
	}

	console.log("TIME TO ASK ALLOW NOTIFY");

//	wdlib.api.IApi.allowNotificationsDialog(function(res) {
//		app.controller.Main.server("vk.app.user", "notify-allow", {allow: res ? 1 : 0}, undefined, undefined, {retry: 3});
//	});
}

// ===============================================================================

// ===============================================================================
// internal functions 
// ===============================================================================

}, ((this.app.controller = this.app.controller || {})).User = {});
