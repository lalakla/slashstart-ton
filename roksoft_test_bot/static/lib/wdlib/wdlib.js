(function(global, factory) {
	global.wdlib = global.wdlib || {};
	factory(global.wdlib);
}(this, (function(exports) {
"use strict";

exports.VERSION = "1.7.13";

exports.Config = {
	DEBUG: false,

	PROJECT_URL: "",
	PROJECT_DOMAIN: "",
	
	CLIENT_KEY: "",

	// это старые параметры - но много где использующиеся
	CLIENT_PLATFORM: 0,
	CLIENT_USER_ID: "0",
	// это новые, названия более логичные
	// но пока используются не везде
	CURRENT_API: 0,
	CURRENT_API_USER_ID: "0",
};

var _config = {
	debug: false,
	base: "",
	map: {}
};

var _id = 0;
var _modules = {};
var _loaders = {};

var _onload = undefined;
var _loadstat = {
	total: 0,
	loading: 0,
	loaded: 0,
	error: 0,

	onload: function(arg)
	{
//		console.log("wdlib.module.loader["+arg.src+"] : LOADED : ", arg, this);
		if(_onload !== undefined) {
			_onload.call(undefined, true, arg.src, this, null);
		}
	},
	onerror: function(arg, error)
	{
		console.error("wdlib.module.loader["+arg.src+"] : ERROR : ", error, arg, this);
		if(_onload !== undefined) {
			_onload.call(undefined, false, arg.src, this, error);
		}
	}
}

exports.setOnLoad = function(onload)
{
	_onload = onload;
}

exports.src = function(src)
{
	// check src with _config base & map
	var _src = _config.map[src];

	if(!_src && src.charAt(0) != '/') {
		// check params like 'LIBNAME/some/special/file.js'
		var pos = src.indexOf('/');
		var libpath = undefined;

		if(pos != -1 && pos != 0 && (libpath = _config.map[src.substr(0, pos)])) {
			_src = libpath + src.substr(pos);
		}
	}

	_src = _src || (_config.base + src);

	return _src;
}

/*
 * @param Array|String imports
 */
exports.run = function(imports)
{
	wdlib.module(
		"wdlib.run" + String(++_id),
		imports
	);
}

/*
 * @param Array|String imports
 * @param Function callback
 * @param Object exports [optional, default = null]
 */
exports.load = function(imports, callback, exports = null)
{
	wdlib.module(
		"wdlib.load" + String(++_id),
		imports,
		function() {
			callback.call(null, exports);
		}
	);
}

exports.queue = function(arg)
{
//	console.log("QUEUE ARG : ", arg);
	var args = [];
	for(var i=0; i<arguments.length; ++i) {
		args.push(arguments[i]);
	}

	var _load = function(args, resolve, reject) {
		wdlib.module(
			"wdlib.queue" + String(++_id),
			args,
			function() {
//				console.log("QUEUE complete : ", arg);
				resolve();
			}
		);
	}

	var _queue = function(promise, args) {
		var p = new Promise(function(resolve, reject) {
			promise.then(function() {
				_load.call(undefined, args, resolve, reject);
			});
		});

		p.queue = function(arg)
		{
			var args = [];
			for(var i=0; i<arguments.length; ++i) {
				args.push(arguments[i]);
			}
			return _queue.call(undefined, p, args);
		}

		return p;
	}

	var promise = new Promise(function(resolve, reject) {
		_load.call(undefined, args, resolve, reject);
	});

	promise.queue = function(arg) {
		var args = [];
		for(var i=0; i<arguments.length; ++i) {
			args.push(arguments[i]);
		}
		return _queue.call(undefined, promise, args);
	};

	return promise;
}

/**
 * @param String filename
 * @param Array|String imports
 * @param Function body [optional]
 * @param Object exports [optional]
 */
exports.module = function(filename, imports, body, exports)
{
	if(_config.debug) {
		console.log("wdlib.module : ", filename);
		console.log("wdlib.module : imports : ", imports, ":", typeof imports);
	}

	if(typeof imports === "string") {
		imports = [imports];
	}
	if(typeof imports !== "object") {
		throw new Error("wdlib.module imports should be Array or String");
	}

	if(_modules[filename]) {
		throw new Error("wdlib.module '" + filename + "' already exists");
	}

	var _args = Array.from(arguments).slice(3);

	_modules[filename] = new Module(filename, imports, body, _args);
}

/**
 * @param Object config
 */
exports.module.config = function(config)
{
	for(var k in config) {
		if(!_config.hasOwnProperty(k)) continue;
		switch(k) {
			case "map":
				for(var kk in config.map) {
					_config.map[kk] = config.map[kk];
				}
				break;
			default:
				_config[k] = config[k];
				break;
		}
	}

	if(_config.debug) {
		console.log("wdlib.module.config:", _config);
	}
}

var MODULE_STATE_PENDING = 0;
var MODULE_STATE_LOADED = 1;
var LOADER_STATE_PENDING = 0;
var LOADER_STATE_LOADED = 1;

var SRC_TYPE_REGEXP = /\.([a-z]+)$/
var SRC_TYPE_JS = "js";
var SRC_TYPE_CSS = "css";
var SRC_TYPE_TEMPLATE = "template";

const MAX_TRIES_TO_ERROR = 3;

function Module(name, imports, body, args)
{
	this.name = name;
	this.imports = imports;
	this.body = body;
	this.args = args;

	this.state = MODULE_STATE_PENDING;
	this.pending = 0;
	this.callbacks = [];

	this._release = function()
	{
		if(this.pending) {
			this.pending --;
		}
		if(this.pending) {
			return;
		}

		// all imports loaded
		// release module

		this.state = MODULE_STATE_LOADED;
		try {
			if(this.body) {
				this.body.apply(null, this.args);
			}
		}
		catch(e) {
			console.error("wdlib.module["+this.name+"] : release error: ", e);

			// add to _loadstat
			_loadstat.loading--;
			_loadstat.error++;
			_loadstat.onerror(new LoaderArg(this.name), e);
			return;
		}

		if(_config.debug) {
			console.log("wdlib.module : " + this.name + " loaded");
		}

		for(var i=0; i<this.callbacks.length; ++i) {
			this.callbacks[i].method.call(this.callbacks[i].ctx);
		}

		this.callbacks = [];
	}

	// check parent loader callbacks
	var _pLoader = _loaders[this.name];
	if(_pLoader) {
		this.callbacks = _pLoader.callbacks;
		_pLoader.callbacks = [];
	}

	if(this.imports.length) {
		for(var i=0; i<this.imports.length; ++i) {
			var im = new LoaderArg(this.imports[i]);

			var _module = _modules[im.name];
			if(_module && _module.state != MODULE_STATE_LOADED) {
				_module.callbacks.push({method: this._release, ctx: this});
				this.pending++;
				continue;
			}
		
			var _loader = _loaders[im.name];
			if(!_loader) {
				_loader = new Loader(im);
				_loaders[im.name] = _loader;
			}
			if(_loader.state != LOADER_STATE_LOADED) {
				_loader.callbacks.push({method: this._release, ctx: this});
				this.pending++;
			}
		}
	}

	if(!this.pending) {
		this._release();
	}
}

function Loader(arg)
{
	this.arg = arg;
	this.state = LOADER_STATE_PENDING;
	this.callbacks = [];

	this.s = undefined;

	this._load = function()
	{
		if(_config.debug) {
			console.log("wdlib.module.loader : start loading : ", this.arg.src);
		}

		var ss = undefined;
		var t = undefined;

		switch(this.arg.type) {
			case SRC_TYPE_TEMPLATE:
				this._load_template();
				return;
			case SRC_TYPE_CSS:
				this.s = document.createElement("link");
				this.s.type = "text/css";
				this.s.rel = "stylesheet";
				this.s.href = this.arg.src;
				this.s.media = "all";
//				this.s.crossOrigin = "anonymous";
				this.s.id = this.arg.id;
		
				ss = document.getElementsByTagName("link");
				if(!ss.length) {
					// no link tags found
					// insert into HEAD
					ss = document.getElementsByTagName("head");
					t = ss[ss.length - 1];
					t.insertBefore(this.s, undefined);
				}
				else {
					t = ss[ss.length - 1];
					t.parentNode.insertBefore(this.s, t.nextSibling);
				}

				// STATE to LOADED for CSS files
				// do not wait for css files to load
				this._release();
				return;

				break;
			case SRC_TYPE_JS:
				this.s = document.createElement("script");
				this.s.type = "text/javascript";
				this.s.src = this.arg.src;
				this.s.defer = true;
				this.s.async = this.arg.async;
				this.s.crossOrigin = this.arg.crossOrigin;
				this.s.id = this.arg.id;

				if(this.arg.tries == 0) {
					// add to _loadstat
					_loadstat.total++;
					_loadstat.loading++;
				}
				this.arg.tries++;
				
//				ss = document.getElementsByTagName("script");
//				t = ss[ss.length - 1];
//				t.parentNode.insertBefore(this.s, t.nextSibling);
				document.head.appendChild(this.s);

				break;
		}

		var self = this;

		this.s.onload = this.s.onreadystatechange = function()
		{
			if(_config.debug) {
				console.log("wdlib.module.loader["+self.arg.src+"] readyState changed : ", this.readyState, this);
			}
			if((!this.readyState || this.readyState == "complete")) {
				switch(self.state) {
					case LOADER_STATE_PENDING:
						self._release();
						break;
					case LOADER_STATE_LOADED:
					default:
						// script already loaded, do nothing
						break;
				}
			}
		}
		this.s.onerror = function(e) {

			if(self.arg.tries < MAX_TRIES_TO_ERROR) {
				console.log("wdlib.module.loader["+self.arg.src+"] : try to reload in "+self.arg.retry_timeout+"msec");
				setTimeout(function() {
					self.arg.retry_timeout *= 2;
					self.arg.randomize();
					self._load();
				}, self.arg.retry_timeout);
				return;
			}

			console.error("wdlib.module.loader["+self.arg.src+"] : ERROR : ", this, e, JSON.stringify(e, ["message", "arguments", "type", "name"]), arguments);

			// add to _loadstat
			_loadstat.loading--;
			_loadstat.error++;
			_loadstat.onerror(self.arg, e);
		}
	}

	this._load_template = function()
	{
		var self = this;

		if(this.arg.tries == 0) {
			// add to _loadstat
			_loadstat.total++;
			_loadstat.loading++;
		}
		this.arg.tries++;

		var _err = function(err)
		{
			if(self.arg.tries < MAX_TRIES_TO_ERROR) {
				console.log("wdlib.module.loader["+self.arg.src+"] : try to reload in "+self.arg.retry_timeout+"msec");
				setTimeout(function() {
					self.arg.retry_timeout *= 2;
					self.arg.randomize();
					self._load();
				}, self.arg.retry_timeout);
				return;
			}
			
			console.error("wdlib.module.loader["+self.arg.src+"] : _load_template ERROR: ", err, JSON.stringify(err, ["message", "arguments", "type", "name"]), arguments);

			// add to _loadstat
			_loadstat.loading--;
			_loadstat.error++;
			_loadstat.onerror(self.arg, err);
		}

		try {
			fetch(this.arg.src)
				.then(function(response) {
					if(!response.ok) {
						throw new Error("error while loading template : " + self.arg.src);
					}
					return response.text();
				})
				.then(function(text) {
					var s = document.createElement("script");
					s.type = "text/template";
					s.id = self.arg.id;
					s.innerHTML = text;
					var t = document.getElementsByTagName("script")[0];
					t.parentNode.insertBefore(s, t);
					self._release();
				})
				.catch(function(e) {
					_err.call(undefined, e);
				});
		}
		catch(e) {
			_err.call(undefined, e);
		}
	}

	this._release = function()
	{
		if(_config.debug) {
			console.log("wdlib.module.loader["+this.arg.src+"] loaded");
		}

		this.state = LOADER_STATE_LOADED;

		for(var i=0; i<this.callbacks.length; ++i) {
			this.callbacks[i].method.call(this.callbacks[i].ctx);
		}

		this.callbacks = [];

		// add to _loadstat
		if(arg.type != SRC_TYPE_CSS) {
			_loadstat.loading--;
			_loadstat.loaded++;
			_loadstat.onload(this.arg);
		}
	}

	this._load();
}

function LoaderArg(arg)
{
	if(typeof arg === "string") {
		arg = {src: arg, name: arg};
	}
	if(typeof arg !== "object") {
		throw new Error("wdlib.module : imports items should be Object or String");
	}

	this.id = arg.id || ("wdlib.loader.item." + String(++_id));
	this.name = arg.name || arg.src;

	// check src with _config base & map
	this.src = wdlib.src(arg.src);

	// check type
	this.type = SRC_TYPE_JS;
	if(!arg.type) {
		var match = this.src.match(SRC_TYPE_REGEXP);
		if(match !== null) {
			this.type = match[1];
		}
	}
	else {
		this.type = arg.type;
	}

	this.crossOrigin = arg.crossOrigin !== undefined ? arg.crossOrigin : "anonymous";
	this.async = arg.async !== undefined ? arg.async : true;
	this.tries = 0;
	this.retry_timeout = 1000;

	this.randomize = function()
	{
		if(this.src.indexOf('?') == -1) {
			this.src += "?";
		}
		else {
			this.src += "&";
		}

		this.src += "rnd=" + Math.random();
	}
}

})));
