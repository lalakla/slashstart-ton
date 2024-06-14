wdlib.module("/js/controller/poll.js", [
	"wdlib/net/poll.js",
	"/js/controller/base.js",
	"/js/events/poll.js"
],
function(exports) {
"use strict";

// extend app.controller.Poll object from app.controller.Base
app.controller.Base.call(exports);

// ===============================================================================
// app.controller.Poll interface

exports.run = function()
{
	// console.log("app.controller.Poll::run");

	var url = wdlib.Config.PROJECT_URL + "/lpoll";
	wdlib.net.Poll.init(url, app.Main.currentUser.id, onSocketData);
	
	// test
//	app.controller.Poll.addEventListener(app.events.poll.ECHO_EVENT, onEchoEvent);
//	app.controller.Main.server("vk.app.main", "poll_echo", {});
	
}

exports.stop = function()
{
	wdlib.net.Poll.stop();
}

// ===============================================================================

function onSocketData(data)
{
	console.log("app.controller.Poll::onSocketData : ", data);

	// processing event
	for(var i=0; i<data.length; ++i) {
		var ev = new app.events.poll.Event(data[i]);
		app.controller.Poll.dispatchEvent(ev);
	
		var evt = new app.events.server.Event(app.events.server.ON_POLL_EVENT, {e: ev});
		app.controller.Poll.dispatchEvent(evt);
	}
}

function onEchoEvent(event)
{
	console.log("onEchoEvent : ", event);
}

}, ((this.app.controller = this.app.controller || {})).Poll = {});

