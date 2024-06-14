wdlib.module("wdlib/net/secure_http.js", [
	"md5",
	"wdlib/model/error.js",
	"wdlib/net/http.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/worker.js"
],
function(exports) {
"use strict";

var _queue = [];
var _waiting = false;

var _user_id = "";
var _platform_id = 0;
var _auth_key = "";
var _sid = "";
var _server_ts_delta = 0;
var _extra = {};

var _worker = undefined;
var _wreq = undefined;
var _wuniq = 0;

var _error_callback = undefined;

// ============================================================================
// wdlib.net.SecureHttp interface

/**
 * @param String user_id
 * @param int platform_id
 * @param String auth_key
 * @param Function error_callback
 * @param Object extra, [optional, default = {}]
 */
exports.init = function(user_id, platform_id, auth_key, error_callback, extra = undefined)
{
	_user_id = user_id;
	_platform_id = platform_id;
	_auth_key = auth_key;
	_error_callback = error_callback;
	_extra = extra || {};

	_init_worker();
}

/**
 * @param String controller
 * @param String method
 * @param Object params
 * @param Function callback
 * @param Object udata [optional]
 * @param Object context [optional]
 */
exports.send = function(controller, method, params, callback, udata, context, is_empty_sid = false)
{
	_queue.push(new __Request({
		context: context,
		controller: controller,
		method: method,
		params: params,
		callback: callback,
		udata: udata,
		is_empty_sid: is_empty_sid
	}));
	try_send();
}

/**
 * @param String controller
 * @param String method
 * @param Object params
 * @param Function callback
 * @param Object udata [optional]
 * @param Object context [optional]
 */
exports.report = function(controller, method, params, callback, udata, context)
{
	var req = new __Report({
		context: context,
		controller: controller,
		method: method,
		params: params,
		callback: callback,
		udata: udata
	});
	req.init({
		user_id: _user_id,
		platform_id: _platform_id
	});

	var _err = function(err)
	{
		console.error("wdlib.net.SecureHttp : REPORT FAIL : ", req, err);
	}

	try {
		fetch(req.url, {
			method: "post",
			mode: "cors",
			headers: {
				"Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
			},
			body: wdlib.net.Http.encode(req.params)
		}).then(function(response) {
			if(!response.ok) {
				throw new Error("error while report : " + req.url);
			}
			return response.json();
		}).then(function(data) {
//			console.log("wdlib.net.SecureHttp : HTTP REPORT : ", data);

			// check if error : {"error":"ERROR MESSAGE","error_code":ERROR_CODE}
			var error = undefined;
			if(data.error) {
				// some error occured
				error = new wdlib.model.Error(data);
			
				console.error("wdlib.net.SecureHttp : HTTP REPORT ERROR : ", error, data);
			}

			if(req.callback) {
				req.callback.apply(req.context, [error, data, req.udata]);
			}
		}).catch(function(e) {
			_err.call(undefined, e);
		});
	}
	catch(e) {
		_err.call(undefined, e);
	}
}

exports.redirect = function(url)
{
	window.location = url;
}

/**
 * @param now [optional, default=Date.now()]
 * @return int
 */
exports.getServerTimestamp = function(now = undefined)
{
	if(!now) {
		now = Date.now() / 1000;
	}
	return wdlib.utils.intval(now - _server_ts_delta);
}
// ============================================================================

// ============================================================================
// init SharedWorker here

//_waiting = true;
//wdlib.utils.worker.create(wdlib.src("wdlib/net/http-worker.js"), function(w) {
//	startWorker(w);
//});


function startWorker(worker)
{
	if(!worker) {
		_waiting = false;
		return;
	}
	
	console.log("wdlib.net.SecureHttp : created SharedWorker at ", wdlib.src("wdlib/net/http-worker.js"));
	_worker = worker;

	_worker.port.addEventListener("message", function(e) {
		// console.log("WORKER MESSAGE : ", e);
		try {
			var data = JSON.parse(e.data);

			switch(data.cmd) {
				case "uniq":
					_wuniq = data.uniq;
					_init_worker();
					break;
				case "init":
					_waiting = false;
					try_send();
					break;
				case "send":
					_on_send(data);
					break;
			}
		}
		catch(e) {
			console.error("HTTP WORKER : error catch : ", e.toString());
	
			var error = undefined;

			if(e instanceof wdlib.model.Error) {
				error = e;
				error.file = "wdlib.net.secure_http.js";
			}
			else {
				error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR, stack: e.stack, file: e.fileName || "wdlib.net.secure_http.js"});
			}

			if(_error_callback) {
				_error_callback.call(null, error);
			}

			_waiting = false;
			try_send();
		}
	});

	_worker.port.start();
	_worker.port.postMessage(JSON.stringify({
		cmd: "uniq",
		md5: wdlib.src("md5")
	}));

	_waiting = true;
}

function _init_worker()
{
	if(_worker && _wuniq && _user_id) {
		console.log("wdlib.net.SecureHttp : init worker : ", _wuniq, _user_id);
		_worker.port.postMessage(JSON.stringify({
			cmd: "init",
			uniq: _wuniq,
			user_id: _user_id,
			platform_id: _platform_id,
			auth_key: _auth_key,
			extra: _extra,
			project_url: wdlib.Config.PROJECT_URL,
		}));
	}
}

function _on_send(data)
{
	// console.log("wdlib.net.SecureHttp : on worker send : ", data);

	_server_ts_delta = data.server_ts_delta || _server_ts_delta;

	if(data.error) {
		console.error("wdlib.net.SecureHttp : HTTP SEND FAIL : ", _wreq, data.error);

		var error = new wdlib.model.Error(data.error);

		if(_error_callback) {
			_error_callback.call(null, error);
		}
	}

	if(data.data) {
		if(_wreq.callback) {
			_wreq.callback.apply(_wreq.context, [data.data, _wreq.udata]);
		}
	}
	
	// a little clear for Request item
	_wreq.callback = undefined;
	
	// save to check with next one
	// _prev_req = _wreq;

	_wreq = undefined;

	_waiting = false;
	try_send();
}

// ============================================================================
// private methods

function try_send()
{
	if(_waiting || !_queue.length) {
		return;
	}

	_waiting = true;
	_send(_queue.shift());
}

var _prev_req = undefined;

function _send(req)
{
	if(_worker) {
		_wreq = req;
		_worker.port.postMessage(JSON.stringify({
			cmd: "send",
			controller: req.controller,
			method: req.method,
			params: req.params,
			uniq: _wuniq,
			user_id: _user_id,
			platform_id: _platform_id,
			is_empty_sid: req.is_empty_sid
		}));
		return;
	}

	req.init({
		user_id: _user_id,
		platform_id: _platform_id,
		auth_key: _auth_key,
		sid: req.is_empty_sid ? "" : _sid
	});

	var _err = function(e)
	{
		console.error("wdlib.net.SecureHttp : HTTP SEND FAIL : ", req, e);

		var error = undefined;

		if(e instanceof wdlib.model.Error) {
			error = e;
			error.file = "wdlib.net.secure_http.js";
		}
		else {
			error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR, stack: e.stack, file: e.fileName || "wdlib.net.secure_http.js"});
		}
		error.error += "; request=" + req.url;
		error.error += "; params=" + JSON.stringify(req.params);

		if(_error_callback) {
			_error_callback.call(null, error);
		}

		// a little clear for Request item
		req.callback = undefined;
		// save to check with next one
		_prev_req = req;

		_waiting = false;
		try_send();
	}

	try {
		fetch(req.url, {
			method: "post",
			mode: "cors",
			headers: {
				"Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
			},
			body: wdlib.net.Http.encode(req.params)
		}).then(function(response) {
			if(!response.ok) {
				throw new Error("error while request : " + req.url);
			}
			return response.json();
		}).then(function(data) {
			//console.log("DATA: ", data);

			// check if error : {"error":"ERROR MESSAGE","error_code":ERROR_CODE}
			if(!data || data.error) {
				// some error occured
				var error = new wdlib.model.Error(data || {error: "HTTP DATA UNDEFINED", error_code: wdlib.model.error.ERROR});
	
				// check for SIG ERROR
				if(error.error_code == wdlib.model.error.ERROR_SIG) {
					error.error += "; sigstr='" + req.sigstr + "'";
					if(_prev_req) {
						error.error += "; prev_request=" + _prev_req.url;
					}
				}

				// console.error("wdlib.net.SecureHttp : HTTP ERROR : ", data, error);
				throw error;
			}

			if(data.server_timestamp) {
				_server_ts_delta = Date.now() / 1000 - data.server_timestamp;
			}

			if(data.__new_sid) {
				_sid = "";
			}

			if(data.sig != md5(String(_user_id) + data.data + _auth_key + _sid)) {
				// auth error
				var error = new wdlib.model.Error({error: "HTTP CLIENT SIG ERROR", error_code: wdlib.model.error.ERROR_SIG});
				error.error += "; sigstr='" + req.sigstr + "'";
				if(_prev_req) {
					error.error += "; prev_request=" + _prev_req.url;
				}

//				console.error("wdlib.net.SecureHttp : HTTP SIG ERROR : ", data, error);
				throw error;
			}
	
			if(data.__sid) {
				_sid = data.__sid;
			}

			data = JSON.parse(data.data);

			if(req.callback) {
				req.callback.apply(req.context, [data, req.udata]);
			}

			// a little clear for Request item
			req.callback = undefined;
			// save to check with next one
			_prev_req = req;

			_waiting = false;
			try_send();
		}).catch(function(e) {
			_err.call(undefined, e);
		});
	}
	catch(e) {
		_err.call(undefined, e);
	}
}

function _sort(a, b)
{
	if(a.k > b.k) {
		return 1;
	}
	else if(a.k < b.k) {
		return -1;
	}
	else {
		return 0;
	}
}
// ============================================================================

// ============================================================================
// internal classes

class __Request {

	constructor(args)
	{
		this.context = args.context || null;

		this.controller = args.controller;
		this.method = args.method;
		this.params = args.params;
		this.callback = args.callback;
		this.udata = args.udata;
		this.is_empty_sid = args.is_empty_sid || false;

		this.sigstr = "";

		this.url = "";
	}

	init(args)
	{
		var user_id = args.user_id;
		var platform_id = args.platform_id;
		var auth_key = args.auth_key;
		var sid = args.sid || "";

		this.url = wdlib.Config.PROJECT_URL + "/" + this.controller + "/" + this.method + "?viewer_id=" + String(user_id) + "&viewer_platform=" + String(platform_id);

		var list = [];
		for(var k in _extra) {
			if(typeof _extra[k] === "object") {
				list.push({k:k, v:JSON.stringify(_extra[k])});
			}
			else {
				list.push({k:k, v:_extra[k]});
			}
		}
		for(var k in this.params) {
			if(this.params[k] === "" || this.params[k] === undefined || this.params[k] === null) {
				continue;
			}
			if(typeof this.params[k] === "object") {
				list.push({k:k, v:JSON.stringify(this.params[k])});
			}
			else {
				list.push({k:k, v:this.params[k]});
			}
		}

		list.push({k:"rnd", v:Math.random()});
		list.push({k:"viewer_id", v:user_id});
		list.push({k:"viewer_platform", v:platform_id});

		list.sort(_sort);

		this.params = {};

		var str = String(user_id);
		for(var i=0; i<list.length; ++i) {
			str += String(list[i].k) + '=' + String(list[i].v);
			this.params[list[i].k] = list[i].v;
		}
		str += auth_key + sid;
		this.sigstr = str;

		this.params["sig"] = md5(this.sigstr);

		//console.log("SIG STR: ", str);
	}
}

class __Report {

	constructor(args)
	{
		this.context = args.context || null;

		this.controller = args.controller;
		this.method = args.method;
		this.params = args.params;
		this.callback = args.callback;
		this.udata = args.udata;

		this.url = "";
	}

	init(args)
	{
		var user_id = args.user_id;
		var platform_id = args.platform_id;

		this.url = wdlib.Config.PROJECT_URL + "/" + this.controller + "/" + this.method + "?viewer_id=" + String(user_id) + "&viewer_platform=" + String(platform_id);

		var list = [];
		for(var k in _extra) {
			if(typeof _extra[k] === "object") {
				list.push({k:k, v:JSON.stringify(_extra[k])});
			}
			else {
				list.push({k:k, v:_extra[k]});
			}
		}
		for(var k in this.params) {
			if(this.params[k] === "" || this.params[k] === undefined || this.params[k] === null) {
				continue;
			}
			if(typeof this.params[k] === "object") {
				list.push({k:k, v:JSON.stringify(this.params[k])});
			}
			else {
				list.push({k:k, v:this.params[k]});
			}
		}

		list.push({k:"rnd", v:Math.random()});
		list.push({k:"viewer_id", v:user_id});
		list.push({k:"viewer_platform", v:platform_id});

		list.sort(_sort);

		this.params = {};

		for(var i=0; i<list.length; ++i) {
			this.params[list[i].k] = list[i].v;
		}

		//console.log("SIG STR: ", str);
	}
}
// ============================================================================

}, ((wdlib.net = wdlib.net || {})).SecureHttp = {});
