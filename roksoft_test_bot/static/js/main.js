wdlib.module("/js/main.js", [
	"wdlib/net/http.js",
	"/js/config.js",
	"/js/controller/main.js",
	"/js/view/main.js",
],
function(exports) {
"use strict";

console.log("MAIN START v" + wdlib.Config.VERSION);

var _app_closed = false;

// ============================================================================	
// app.Main interface

exports.currentUser = undefined;
exports.apiUser = undefined;
exports.apiAuth = undefined;

/**
 * Starts VK (ok\mm\other) application
 *
 */
exports.appstart = function()
{
	app.controller.Main.init();
	
	wdlib.run("/js/app.js");
}

exports.webstart = function(data)
{
	app.Config.init(data);

	app.controller.Main.init();
}

/**
 * @param String key
 * @param int val [optional, default=1]
 */
exports.statsReport = function(key, val = 1)
{
	val = wdlib.utils.intval(val);
	if(val <= 0) {
		return;
	}

	/*
	wdlib.net.Http.send("/php/index/stats", {
		app_client_platform: wdlib.Config.CLIENT_CURRENT_PLATFORM,
		viewer_platform: wdlib.Config.CLIENT_PLATFORM,
		viewer_id: wdlib.Config.CLIENT_USER_ID,
		rnd: Math.random(),
		key: key,
		val: val
	});
	*/
}

/**
 * @param Object log
 */
exports.logReport = function(log)
{
	log = wdlib.api.IApi.api_args.platform + " | " + wdlib.api.IApi.viewer_id + " | dpr.real=" + String(wdlib.Config.screen.realPixelRatio) + ", dpr.used=" + String(wdlib.Config.screen.pixelRatio) +  " | " + JSON.stringify(log);
	wdlib.net.Http.send("/error/trace", {
		viewer_platform: wdlib.Config.CURRENT_API,
		viewer_id: (app.Main.apiUser) ? app.Main.apiUser.api_user_id : 0,
		rnd: Math.random(),
		log: log
	});
}

var _errors_sent = new Map;
exports.sendError = function(e)
{
	var sent = _errors_sent.get(e.error_code);
	sent = wdlib.utils.intval(sent);

	// do not send SESSION_ERROR
	if(sent < 2 && ![app.Config.ERROR_AUTH].includes(e.error_code)) {
		var str = "ERROR";
		if(_app_closed) {
			str += "[app-closed]";
		}
		str += " : ver: '" + wdlib.Config.VERSION + "', '" + e.error + "', file: '" + e.file + "', line: " + e.line + ", stack: '" + (e ? e.stack : "") + "'";
		console.error("ERROR: ", e);
		console.error("SEND ERROR : ", str);
	
		wdlib.net.Http.send("/error/error", {
			viewer_platform: wdlib.Config.CURRENT_API,
			viewer_id: (app.Main.apiUser) ? app.Main.apiUser.api_user_id : 0,
			errno: e.error_code,
			method: e.file,
			comment: str
		});

		sent++
		_errors_sent.set(e.error_code, sent);
	}
}

exports.onError = function(error)
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
// Main internal functions

// ============================================================================	

// ============================================================================	
// Main start here

// LOAD URL PARAMS FIRST
var url = window.location.toString();

//get the parameters
url.match(/\?(.+)$/);
var params = RegExp.$1;

// split up the query string and store in an associative array
wdlib.Config.FLASHVARS = {};
wdlib.Config.HASHVARS = {};
params = params.split('#');

var urlparams = params[0];
var hashparams = params[1];

params = urlparams.split('&');
for(var i=0;i<params.length;i++) {
	var tmp = params[i].split("=");
	wdlib.Config.FLASHVARS[tmp[0]] = decodeURIComponent(tmp[1]);
}

if(hashparams) {
	params = hashparams.split('&');
	for(var i=0;i<params.length;i++) {
		var tmp = params[i].split("=");
		wdlib.Config.HASHVARS[tmp[0]] = decodeURIComponent(tmp[1]);
	}
}

console.log("FLASHVARS: ", wdlib.Config.FLASHVARS);
console.log("HASHVARS: ", wdlib.Config.HASHVARS);

// before unload event catch -> when close, reload ... app page
window.addEventListener("beforeunload", function(e) {
	_app_closed = true;
	console.log("UNLOAD EVENT CATCH : ", e);
//	e.preventDefault();
//	e.returnValue = '';
});

// ============================================================================	

}, (Object.assign(this.app = this.app || {}, {

	// APP STRUCTURE ---------------------
	controller: {
		app: {}
	},
	view: {
		form: {
		},
		popup: {
		},
		web: {
			page: {}
		},
		app: {
			page: {
				user: {},
			},
			form: {}
		},
		tonConnect: {},
	},
	model: {},
	events: {},
	service: {},
	utils: {},
	// -----------------------------------
})).Main = {});
