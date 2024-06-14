wdlib.module("wdlib/net/http.js", [
	"wdlib/model/error.js"
],
function(exports) {
"use strict";

var _extra = {};

// ============================================================================
// wdlib.net.Http interface

exports.redirect = function(url)
{
	window.location = url;
}

/**
 * @param Object extra
 */
exports.init = function(extra)
{
	_extra = extra || {};
}

/**
 * @param String uri
 * @param Object params
 * @param Function callback
 * @param Object udata [optional]
 * @param Object context [optional]
 *
 */
exports.send = function(uri, params, callback, udata, context)
{
	var r = new __Request({
		context: context,
		uri: uri,
		params: params,
		callback: callback,
		udata: udata
	});
	_send(r);
}

/**
 * @param String uri
 * @param Object params
 * @param Function callback
 * @param Object udata [optional]
 * @param Object context [optional]
 *
 * same as wdlib.net.Http.send
 * for backward compatibility
 */
exports.report = function(uri, params, callback, udata, context)
{
	var r = new __Request({
		context: context,
		uri: uri,
		params: params,
		callback: callback,
		udata: udata
	});
	_send(r);
}

/**
 * encode data to URL
 *
 * @param Object data
 * @param Boolean sort [optional, default = false]
 * @param Object info [optional, default = null]
 *
 * @return string
 */
exports.encode = function(data, sort = false, info = null)
{
	var str = "";
	if(!(typeof data === "object")) {
		return data;
	}

	if((info !== null) && !(typeof info === "object")) {
		info = null;
	}

	var list = [];

	for(var k in data) {
		let _k = encodeURIComponent(k);
		let _v = encodeURIComponent((typeof data[k] === "object") ? JSON.stringify(data[k]) : data[k])

		if(sort || info !== null) {
			list.push({k:_k, v:_v});
		}
		else {
			str += (str.length ? "&" : "") + _k + "=" + _v; 
		}
	}

	if(sort) {
		list.sort(_sort);
	}

	if(info) {
		info.list = list;
	}

	if(!str.length && list.length) {
		str = list.reduce(function(str, e) {
			return (str.length ? "&" : "") + e.k + "=" + e.v;
		});
	}

	return str;
}

// ============================================================================

// ============================================================================
// private methods

function _send(req, _onOk = undefined, _onError = undefined)
{
	req.init();

	var _err = function(err)
	{
		console.error("wdlib.net.Http::_send FAIL : ", req.url, err);

		if(req.callback) {
			req.callback.apply(req.context, [err, undefined, req.udata]);
		}

		if(_onError !== undefined) {
			_onError.call(undefined, err, req);
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
//				console.log("ERROR : RESPONSE : ", response);
				throw new Error("error while request : " + req.url + ", err status: " + response.status + ", statusText: " + response.statusText);
			}
			return response.json();
		}).then(function(data) {
			// check if error : {"error":"ERROR MESSAGE","error_code":ERROR_CODE}
			var error = undefined;
			if(data.error) {
				// some error occured
				error = new wdlib.model.Error(data);
			
				console.error("wdlib.net.Http : HTTP ERROR RETURNED : ", error, data);
			}

			if(req.callback) {
				req.callback.apply(req.context, [error, data, req.udata]);
			}

			if(_onOk !== undefined) {
				_onOk.call(undefined, data, req);
			}
		}).catch(function(e) {
			console.log("wdlib.net.Http : FETCH HTTP REQUEST ERROR !!!", e);
			_err.call(undefined, e);
		});
	}
	catch(e) {
		console.log("wdlib.net.Http : FETCH exception catch !!!", e);
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

		this.uri = args.uri;
		this.params = args.params;
		this.callback = args.callback;
		this.udata = args.udata;

		this.url = "";
	}

	init(args)
	{
		this.url = wdlib.Config.PROJECT_URL + this.uri;

		var list = [];
		for(var k in _extra) {
			this.params[k] = _extra[k];
		}

		this.params["rnd"] = Math.random();
	}
}
// ============================================================================

}, ((wdlib.net = wdlib.net || {})).Http = {});
