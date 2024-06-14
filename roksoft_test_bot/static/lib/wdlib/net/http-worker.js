/**
 * This is WEB Worker file for SecureHttp of wdlib
 *
 */

self.addEventListener("connect", function(e) {
	// console.log("WORKER : ON CONNECT : ", e);
	 
	e.source.addEventListener("message", function(ev) {
		// console.log("WORKER : ON MESSAGE : ", ev.data);

		try {
			var data = JSON.parse(ev.data);

			switch(data.cmd) {
				case "uniq":
					uniq(e, data);
					break;
				case "init":
					init(e, data);
					break;
				case "send":
					send(e, data);
					break;
			}
		}
		catch(err) {
			console.error("HTTP WORKER : data error : ", err.toString());
		}
	
	}, false);

	e.source.start();
}, false);

// ============================================================================
// ERROR constants

var ERROR = {
	OK: 0,
	ERROR: -1,
	ERROR_AUTH: -5,
	ERROR_API: -16,
	ERROR_SIG: -20
}

// ============================================================================

var _uniq = 0;
var _session = new Map;

var _queue = [];
var _waiting = false;

function uniq(e, data)
{
	self.importScripts(data.md5);

	e.source.postMessage(JSON.stringify({
		cmd: "uniq",
		uniq: ++_uniq
	}));
}

function init(e, data)
{
	var sess = new Session;
	sess.init(data);

	var _sess = _session.get(Session.key(sess.platform_id, sess.user_id));
	if(!_sess) {
		_session.set(Session.key(sess.platform_id, sess.user_id), sess);
	}
	else {
		sess = _sess;
	}

	e.source.postMessage(JSON.stringify({
		cmd: "init"
	}));
}

function send(e, data)
{
	var sess = _session.get(Session.key(data.platform_id, data.user_id));
	if(!sess) {
		console.error("HTTP WORKER : SEND ERROR : SESSION NOT FOUND");
		return;
	}

	_queue.push(new __Request(sess, e.source, {
		controller: data.controller,
		method: data.method,
		params: data.params,
		is_empty_sid: data.is_empty_sid
	}));
	try_send();
}

function try_send()
{
	if(_waiting || !_queue.length) {
		return;
	}

	_waiting = true;
	_send(_queue.shift());
}

function _send(req)
{
	req.init();

	// console.log("HTTP WORKER : SEND : ", JSON.stringify(req.params));

	var _err = function(e)
	{
		console.error("wdlib.net.Http-Worker : HTTP SEND FAIL : ", req, e);
		
		var error = undefined;

		if(e instanceof Error) {
			error = Object.assign({error: e.message, error_code: ERROR.ERROR, stack: e.stack, file: e.fileName || "wdlib.net.http-worker.js"});
		}
		else {
			error = e;
			error.file = "wdlib.net.http-worker.js";
		}

		req.release(null, error);

		// save to check with next one
		req.sess._prev_req = req;

		_waiting = false;
		try_send();
	}

	try {
		fetch(req.url, {
			method: "post",
			headers: {
				"Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
			},
			body: encode(req.params)
		}).then(function(response) {
			if(!response.ok) {
				throw new Error("error while request : " + req.url);
			}
			return response.json();
		}).then(function(data) {
			// console.log("HTTP WORKER DATA: ", data);

			// check if error : {"error":"ERROR MESSAGE","error_code":ERROR_CODE}
			if(!data || data.error) {
				// some error occured
				var error = Object.assign({error: "HTTP DATA UNDEFINED", error_code: ERROR.ERROR}, data);
	
				// check for SIG ERROR
				if(error.error_code == ERROR.ERROR_SIG) {
					error.error += "; sigstr='" + req.sigstr + "'";
					if(req.sess._prev_req) {
						error.error += "; prev_request=" + req.sess._prev_req.url;
					}
				}

				// console.error("wdlib.net.SecureHttp : HTTP ERROR : ", data, error);
				throw error;
			}

			if(data.server_timestamp) {
				req.sess.server_ts_delta = Date.now() / 1000 - data.server_timestamp;
			}

			if(data.__new_sid) {
				req.sess.sid = "";
			}

			if(data.sig != md5(String(req.sess.user_id) + data.data + req.sess.auth_key + req.sess.sid)) {
				// auth error
				var error = Object.assign({error: "HTTP CLIENT SIG ERROR", error_code: ERROR.ERROR_SIG});
				error.error += "; sigstr='" + req.sigstr + "'";
				if(req.sess._prev_req) {
					error.error += "; prev_request=" + req.sess._prev_req.url;
				}

//				console.error("wdlib.net.SecureHttp : HTTP SIG ERROR : ", data, error);
				throw error;
			}
	
			if(data.__sid) {
				req.sess.sid = data.__sid;
			}

			data = JSON.parse(data.data);

			req.release(data);

			// a little clear for Request item
			req.callback = undefined;
			// save to check with next one
			req.sess._prev_req = req;

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

// ============================================================================
class Session {

	constructor()
	{
		this.project_url = "";

		this.user_id = "";
		this.platform_id = 0;
		this.auth_key = "";
		this.extra = {};
		this.sid = "";

		this.server_ts_delta = 0;
		this._prev_req = undefined;
	}

	init(data)
	{
		this.project_url = data.project_url;

		this.user_id = String(data.user_id);
		this.platform_id = data.platform_id;
		this.auth_key = data.auth_key;
		this.extra = data.extra || {};
	}

	static key(platform_id, user_id)
	{
		return String(platform_id) + "-" + String(user_id);
	}
}

class __Request {

	constructor(sess, source, args)
	{
		this.sess = sess;
		this.source = source;

		this.controller = args.controller;
		this.method = args.method;
		this.params = args.params;
		this.is_empty_sid = args.is_empty_sid;

		this.sigstr = "";
		this.url = "";
	}

	init()
	{
		if(this.is_empty_sid) {
			// force clear SID when main/init
			this.sess.sid = "";
		}

		var user_id = this.sess.user_id;
		var platform_id = this.sess.platform_id;
		var auth_key = this.sess.auth_key;
		var sid = this.sess.sid || "";

		this.url = this.sess.project_url + "/" + this.controller + "/" + this.method + "?viewer_id=" + String(user_id) + "&viewer_platform=" + String(platform_id);

		var list = [];
		for(var k in this.sess.extra) {
			if(typeof this.sess.extra[k] === "object") {
				list.push({k:k, v:JSON.stringify(this.sess.extra[k])});
			}
			else {
				list.push({k:k, v:this.sess.extra[k]});
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

	release(data, error = null)
	{
		this.source.postMessage(JSON.stringify({
			cmd: "send",
			data: data,
			error: error,
			server_ts_delta: this.sess.server_ts_delta
		}));

		this.source = undefined;
	}
}

// ============================================================================
// utils
function encode(data, sort = false, info = null)
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
