wdlib.module("/js/controller/main.js", [
	"wdlib/net/http2.js",
	"wdlib/model/error.js",
	"wdlib/utils/object.js",
	"/js/controller/base.js",
	"/js/controller/user.js",
	"/js/controller/client.js",
	"/js/controller/poll.js",
	"/js/model/user.js",
	"/js/model/user-info.js",
	"/js/view/popup/alert.js",
	"/js/events/client.js",
	"/js/events/server.js",
	"/js/config.js",
],
function(exports) {
"use strict";

// extend app.controller.Main object from app.controller.Base
app.controller.Base.call(exports);

var _user_info = new app.model.userInfo.UserInfo;

// ===============================================================================
// app.controller.Main interface

exports.init = function()
{
	app.view.Main.init();
	
	// init currentUser
	if(wdlib.Config.CURRENT_USER) {
		app.Main.currentUser = new app.model.user.User(wdlib.Config.CURRENT_USER);
	}
	// init apiAuth
	if(wdlib.Config.CURRENT_API_USER) {
		app.Main.apiAuth = new app.model.user.ApiAuth(wdlib.Config.CURRENT_API_USER);
	}

	app.controller.Poll.init();
	app.controller.Client.init();
	app.controller.User.init();
}

exports.getUserInfo = function()
{
	return _user_info;
}

/**
 * @param String controller
 * @param String method
 * @param Object params
 * @param Function callback
 * @param Object udata
 * @param Object options [optional, default=undefined 
 */
exports.server = function(controller, method, params, callback, udata, options)
{
	params = params || {};
	wdlib.net.Http2.send(controller, method, params, onServerOk, Object.assign({
		udata: {
			callback: callback,
			udata: udata
		},
	}, options));
}

/**
 * @param wdlib.model.Error error
 */
exports.error = function(error)
{
	app.view.Main.hideWaiter();

	if(!(error instanceof wdlib.model.Error)) {
		var _e = error;
		error = new wdlib.model.Error;
		error.error_code = _e;
	}

	switch(error.error_code) {
		case wdlib.model.error.ERROR_NOT_ENOUGH:
			console.log("app.controller.Main : ERROR : ERROR_NOT_ENOUGH");
			app.view.Main.addPopup(app.view.popup.Alert, {title: "Ошибка!", text: "У вас недостаточно средств!"});
			break;
		case wdlib.model.error.ERROR_ACCESS:
			console.log("app.controller.Main : ERROR : ERROR_ACCESS");
			app.view.Main.addPopup(app.view.popup.Alert, {title: "Ошибка доступа!", text: "У вас нет прав на это действие!"});
			break;
		case wdlib.model.error.ERROR_AUTH:
			// app.controller.Poll.stop();
			app.view.Main.addPopup(app.view.popup.Alert, {title: "Ошибка авторизации!", text: "Произошла ошибка авторизации в соц.сети! Пожалуйста, обновите страницу приложения!"});
			app.Main.sendError(error);
			break;
		case wdlib.model.error.ERROR_SIG:
			// app.controller.Poll.stop();
			app.view.Main.addPopup(app.view.popup.Alert, {title: "Ошибка подписи!", text: "Произошла ошибка подписи запроса! Возможно, вы открыли несколько вкладок с приложением!  Пожалуйста, оставьте только одну вкладку и обновите страницу!"});
			app.Main.sendError(error);
			break;
		case wdlib.model.error.ERROR_API:
			console.log("app.controller.Main : ERROR API CATCH : ", error);
			app.view.Main.addPopup(app.view.popup.Alert, {title: "Ошибка API!", text: "Произошла ошибка в запросе к АПИ соц.сети!"});
			app.Main.sendError(error);
			break;
		default:
			console.log("app.controller.Main : ERROR CATCH : ", error);
			app.view.Main.addPopup(app.view.popup.Alert, {title: "Ошибка!", text: "Произошла неизвестная ошибка!"});
			app.Main.sendError(error);
			break;
	}

//	app.controller.Main.dispatchEvent(new app.events.client.ClientEvent(app.events.client.ERROR, {error: error}));
}

/**
 * @param now [optional, default=Date.now()]
 * @return int
 */
exports.getServerTimestamp = function(now = undefined)
{
	return wdlib.net.Http2.getServerTimestamp(now);
}

exports.refresh = function()
{
	location.reload();
}
exports.redirect = function(url)
{
	wdlib.net.Http.redirect(url);
}

exports.closeApp = function()
{
	if(app.controller.App) {
		app.controller.app.closeApp();
	}
}

// ===============================================================================

// ===============================================================================
// internal functions

function onServerResponse(data)
{
	var i = 0;
	var p = undefined;

//	console.log("app.controller.Main.onServerResponse: ", data);

	if(data.User) {
		// создаем или обновляем currentUser'а
		if(!app.Main.currentUser) {
			app.Main.currentUser = new app.model.user.User(data.User);
		}
		else {
			app.Main.currentUser.init(data.User);
		}
		app.controller.Main.dispatchEvent(new app.events.server.Event(app.events.server.ON_USER_LOADED, {user: app.Main.currentUser}));
	}

	if(data.UserInfo) {
		_user_info.init(data.UserInfo);
	}

	if(data.Users && data.Users.length) {
		for(i=0; i<data.Users.length; ++i) {
			var u = new app.model.user.User;
			u.init(data.Users[i]);
			app.model.user.setUser(u);
		}
	}

	if(p = wdlib.utils.object.isset(data, "Error")) {
		app.controller.Main.error(new wdlib.model.Error(p));
	}
	
	app.controller.Main.dispatchEvent(new app.events.server.Event(app.events.server.ON_SERVER_RESPONSE, {data: data}));
}

function onServerOk(data, udata)
{
	data = data || {};

	try {
		onServerResponse(data);

		if(udata.callback) {
			udata.callback.apply(null, [data, udata.udata]);
		}
	}
	catch(e) {
		console.error("app.controller.Main : JS ERROR CATCH : ", e);

		var error = undefined;

		if(e instanceof wdlib.model.Error) {
			error = e;
			error.file = "app.controller.main.js";
		}
		else {
			error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR, stack: e.stack, file: e.fileName || "app.controller.main.js"});
		}

		app.controller.Main.error(error);
	}
}

// ===============================================================================

}, ((this.app.controller = this.app.controller || {})).Main = {});

