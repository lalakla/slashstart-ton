wdlib.module("wdlib/net/poll.js", [
	"wdlib/net/http.js",
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.net.PollSocket class

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
		this.error_callback = undefined;

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
	 * @params Function error_callback
	 */
	connect(params, callback, error_callback)
	{
		if(this.on) {
			console.log("wdlib.net.PollSocket::connect error: already listening");
			return;
		}

		this.callback = callback;
		this.error_callback = error_callback;

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
			if(self.error_callback !== undefined) {
				self.error_callback.call(undefined, err);
			}
		}

		try {

			var params = {
				method: "post",
				headers: {
					"Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
				},
				body: wdlib.net.Http.encode(this.data)
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
		this._stop = true;

		if(this._abrt) {
			this._abrt.abort();
		}
	}
}

exports.PollSocket = PollSocket;
// ============================================================================

// ============================================================================
// wdlib.net.Poll class

class Poll {

	constructor()
	{
		this.sock = undefined;
		this.err_timeout = 1000;
	}

	init(url, uid, callback)
	{
		this.url = url;
		this.uid = uid;
		this.callback = callback;

		this.sock = new wdlib.net.PollSocket(this.url, this.uid);
		this._sock();
	}

	_sock()
	{
		if(this.sock) {
			this.sock.connect({}, this.onSocketData.bind(this), this.onSocketError.bind(this));
		}
	}

	onSocketData(data)
	{
		this.err_timeout = 1000;
		
		// continue listening
		this._sock();

		// processing event
		this.callback.call(undefined, data);
	}

	onSocketError(err)
	{
		console.error("wdlib.net.Poll sokect error ", err, " try reconnect in ", this.err_timeout, " msec");

		var self = this;

		setTimeout(function() {
			self.err_timeout *= 2;

			// continue listening
			self._sock();

		}, this.err_timeout);
	}

	stop()
	{
		if(this.sock) {
			this.sock.stop();
			this.sock = undefined;
		}
	}
}

exports.Poll = new Poll;
// ============================================================================

}, (wdlib.net = wdlib.net || {}));
