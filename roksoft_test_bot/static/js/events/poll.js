wdlib.module("/js/events/poll.js", [
	"wdlib/events/dispatcher.js"
],
function(exports) {
"use strict";

// ============================================================================
// app.events.task CONSTANTS

exports.ECHO_EVENT = "PollEchoEvent";
exports.BROADCAST_EVENT = "PollBroadcastEvent";

// ============================================================================

// ============================================================================
// app.events.poll.Event class

class Event extends wdlib.events.Event {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args.type || exports.ECHO_EVENT, {});

		this.user_id = "";
		this.users = [];

		this.init(args);
	}
	/**
	 * @param Object args
	 */
	init(args)
	{
		this.user_id = args.user_id || this.user_id;
		this.data = args.data || this.data;

		if(args.hasOwnProperty("users")) {
			this.users = [];

			for(var i=0; i<args.users.length; ++i) {
				var u = new app.model.user.User(args.users[i]);
				app.model.user.setUser(u);

				this.users.push(u);
			}
		}
	}
	
}

exports.Event = Event;
// ============================================================================

}, ((this.app.events = this.app.events || {}).poll = {}));
