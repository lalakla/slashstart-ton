(function(global, factory) {
	global.wdlib = global.wdlib || {};
	factory(global.wdlib.Loader = {});
}(this, (function(exports) {

"use strict";

var __cache = {};
var __config = {
	debug: false,
	base: "",
	map: {}
};

var _last = null;
var _executing = [];

var SCRIPT_STATE_UNLOADED = 0;
var SCRIPT_STATE_LOADING = 1;
var SCRIPT_STATE_EXECUTING = 2;
var SCRIPT_STATE_LOADED = 3;

var SRC_TYPE_REGEXP = /\.([a-z]+)$/
var SCRIPT_SRC_TYPE_JS = "js";
var SCRIPT_SRC_TYPE_CSS = "css";
var SCRIPT_SRC_TYPE_TEMPLATE = "template";

class ScriptLoader {

	constructor(args) {
		this.id = args.id;
		this.name = args.name;
		this.src = args.src;
		this.src_type = SCRIPT_SRC_TYPE_JS;

		if(args.src_type == undefined) {
			var match = this.src.match(SRC_TYPE_REGEXP);
			if(match !== null) {
				//console.log("SCRIPT SRC: ", match);
				this.src_type = match[1];
			}
		}
		else {
			this.src_type = args.src_type;
		}

		this.state = SCRIPT_STATE_UNLOADED;

		this.callback = [];

		this.s = null;
	}

	load() {
		var self = this;
		_last = this;

//		console.log("ScriptLoader["+this.src+"] start loading");

		this.state = SCRIPT_STATE_LOADING;

		switch(this.src_type) {
			case SCRIPT_SRC_TYPE_CSS:
				this.s = document.createElement("link");
				this.s.type = "text/css";
				this.s.rel = "stylesheet";
				this.s.href = this.src;
				this.s.media = "all";
				break;
			case SCRIPT_SRC_TYPE_TEMPLATE:
				this.load_template();
				return;
			case SCRIPT_SRC_TYPE_JS:
			default:
				this.s = document.createElement("script");
				this.s.type = "text/javascript";
				this.s.src = this.src;
//				this.s.async = true;
//				this.s.defer = true;
				this.s.async = false;
				this.s.defer = false;
				break;
		}
		this.s.id = this.id;
		this.s.onload = this.s.onreadystatechange = function() {
			// console.log("ScriptLoader["+self.name+"] readyState changed : ", this.readyState);

			if((!this.readyState || this.readyState == "complete")) {
				switch(self.state) {
					case SCRIPT_STATE_UNLOADED:
					case SCRIPT_STATE_LOADING:
						self.release();
						break;
					case SCRIPT_STATE_EXECUTING:
						// wait for script to execute
						break;
					case SCRIPT_STATE_LOADED:
					default:
						// script already loaded, do nothing
						break;
				}
			}
		}
		var t = document.getElementsByTagName("script")[0];
		t.parentNode.insertBefore(this.s, t);
	}
	load_template() {
		var self = this;

		var req = new XMLHttpRequest;
		req.addEventListener("load", function() {
			switch(self.state) {
				case SCRIPT_STATE_UNLOADED:
				case SCRIPT_STATE_LOADING:
					var s = document.createElement("script");
					s.type = "text/template";
					//var s = document.createElement("template");
					//var s = document.createElement("div");
					s.id = self.id;
					s.innerHTML = this.responseText;
					//console.log("TEMPLATE LOADED: ", this.responseText);
					var t = document.getElementsByTagName("script")[0];
					t.parentNode.insertBefore(s, t);
					//console.log("TEMPLATE SET: ", s.innerHTML);
					self.release();
					break;
				case SCRIPT_STATE_EXECUTING:
					// wait for script to execute
					break;
				case SCRIPT_STATE_LOADED:
				default:
					// script already loaded, do nothing
					break;
			}
		});

		req.addEventListener("error", function(err) {
			console.log("wdlib.Loader.load_template error: ", err);
		});

		req.open("GET", this.src, true);
		req.send();
	}

	release() {
		if(__config.debug) {
			console.log("ScriptLoader["+this.src+"] loaded");
		}

		this.state = SCRIPT_STATE_LOADED;

		for(var i=0; i<this.callback.length; ++i) {
			//this.callback[i](this);
			this.callback[i].call(null, this);
		}

		this.callback = [];
	}
}

exports.config = function(args) {
	for(var k in args) {
		if(k == "base") {
			__config.base = args[k];
		}
		// init map
		if(k == "map") {
			for(var kk in args.map) {
				__config.map[kk] = args.map[kk];
			}
		}
		// if DEBUG
		if(k == "debug") {
			__config.debug = args[k];
		}
	}

	//console.log("CONFIG: ", __config);
}

var __id = 0;

exports.import = function(args, callback = null) {
	if(__config.debug) {
		console.log("Loader.import : ", args, ":", typeof args);
	}

	if(typeof args === "string") {
		args = [args];
	}
	if(typeof args !== "object") {
		throw new Error("wdlib.Loader.import args should be Array or String");
	}

	var _args = Array.from(arguments).slice(2);
	//console.log("_args: ", _args);

	function on_load(script = null) {
		import_next();
	}

	function import_next() {
		if(!args.length) {
			// load complete
			if(callback != null) {
				callback.apply(null, _args);
			}

			return;
		}

		var s = args.shift();

		if(typeof s === "string") {
			s = {src: s};
		}
		if(typeof s !== "object") {
			throw new Error("wdlib.Loader.import args should be Object or String");
		}

//		console.log("TRY LOAD: ", s);

		// check __config.map by name
		var ss = __config.map[s.src];
		ss = ss ? ss : __config.base + s.src;

		// check cache
		var script = __cache[ss];
		if(!script) {
			var id = s.id !== undefined ? s.id : "wdlib.loader.loaded." + String(++__id);

			script = new ScriptLoader({id: id, name: s.src, src: ss, src_type: s.type});
			script.load();

			__cache[ss] = script;
		}

		if(script.state == SCRIPT_STATE_LOADED) {
			import_next();
		}
		else {
			script.callback.push(on_load);
		}
	}

	import_next();
}

exports.waitForMe = function() {
	if(!_last) return;

//	console.log("WAIT FOR: ", _last.name);
	_last.state = SCRIPT_STATE_EXECUTING;
	_executing.push(_last);
}
exports.releaseMe = function() {
	if(_executing.length) {
		var script = _executing.pop();
//		console.log("RELEASE FOR: ", script.name);
		script.release();
	}
}

})));
