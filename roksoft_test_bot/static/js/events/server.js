wdlib.module("/js/events/server.js", [
	"wdlib/events/dispatcher.js"
],
function(exports) {
"use strict";

// ============================================================================
// app.events.server CONSTANTS

exports.ON_SERVER_RESPONSE = "app.events.server.ON_SERVER_RESPONSE";
exports.ON_POLL_EVENT = "app.events.server.ON_POLL_EVENT";
exports.ON_USER_LOADED = "app.events.server.ON_USER_LOADED";
exports.ON_FRIENDS_LOADED = "app.events.server.ON_FRIENDS_LOADED";
exports.ON_FRIEND_LOADED = "app.events.server.ON_FRIEND_LOADED";

// ============================================================================

// ============================================================================
// app.events.server.Event class

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

}, ((this.app.events = this.app.events || {}).server = {}));
