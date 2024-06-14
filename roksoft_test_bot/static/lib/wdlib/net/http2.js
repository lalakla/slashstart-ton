wdlib.module("wdlib/net/http2.js", [
//	"jquery",
	"wdlib/model/error.js",
	"wdlib/net/http.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/worker.js"
],
function(exports) {
"use strict";

var _user_id = "";
var _platform_id = 0;
var _token = "";
var _server_ts_delta = 0;
var _extra = {};

var _error_callback = undefined;

// ============================================================================
// wdlib.net.Http2 interface

/**
 * @param String user_id
 * @param int platform_id
 * @param Function error_callback
 * @param Object extra, [optional, default = {}]
 */
exports.init = function(user_id, platform_id, error_callback, extra = undefined)
{
	_user_id = user_id;
	_platform_id = platform_id;
	_error_callback = error_callback;
	_extra = extra || {};
}

exports.setExtra = function(extra)
{
	_extra = Object.assign(_extra, extra);
}

/**
 * @param String controller
 * @param String method
 * @param Object params
 * @param Function callback
 * @param Object options [optional] {context,udata,retry}
 *
 */
exports.send = function(controller, method, params, callback, options)
{
	var r = new __Request({
		controller: controller,
		method: method,
		params: params,
		callback: callback,
		options: options
	});

	_send(r);
//	_sendJquery(r);
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
// private methods

function _send(req, _retry = false)
{
	if(!_retry) {
		req.init({
			user_id: _user_id,
			platform_id: _platform_id,
			token: _token
		});
	}

	var _err = function(e)
	{
		// console.error("wdlib.net.Http2 : HTTP SEND FAIL : ", req, e);

		if(!(e instanceof wdlib.model.Error)) {
			// network error, possible to retry
			if(req.options.retry) {
				console.log("wdlib.net.Http2 : RETRYING : in " + req._retry_timeout + "msec");
				setTimeout(function() {
					req.options.retry--;
					req._retry_timeout *= 2;
					_send(req, true);
				}, req._retry_timeout);
				return;
			}
		}

		var error = undefined;

		if(e instanceof wdlib.model.Error) {
			error = e;
			error.file = "wdlib.net.http2.js";
		}
		else {
			error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR, stack: e.stack, file: e.fileName || "wdlib.net.http2.js"});
		}
		error.error += "; request=" + req.url;
		error.error += "; params=" + JSON.stringify(req.params);

		// check onError for req.options
		if(req.options.onError !== undefined && req.options.onError(error)) {
			// if onError returns TRUE - so it's ok & stop doing anything more 
			return;
		}

		if(_error_callback) {
			_error_callback.call(null, error);
		}
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
				throw new Error("error while request : " + req.url + ", err status: " + response.status);
			}
			return response.json();
		}).then(function(data) {
			//console.log("DATA: ", data);

			// check if error : {"error":"ERROR MESSAGE","error_code":ERROR_CODE, "error_data": some-error-data}
			if(!data || data.error) {
				// some error occured
				var error = new wdlib.model.Error(data || {error: "HTTP DATA UNDEFINED", error_code: wdlib.model.error.ERROR});
				// console.error("wdlib.net.Http2 : HTTP ERROR : ", data, error);
				throw error;
			}

			if(data.token) {
				_token = data.token;
			}

			if(data.server_timestamp) {
				_server_ts_delta = Date.now() / 1000 - data.server_timestamp;
			}

			data = JSON.parse(data.data);

			if(req.callback) {
				req.callback.apply(req.options.context, [data, req.options.udata]);
			}
		}).catch(function(e) {
			// console.error("wdlib.net.Http2 : FETCH HTTP REQUEST ERROR !!!");
			_err.call(undefined, e);
		});
	}
	catch(e) {
		console.error("wdlib.net.Http2 : FETCH exception catch !!!");
		_err.call(undefined, e);
	}
}

function _sendJquery(req)
{
	req.init({
		user_id: _user_id,
		platform_id: _platform_id,
		token: _token
	});

	var _err = function(e)
	{
		console.error("wdlib.net.Http2 : HTTP SEND FAIL : ", req, e);

		var error = undefined;

		if(e instanceof wdlib.model.Error) {
			error = e;
			error.file = "wdlib.net.http2.js";
		}
		else {
			error = new wdlib.model.Error({error: e.message, error_code: wdlib.model.error.ERROR, stack: e.stack, file: e.fileName || "wdlib.net.http2.js"});
		}
		error.error += "; request=" + req.url;
		error.error += "; params=" + JSON.stringify(req.params);

		if(_error_callback) {
			_error_callback.call(null, error);
		}
	}

	try {
		let xhr = $.ajax({
			type: "POST",
			dataType: "json",
			url: req.url,
			data: wdlib.net.Http.encode(req.params)
		}).done(function(data) {
			//console.log("DATA: ", data);

			// check if error : {"error":"ERROR MESSAGE","error_code":ERROR_CODE}
			if(!data || data.error) {
				// some error occured
				var error = new wdlib.model.Error(data || {error: "HTTP DATA UNDEFINED", error_code: wdlib.model.error.ERROR});
				console.error("wdlib.net.Http2 : HTTP ERROR : ", data, error);
				_err.call(undefined, error);
				return;
			}

			if(data.token) {
				_token = data.token;
			}

			if(data.server_timestamp) {
				_server_ts_delta = Date.now() / 1000 - data.server_timestamp;
			}

			data = JSON.parse(data.data);

			if(req.callback) {
				req.callback.apply(req.options.context, [data, req.options.udata]);
			}
		}).fail(function(jqXHR, textStatus, err) {
			console.log("wdlib.net.Http2 : jQuery HTTP REQUEST ERROR !!!", req, textStatus, err, jqXHR);
			var error = new wdlib.model.Error({
				error: "HTTP REQUEST ERROR : " + jqXHR.readyState + ", " + jqXHR.status + ", " + jqXHR.statusText + " | " + JSON.stringify(err),
				error_code: wdlib.model.error.ERROR
			});
			_err.call(undefined, error);
		});
	}
	catch(e) {
		_err.call(undefined, e);
	}
}

// ============================================================================

// ============================================================================
// internal classes

class __Request {

	constructor(args)
	{
		this.controller = args.controller;
		this.method = args.method;
		this.params = args.params;
		this.callback = args.callback;
		this.options = Object.assign({
			context: undefined,
			udata: undefined,
			retry: 0
		}, args.options);

		this.url = "";

		this._retry_timeout = 1000;
		this._request_id = performance.now();
	}

	init(args)
	{
		var user_id = args.user_id;
		var platform_id = args.platform_id;
		var token = args.token;

		this.url = wdlib.Config.PROJECT_URL + "/" + this.controller + "/" + this.method + "?viewer_id=" + String(user_id) + "&viewer_platform=" + String(platform_id);

		var list = [];
		for(var k in _extra) {
			this.params[k] = _extra[k];
		}

		this.params["rnd"] = Math.random();
		this.params["token"] = token;
		this.params["request-id"] = this._request_id;

//		this.params["viewer_id"] = user_id;
//		this.params["viewer_platform"] = platform_id;
	}
}
// ============================================================================

}, ((wdlib.net = wdlib.net || {})).Http2 = {});
