wdlib.module("wdlib/pixi/base.js", [
	"wdlib/events/dispatcher.js",
	"wdlib/model/disposable.js",
	"wdlib/utils/utils.js"
],
function(exports) {
"use strict";

var _loading = false;
var _to_load = [];
var _map = new Map;

var _pixi = false;
var _pixi_version = "undetected";
var _pixi_major_version = 0;

// extend wdlib.pixi object from wdlib.events.EventDispatcher
wdlib.events.EventDispatcher.call(exports);

// ============================================================================
// wdlib.pixi.Event class

exports.ON_PIXI_LOADED_EVENT = "wdlib.pixi.events.ON_LOADED";
exports.IS_PIXI8 = false;

class Event extends wdlib.events.Event {

	/**
	 * @param String type
	 * @param Object data
	 */
	constructor(type, data)
	{
		super(type, data);
	}
	
}

exports.Event = Event;
// ============================================================================

// ============================================================================
// wdlib.pixi.* interface

/**
 * @param String name
 * @param String url
 * @param Fuction callback
 */
exports.load = function(name, url, callback)
{
	if(!Array.isArray(name)) {
		wdlib.pixi.load([{name: name, url: url, callback: callback}]);
		return;
	}

	// in this case we have name = array of {name: name, url: url, callback: callback}
	var items = name;

	items.forEach(function(item) {

		var req = new Req(item.name, item.url, item.callback, item.asset);

		// check if already loading
		var tl = _map.get(item.url);
		if(tl) {
			// found
			// add callback
			tl.reqs.push(req);
			return;
		}
	
		tl = new ToLoad(req.url, req.asset);
		tl.reqs.push(req);
		_map.set(tl.url, tl);

		_to_load.push(tl);
	});

	try_load();
}

exports.cache = function(key)
{
	return PIXI.Assets.cache.get(key);
}

exports.version = function()
{
	return _pixi_version;
}

exports.isLoaded = function()
{
	return _pixi;
}

// ============================================================================
// test of WEB GL

var lib = (!wdlib.Config.IS_MOBILE && isWebGLSupported()) ? "pixijs" : "pixijs-legacy";
console.log("PIXI using " + lib + " version....");
_pixi_version = lib + "-unloaded";

var re = /^([0-9])+\./;

wdlib.load([lib], function() {
	if(!PIXI) {
		// some PIXI load error!
		console.error("PIXI doesn't load!!!", PIXI);
		return;
	}
	_pixi = true;
	_pixi_version = lib + "-" + PIXI.VERSION;

	let ve_re = re.exec(PIXI.VERSION);
	if(ve_re !== null) {
		_pixi_major_version = wdlib.utils.intval(ve_re[1]);
	}
	
	console.log("PIXI.VERSION : ", _pixi_version, ", major : ", _pixi_major_version);

	if(_pixi_major_version >= 8) {
		wdlib.pixi.IS_PIXI8 = true;
	}
	else {
		// set FPS to flash defaults
		PIXI.settings.TARGET_FPMS = 0.024;
		PIXI.settings.SORTABLE_CHILDREN = true;
	}

	// custom resource loader middleware
//	PIXI.Loader.shared.use(function(resource, next) {
//		console.log("PIXI CUSTOM loader middleware : ", resource);
//		next();
//	});

	wdlib.pixi.dispatchEvent(new wdlib.pixi.Event(wdlib.pixi.ON_PIXI_LOADED_EVENT, {}));

	try_load();
});

var supported;

function isWebGLSupported() {
	if (typeof supported === 'undefined') {
		supported = (function supported() {
			var contextOptions = {
				stencil: true,
				failIfMajorPerformanceCaveat: true /*settings.FAIL_IF_MAJOR_PERFORMANCE_CAVEAT,*/
			};
			try {
				if (!window.WebGLRenderingContext) {
					return false;
				}
				var canvas = document.createElement('canvas');
				var gl = (canvas.getContext('webgl', contextOptions)
					|| canvas.getContext('experimental-webgl', contextOptions));
				var success = !!(gl && gl.getContextAttributes().stencil);
				if (gl) {
					var loseContext = gl.getExtension('WEBGL_lose_context');
					if (loseContext) {
						loseContext.loseContext();
					}
				}
				gl = null;
				return success;
			}
			catch (e) {
				return false;
			}
		})();
	}
	return supported;
}

// ============================================================================

// for pixi.version >= 7
function try_load7()
{
	if(!_pixi || !_to_load.length) {
		return;
	}

	var processLoad = function(tl)
	{
		let asset = tl.asset ? tl.asset : tl.url;
//		console.log("PIXI asset to load : ", asset);

		PIXI.Assets.load(asset).then(function(res) {
			// console.log("PXIX7 : something loaded  : ", res);

			_map.delete(tl.url);
			tl.release(res, tl.url);

		});
	}

	while(_to_load.length) {
		var tl = _to_load.shift();

		processLoad(tl);
	}
}

function try_load()
{
	// FORCE use of PIXI.v7
	try_load7();
	return;

	if(!_pixi || _loading || !_to_load.length) {
		return;
	}

	_loading = true;

	while(_to_load.length) {
		var res = undefined;
		var tl = _to_load.shift();

		// check if already loaded
		if(res = PIXI.Loader.shared.resources[tl.url]) {
			tl.release(res, tl.url, PIXI.Loader.shared.resources);
			_map.delete(tl.url);
			continue;
		}

		PIXI.Loader.shared.add(tl.url, tl.url);
	}

	PIXI.Loader.shared.load(function(loader, resources) {
//		console.log("PIXI LOADER RESOURSES : ", resources);

		for(var res in resources) {
			var tl = _map.get(resources[res].url);
			if(!tl) {
				// some error
				continue;
			}

			tl.release(resources[res], res, resources);
			_map.delete(tl.url);
		}

		_loading = false;
		try_load();
	});
}

// ============================================================================
// internal classes

class Req extends wdlib.model.Disposable {

	constructor(name, url, callback, asset)
	{
		super();

		this.name = name;
		this.url = url;
		this.asset = asset;
		this.callback = callback;
	}

	dispose()
	{
		super.dispose();

		this.callback = undefined;
		this.name = undefined;
		this.url = undefined;
		this.asset = undefined;
	}
}

class ToLoad {

	constructor(url, asset = undefined)
	{
		this.url = url;
		this.asset = asset;
		this.reqs = [];
	}

	release(data, name, resources)
	{
		for(var i=0; i<this.reqs.length; ++i) {
			this.reqs[i].callback.call(undefined, data, name, resources);
			this.reqs[i].dispose();
		}
		this.reqs = [];
	}
}

// ============================================================================

}, (wdlib.pixi = wdlib.pixi || {}));
