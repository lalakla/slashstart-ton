wdlib.module("/js/view/main.js", [
	"jquery",
	"template7",
	"bootstrap",
	"wdlib/utils/template7.js",
	"wdlib/utils/linked_list.js",
	"/js/view/popup/base.js",
],
function(exports) {
"use strict";

var _body = $("body");
var _container = $(".global-container", _body);

var _animate_layer = undefined;
var _waiter_layer = undefined;

var _popups = new wdlib.utils.LinkedList;

// подписываемся на кнопку - назад
$(window).on("popstate", function(event) {
//	event.stopPropagation();
//	event.preventDefault();

//	alert("POP : " + event.state);
//	console.log("POP : ", event.originalEvent.state);

	onBackClick();
});

// ===============================================================================
// app.view.Main interface

exports.init = function()
{
	console.log("app.view.Main::init");

	// init global waiter container
	_waiter_layer = $("#global-waiter-layer", _body);
	_animate_layer = $("#global-animate-layer", _body);
}

exports.body = function()
{
	return _body;
}
exports.container = function()
{
	return _container;
}

exports.showWaiter = function()
{
	if(_waiter_layer) {
		_waiter_layer.css({
			display: "block"
		});
	}
}
exports.hideWaiter = function()
{
	if(_waiter_layer) {
		_waiter_layer.css({
			display: "none"
		});
	}
}
exports.startAnimate = function()
{
	if(_animate_layer) {
		_animate_layer.css({
			display: "block"
		});
	}

	return _animate_layer;
}
exports.stopAnimate = function()
{
	if(_animate_layer) {
		_animate_layer.css({
			display: "none"
		});
	}
}

exports.addPopup = function(type, args)
{
	var popup = app.view.popup.create(type, args);

	if(_popups.length) {
		// sleep previous popup
		_popups.first().data.sleep();
	}
	else {
		// первый попап

		// обязательно добавляем первый элемент в window.History
		window.history.pushState({popup_id: popup.popup_id}, "", "");
	}

	_body.prepend(popup.popup);

	popup.modal();
	popup.awake();

	_popups.unshift(new wdlib.utils.LinkedListNode(popup));
}

exports.closePopup = function(popup)
{
	// find in _popups
	wdlib.utils.LinkedList.iterate(function(node) {
		if(node.data.popup_id == popup.popup_id) {
			_popups.erase(node);
			return false;
		}
		return true;
	}, _popups.first(), undefined, null);

	popup.popup.remove();

	if(_popups.length && !_popups.first().data.popup_active) {
		// awake previous popup
		_popups.first().data.awake();

		// а здесь не снимаю старый - но обновляю ему значение
		window.history.replaceState({popup_id: _popups.first().data.popup_id}, "", "");
	}
}

exports.getPopup = function()
{
	let popup = _popups.length ? _popups.first().data : undefined;
	return popup;
}
// ===============================================================================

// ===============================================================================
// internal functions

function onBackClick()
{
	// проверяем попапы сначала
	// и если есть - закрываем
	let popup = app.view.Main.getPopup();
	if(popup) {
		// тупой History API - снимает элемент и херит его
		// поэтому насильно добавляю ещё один!
		// добвляю с текущим значением - там, в popop.close - он заменится на значение предыдущего попапа, если было
		window.history.pushState({popup_id: popup.popup_id}, "", "");

		popup.close();

		return;
	}

}
// ===============================================================================

}, ((this.app.view = this.app.view || {})).Main = {});

