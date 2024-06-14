wdlib.module("/js/controller/app/user.js", [
	"wdlib/utils/object.js",
	"/js/controller/base.js",
	"/js/model/user.js",
],
function(exports) {
"use strict";

// extend app.controller.app.User object from app.controller.Base
app.controller.Base.call(exports);

// ===============================================================================
// app.controller.app.User interface

exports.init = function()
{
}

exports.run = function()
{
}

exports.onUsersClick = function(user)
{
	app.controller.app.User.onUserClick(app.Main.currentUser);
}

exports.onUserClick = function(user)
{
	console.log("app.controller.app.User::onUserClick: ", user);
}

exports.loadUser = function(user, need_extended, callback)
{
	var params = {
		"user-id": user.id,
		extended: need_extended ? 1 : 0
	}
	app.controller.Main.server("app.user", "user", params, function(data) {
		console.log("app.console.app.User::loadUser : ", data);
		app.view.Main.hideWaiter();

		var _user = new app.model.user.getUser(user.id);

		var extended = wdlib.utils.object.isset(data, "extended", {});

		callback.call(undefined, _user, extended);
	});
	app.view.Main.showWaiter();
}

// ===============================================================================

}, ((this.app.controller.app = this.app.controller.app || {})).User = {});
