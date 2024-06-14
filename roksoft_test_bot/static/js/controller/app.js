wdlib.module("/js/controller/app.js", [
	"/js/config.js",
	"/js/controller/base.js",
	"/js/controller/app/user.js",
],
function(exports) {
"use strict";

// extend app.controller.App object from app.controller.Base
app.controller.Base.call(exports);

// ===============================================================================
// app.controller.App interface

exports.init = function()
{
	app.controller.app.User.init();

	var extra = wdlib.api.IApi.extra();

	var params = {
		api_user_id: app.Main.apiUser.api_user_id,
		user_data: {
			name: app.Main.apiUser.name,
			sex: app.Main.apiUser.sex,
			pic: app.Main.apiUser.pic,
		},
		extra: extra
	};

	console.log("app.controller.App.init : init params : ", params);

	app.controller.Main.server("app.main", "init", params, function(data) {
//		console.log("app.Main : SERVER INITED : ", data);

		if(data.hasOwnProperty("Config")) {
			app.Config.init(data["Config"]);
		}

		app.controller.App.run();
	}, undefined, {retry: 3});
}

exports.run = function()
{
	console.log("app.controller.App::run");

	var params = {
		extra: wdlib.api.IApi.extra()
	};

	app.controller.Main.server("app.main", "run", params, function(data) {
		subinit();
	}, undefined, {retry: 3});
}

exports.closeApp = function()
{
	wdlib.api.IApi.closeApp();
}

// ===============================================================================

// ===============================================================================
// internal functions

function subinit()
{
	app.view.App.init();

	// User контроллер, init был в controller.Main, но run вызываем тут
	// по идее, там ничего не делается и можно не вызывать - но, мало ли :-)
	app.controller.User.run();

	// стартуем long-poll здесь
	// для app версии
	// для web версии не буду пока запускать
	// app.controller.Poll.run();

	// какие-то, зависимые от app.controller.App контроллеры запускаем тут
	app.controller.app.User.run(); // это отдельный app.User контроллер, странички пользователя показать в приложении

	// check extra param
	// проверяем параметры запуска после #
	// для ВК вроде нормально работает из него через wdlib.Config.HASHVARS - я его в /js/main.js разбираю 
	var p = undefined;
	var extra = wdlib.api.IApi.extra();
	extra = extra || {};
//	if((p = wdlib.utils.object.isset(extra, "task-id"))) {
	//	DO-SOMETHING-HERE
//	}

//	app.view.Main.addPopup(app.view.popup.Alert, {title: "test alert", text: "some test alert text"});

	// poll test
//	app.controller.Main.server("app.main", "poll_echo");
}

// ===============================================================================

}, ((this.app.controller = this.app.controller || {})).App = {});

