/**
 * This is WEB Worker file for wdlib.net.Poll of wdlib
 *
 */

var _uniq = 0;
var _session = new Map;

const broadcast = new BroadcastChannel("wdlib.net.poll-worker.js");

self.addEventListener("connect", function(e) {
	// console.log("POLL WORKER : ON CONNECT : ", e);
	 
	e.source.addEventListener("message", function(ev) {
		// console.log("POLL WORKER : ON MESSAGE : ", ev.data);

		try {
			var data = JSON.parse(ev.data);

			switch(data.cmd) {
				case "uniq":
					uniq(e, data);
					break;
				case "init":
					init(e, data);
					break;
			}
		}
		catch(err) {
			console.error("POLL HTTP WORKER : data error : ", err.toString());
		}
	
	}, false);

	e.source.start();
}, false);

function uniq(e, data)
{
	e.source.postMessage(JSON.stringify({
		cmd: "uniq",
		uniq: ++_uniq
	}));
}

function init(e, data)
{
	var sess = new Session;
	sess.init(data);

	var _sess = _session.get(sess.uid);
	if(!_sess) {
		_session.set(sess.uid, sess);

		// start socket
		sess.sock = new PollSocket(sess.url, sess.uid);
		sess.sock.connect({}, sess.onSocketData.bind(sess));
	}
	else {
		sess = _sess;
	}

	e.source.postMessage(JSON.stringify({
		cmd: "init"
	}));
}

// ============================================================================
// classes

class Session {

	constructor()
	{
		this.url = "";
		this.uid = "";

		this.sock = undefined;
	}

	init(data)
	{
		this.url = String(data.url);
		this.uid = String(data.uid);
	}

	onSocketData(data)
	{
		if(this.sock) {
			// continue listening
			this.sock.connect({}, this.onSocketData.bind(this));
		}

		// processing event
		broadcast.postMessage(JSON.stringify({
			uid: this.uid,
			data: data
		}));
	}
}

class PollSocket {

	/**
	 * @param string _url
	 * @param string _lpoll_uid
	 */
	constructor(_url, _lpoll_uid)
	{
		this.url = _url;
		this.lpoll_uid= _lpoll_uid;

		this.data = {};
		this.callback = undefined;

		this.on = false;
		this._abrt = undefined;
		if(typeof AbortController !== "undefined") {
			this._abrt = new AbortController;
		}
		else if (typeof window.AbortController !== "undefined") {
			this._abrt = new window.AbortController;
		}
		this._stop = false;
	}

	/**
	 * @param Object params
	 * @params Function callback
	 */
	connect(params, callback)
	{
		if(this.on) {
			console.log("wdlib.net.PollSocket::connect error: already listening");
			return;
		}

		this._stop = false;

		this.callback = callback;

		this.data = {};
		for(var k in params) {
			if(params[k] == "") continue;
			if(typeof params[k] === "object") {
				this.data[k] = JSON.stringify(params[k]);
			}
			else {
				this.data[k] = params[k];
			}
		}
		this.data["lpoll_uid"] = this.lpoll_uid;

		this.send();
	}

	send()
	{
		if(this._stop) {
			console.log("wdlib.net.PollSocket::send error: stopped");
			return;
		}

		this.on = true;

		var self = this;

		var _err = function(err)
		{
			console.error("wdlib.net.PollSocket : HTTP ERROR : ", err);
			self.on = false;
		}

		try {

			var params = {
				method: "post",
				headers: {
					"Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
				},
				body: encode(this.data)
			}

			if(this._abrt) {
				params.signal = this._abrt.signal;
			}

			fetch(this.url, params)
			.then(function(response) {
				if(!response.ok) {
					throw new Error("error while report : " + self.url);
				}
				return response.text();
			}).then(function(resp) {
				// console.log("wdlib.net.PollSocket : RESPONSE : ", resp);

				self.on = false;

				if(!resp || !resp.length) {
					// just reconnect;
					// console.log("wdlib.net.PollSocket : reconnecting...");
					self.send();
					return;
				}
			
				var data = JSON.parse(resp);
				self.callback.call(null, data);

			}).catch(function(e) {
				_err.call(undefined, e);
			});
		}
		catch(e) {
			_err.call(undefined, e);
		}
	}

	stop()
	{
		if(this._abrt) {
			this._abrt.abort();
		}

		this._stop = true;
	}
}

// ============================================================================

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
