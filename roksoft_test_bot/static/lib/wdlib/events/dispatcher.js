wdlib.module("wdlib/events/dispatcher.js", [
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.events.Event class

/**
 * Base Event class
 *
 * @param String type
 * @param Object data
 */
var Event = function(type, data)
{
	this.type = type;
	this.data = data || {};
	this.target = null;

	this._propagation = true;

	this.stopPropagation = function() {
		this._propagation = false;
	}
}
exports.Event = Event;
// ============================================================================

// ============================================================================
// wdlib.events.EventDispatcher class

var EventDispatcher = function()
{
	this.listeners = new Map;

	var waiting = false;	
	var queue = [];

	this.hasEventListener = function(type)
	{
		var batch = this.listeners.get(type);
		return batch && !batch.empty();
	}

	this.addEventListener = function(type, callback, context)
	{
		queue.push({
			method: this._addEventListener,
			args: [type, callback, context]
		});
		this._try_next();
	}
	this._addEventListener = function(type, callback, context)
	{
		var batch = this.listeners.get(type);
		if(!batch) {
			batch = new Batch;
			this.listeners.set(type, batch);
		}
		batch.add(callback, context);
	};

	this.removeEventListener = function(type, callback, context)
	{
		queue.push({
			method: this._removeEventListener,
			args: [type, callback, context]
		});
		this._try_next();
	}
	this._removeEventListener = function(type, callback, context)
	{
		var batch = this.listeners.get(type);
		if(batch) {
			batch.remove(callback, context);
			if(batch.empty()) {
				this.listeners.delete(type);
			}
		}
	};

	this.dispatchEvent = function(event) {
		queue.push({
			method: this._dispatchEvent,
			args: [event]
		});
		this._try_next();
	}
	this._dispatchEvent = function(event)
	{
		var batch = this.listeners.get(event.type);
		if(batch) {
			event.target = this;
			batch.dispatch(event);
		}
	};
	this.dispatchData = function(type, event) {
		queue.push({
			method: this._dispatchData,
			args: [type, event]
		});
		this._try_next();
	}
	this._dispatchData = function(type, event)
	{
		var batch = this.listeners.get(type);
		if(batch) {
			batch.dispatch(event);
		}
	};

	this._try_next = function() {
		if(waiting || !queue.length) {
			return;
		}

		waiting = true;
		var s = queue.shift();

		s.method.apply(this, s.args);

		waiting = false;
		this._try_next();
	}
}

exports.EventDispatcher = EventDispatcher;

// ============================================================================
// internal classes 

var Batch = function()
{
	this.listeners = [];
	this.ctxx = new Map;

	this.add = function(callback, context)
	{
		if(!context) {
			this.listeners.push(callback);
		}
		else {
			var ctx = this.ctxx.get(context);
			if(!ctx) {
				ctx = [];
				this.ctxx.set(context, ctx);
			}
			ctx.push(callback);
		}
	}
	this.remove = function(callback, context)
	{
		if(!context) {
			this.listeners = this.listeners.filter(function(listener) {
				return listener.toString() !== callback.toString(); 
			});
		}
		else {
			this.ctxx.delete(context);
		}
	}
	this.dispatch = function(event)
	{
		for(var i=0; i<this.listeners.length; ++i) {
			if(!event._propagation) {
				// stopPropagation called
				// stop dispatching
				break;
			}
			this.listeners[i].call(undefined, event);
		}

		this.ctxx.forEach(function(listeners, ctx) {
			for(var i=0; i<listeners.length; ++i) {
				if(!event._propagation) {
					// stopPropagation called
					// stop dispatching
					break;
				}
				listeners[i].call(ctx, event);
			}
		});
	}

	this.empty = function()
	{
		return !this.listeners.length && !this.ctxx.size;
	}
}

// ============================================================================

}, (this.wdlib.events = this.wdlib.events || {}));
