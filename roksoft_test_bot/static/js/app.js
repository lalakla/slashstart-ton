wdlib.module("/js/app.js", [
	"wdlib/net/http.js",
	"wdlib/net/http2.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/object.js",
	"wdlib/api/vk/app.js",
	"wdlib/api/local.js",
	"wdlib/api/telegram.js",
	"wdlib/model/user/user.js",
	"/js/config.js",
	"/js/model/app-config.js",
],
function(exports) {
"use strict";

var _js_loaded = false;
var _api_inited = false;
var _session_inited = false;
var _to_refresh = false;
var _app_runned = false;
	
var _app_config = undefined;
var _app_config_stored = undefined;

// ============================================================================	
// app.App interface

exports.getAppConfig = function()
{
	return _app_config ? _app_config : _app_config_stored;
}

// ============================================================================	

// ============================================================================	
// App internal functions

function localStart()
{
	var users = wdlib.api.local.getTestUsers(40);

	wdlib.load([
		"/js/controller/app.js",
		"/js/view/app.js",
		"/js/view/popup/local-user.js"
	], function() {
		app.view.Main.addPopup(app.view.popup.LocalUser, {users: users, callback: localContinue});
//		localContinue(users[0]);
		_js_loaded = true;
	});

}
function localContinue(user)
{
//	console.log("LOCAL CONTINUE WITH : ", user);

	wdlib.Config.CURRENT_API_USER_ID = user.api_user_id;
	wdlib.net.Http2.init(wdlib.Config.CURRENT_API_USER_ID, wdlib.Config.CURRENT_API, onHttpError, {
		sess_token: wdlib.Config.SESS_TOKEN
	});

	// send stats
	app.Main.statsReport("start");

	wdlib.Config.FLASHVARS["viewer_id"] = user.api_user_id;

	// HERE TEST OF possible EXTRA PARAM
	var extra = undefined;
//	extra = "room::room::2";
//	extra = "action::fs::14"; // тестируем акцию на фс
//	extra = "group::wallpost::26";
	wdlib.Config.FLASHVARS["extra"] = extra;

	wdlib.api.IApi = new wdlib.api.local.LocalApi(wdlib.Config.FLASHVARS);

	wdlib.api.IApi.init(onApiInited, onApiError, onApiEvent);
}

function apiRun()
{
	switch(wdlib.Config.CURRENT_API) {
		case wdlib.api.API_VK:
			console.log("using VK API");
			wdlib.api.IApi = new wdlib.api.vk.App(wdlib.Config.FLASHVARS);
			break;
		case wdlib.api.API_TELEGRAM:
			console.log("using TELEGRAM API");
			wdlib.api.IApi = new wdlib.api.telegram.Api(wdlib.Config.FLASHVARS);
			break;
	}

	wdlib.api.IApi.init(onApiInited, onApiError, onApiEvent);

	wdlib.load([
		"/js/controller/app.js",
		"/js/view/app.js"
	], function() {
		_js_loaded = true;
		checkRun();
	});
}

function onApiInited(data, auth_params = undefined)
{
	if(_api_inited) {
		console.error("FUCKING ERROR !!! SECOND API INITED call");

//		var error = new wdlib.model.Error({error: "onApiInited MORE THEN ONE CALL!!!, data: " + JSON.stringify(data), error_code: wdlib.model.error.ERROR});
//		error.file = "main.js";
//
//		app.Main.sendError(error);
		return;
	}

	console.log("app.App : API INITED");

	_api_inited = true;

	// send stats
	app.Main.statsReport("api_init");

	// init apiUser
	app.Main.apiUser = new wdlib.model.user.User(data);
	console.log("app.Main.apiUser: ", app.Main.apiUser);

	wdlib.Config.CURRENT_API_USER_ID = app.Main.apiUser.api_user_id;

	if(wdlib.api.isApi(wdlib.api.API_VK)) {
		// это конструкция для ВК
//		wdlib.Config.IS_MOBILE = !(wdlib.api.IApi.api_args.platform === "web");
		wdlib.Config.IS_MOBILE = !(wdlib.api.IApi.api_args.vk_platform === "desktop_web");
		wdlib.Config.IS_IOS = (wdlib.api.IApi.api_args.platform === "html5_ios");

		// достаем сохраненный конфиг
		let key = "app_config_" + (wdlib.api.IApi.api_args.platform);
		wdlib.api.IApi.storageGet(key, function(data) {
//			console.log("APP-CONFIG STORED : ", data);

			for(var i=0; i<data.length; ++i) {
				if(data[i].key == key) {
					_app_config_stored = new app.model.appConfig.Config(data[i].value);
					app.Main.logReport("APP-CONFIG STORED UPDATED : " + JSON.stringify(_app_config_stored));
					if(!_app_config) {
						_app_config = _app_config_stored;
						app.Main.logReport("APP-CONFIG UPDATED : from stored : " + JSON.stringify(_app_config));
					}
					break;
				}
			}
			checkRun();
		});
	}
	else {
		// app_config пока только для ВК умеет вычисляться
		// там отступы задаются для iPhone'ов
		// а для остальных просто дефолтный сделаю
		// т.к. он потом проверяется в checkRun
		_app_config = new app.model.appConfig.Config;
	}

	// сессии нет, мы загрузились с html странички!!
	initSession(auth_params);
}

function initSession(auth_params = undefined)
{
	var params = {
		api: wdlib.Config.CURRENT_API,
		api_user_id: app.Main.apiUser.api_user_id,
		is_mobile: wdlib.Config.IS_MOBILE ? 1 : 0,
		vars: wdlib.Config.FLASHVARS
	};

	if(auth_params !== undefined) {
		params.auth_params = auth_params;
	}

	console.log("app.App.initSession params : ", params);

	// запрашиваем посредством wdlib.ney.Http [без 2] !!!
	wdlib.net.Http.send("/app.index/init-session", params, onSessionInited);
}

function onSessionInited(error, data)
{
	data = data || {};

	console.log("app.App : SESSION INITED : ", data);

	let p = undefined;

	if((p = wdlib.utils.object.isset(data, "APP_URL"))) {
		wdlib.Config.APP_URL = p;
	}
	if((p = wdlib.utils.object.isset(data, "HEAVY_IMAGE_URL"))) {
		wdlib.Config.HEAVY_IMAGE_URL = p;
	}
	if((p = wdlib.utils.object.isset(data, "TELEGRAM_WEB_APP_ID"))) {
		wdlib.Config.TELEGRAM_WEB_APP_ID = p;
	}

	if(data.hasOwnProperty("sess_token")) {
		wdlib.Config.SESS_TOKEN = data["sess_token"];

		wdlib.net.Http2.init(wdlib.Config.CURRENT_API_USER_ID, wdlib.Config.CURRENT_API, onHttpError, {
			sess_token: wdlib.Config.SESS_TOKEN
		});

		_session_inited = true;

		checkRun();
	}
	else {
		onHttpError(wdlib.model.error.ERROR_AUTH);
	}
}

function onApiEvent(e)
{
//	console.log("app.App : API-EVENT : ", e);

	// пока так банальненько - т.к. работаем только на ВК
	if(e && e.detail && e.detail.type == "VKWebAppViewHide") {
		// приложение уходит в фон
		if(app.controller.Client && app.events.client && app.events.client.Event) {
			app.controller.Client.dispatchEvent(new app.events.client.Event(app.events.client.ON_APP_HIDE));
		}
	}
	if(e && e.detail && e.detail.type == "VKWebAppViewRestore") {
		if(app.controller.Client && app.events.client && app.events.client.Event) {
			app.controller.Client.dispatchEvent(new app.events.client.Event(app.events.client.ON_APP_RESTORE));
		}
	}
	if(e && e.detail && e.detail.type == "VKWebAppUpdateConfig") {
		// обновили какой-то конфиг

		if(!_app_config) {
			_app_config = new app.model.appConfig.Config(e.detail.data);
			
			// сохраняем конфиг
			let key = "app_config_" + (wdlib.api.IApi.api_args.platform);
			wdlib.api.IApi.storageSet(key, _app_config);
		
			checkRun();
		}

		if(app.controller.Client && app.events.client && app.events.client.Event) {
			app.controller.Client.dispatchEvent(new app.events.client.Event(app.events.client.ON_APP_CONFIG));
		}
	}
	if(e && e.detail && e.detail.type == "VKWebAppChangeFragment") {
		// url hash changed
		console.log("URL HASH : ", e);
	}
}

function checkRun()
{
	if(_js_loaded && _api_inited && app.App.getAppConfig() && _session_inited && !_app_runned) {
		_app_runned = true;
		app.controller.App.init();
	}
}

function onHttpError(error)
{
	if(app.controller.Main && app.controller.Main.error) {
		app.controller.Main.error(error);
	}
	else {
		app.Main.sendError(error);
	}
}
function onApiError(error)
{
	if(app.controller.Main && app.controller.Main.error) {
		app.controller.Main.error(error);
	}
	else {
		app.Main.sendError(error);
	}
}

// ============================================================================	

// ============================================================================	
// App start here

//wdlib.Config.CURRENT_API = wdlib.utils.object.isset(wdlib.Config.FLASHVARS, "api", wdlib.Config.CURRENT_API);
//wdlib.Config.CURRENT_API = wdlib.utils.object.isset(wdlib.Config.HASHVARS, "api", wdlib.Config.CURRENT_API);
if(wdlib.Config.FLASHVARS.hasOwnProperty("vk_app_id")) {
	wdlib.Config.CURRENT_API = wdlib.api.API_VK;
}
wdlib.Config.CURRENT_API = wdlib.utils.intval(wdlib.Config.CURRENT_API);

switch(wdlib.Config.CURRENT_API) {
	case wdlib.api.API_LOCAL:
		console.log("using LOCAL API");
		localStart();
		break;
	case wdlib.api.API_STANDALONE:
		console.log("using STANDALONE API");
		standaloneStart();
		break;
	default:
		apiRun();
		break;
}
// ============================================================================	

}, (this.app = this.app || {}).App = {});

