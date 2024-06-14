wdlib.module("/js/events/client.js", [
	"wdlib/events/dispatcher.js"
],
function(exports) {
"use strict";

// ============================================================================
// app.events.client CONSTANTS

exports.SWIPE_LEFT = 1;
exports.SWIPE_RIGHT = 2;
exports.SWIPE_UP = 3;
exports.SWIPE_DOWN = 4;

exports.ON_CLIENT_SWIPE = "app.events.client.ON_CLIENT_SWIPE";
exports.ON_POPUP_OPEN = "app.events.client.ON_POPUP_OPEN";
exports.ON_POPUP_CLOSE = "app.events.client.ON_POPUP_CLOSE";
exports.ON_ERROR = "app.events.client.ON_ERROR";
exports.ON_USER_IMAGE_RENDERED = "app.events.client.ON_USER_IMAGE_RENDERED";
exports.ON_UPLOAD_UPDATED = "app.events.client.ON_UPLOAD_UPDATED";
exports.ON_UPLOAD_IMAGE_RENDERED = "app.events.client.ON_UPLOAD_IMAGE_RENDERED";
exports.ON_APP_HIDE = "app.events.client.ON_APP_HIDE";
exports.ON_APP_RESTORE = "app.events.client.ON_APP_RESTORE";
exports.ON_APP_CONFIG = "app.events.client.ON_APP_RESTORE";


// ============================================================================

// ============================================================================
// app.events.client.Event class

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

}, ((this.app.events = this.app.events || {}).client = {}));
