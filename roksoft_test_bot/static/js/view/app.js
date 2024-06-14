wdlib.module("/js/view/app.js", [
	"template7",
	"wdlib/api/base.js",
	"wdlib/button/base.js",
	"/js/view/ton-connect/ui.js",
	"/js/utils/loader.js",
	"/js/utils/template7.js",
	{id: "app-template", src: "/templates/app.html", type: "template"},
],
function(exports) {
"use strict";

var _top_global_container = undefined;
var _bottom_global_container = undefined;
var _preloader = undefined;

var _refresh = undefined;

var _to_height = 0;
var _resizing = false;
var _onResize = function(res, width, height)
{
//	console.log("RESIZE COMPLETE : ", res, height);
	_resizing = false;

//	if(_to_height != height) {
	if(Math.abs(_to_height - height) > 5) {
		_resizing = true;
//		console.log("RESIZE TO : ", _to_height);
		wdlib.api.IApi.setAppSize(0, _to_height, _onResize);
	}
}
var _size = new ResizeObserver(function(entries, observer) {
	for(let entry of entries) {
		let item = $(entry.target);
		_to_height = item.height() + _bottom_global_container.height();

		if(!_resizing) {
			_resizing = true;
//			console.log("RESIZE TO : ", _to_height);
			wdlib.api.IApi.setAppSize(0, _to_height, _onResize);
		}

//		console.log("RESIZE OBSERVER : ", _to_height);
	}
});

var _templ = Template7.compile($("#app-template").html());

// ===============================================================================
// app.view.App interface

exports.init = function()
{
	console.log("app.view.App::init");

	wdlib.setOnLoad(undefined);

	let _container = app.view.Main.container();

	// hide preloader
	_preloader = new app.utils.Loader($("#global-preloader", _container));
	_preloader.hide();

	// draw main screen
	_container.prepend($(_templ({
		user: app.Main.currentUser,
	})));

	_top_global_container = $(".top-global-container", _container);
	_bottom_global_container = $(".bottom-global-container", _container);

	if(wdlib.api.isApi(wdlib.api.API_VK)) {
		_container.addClass("global-is-vk");
	}

	app.view.tonConnect.UI.init($("#ton-connect-button-wrapper", _top_global_container), _top_global_container);

	// обновление размера ифрейма для ВК веб версии
	if(!wdlib.Config.IS_MOBILE && wdlib.api.isApi(wdlib.api.API_VK)) {
		_size.observe(_top_global_container.get(0));
	}
}

// ===============================================================================

// ===============================================================================
// internal functions

// ===============================================================================

}, ((this.app.view = this.app.view || {})).App = {});

