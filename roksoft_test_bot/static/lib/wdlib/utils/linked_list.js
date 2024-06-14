wdlib.module("wdlib/utils/linked_list.js", [
],
function(exports) {
"use strict";

class LinkedListNode {

	/**
	 * @param Object data
	 */
	constructor(data)
	{
		this.prev = undefined;
		this.next = undefined;

		this.data = data;
	}
}

class LinkedList {

	constructor()
	{
		this.head = undefined;
		this.tail = undefined;

		this.length = 0;
	}

	empty()
	{
		return !this.head;
	}

	/**
	 * @return wdlib.utils.LinkedListNode
	 */
	first()
	{
		return this.head;
	}
	/**
	 * @return wdlib.utils.LinkedListNode
	 */
	last()
	{
		return this.tail;
	}

	/**
	 * @param wdlib.utils.LinkedListNode pos
	 * @param wdlib.utils.LinkedListNode node
	 * @return wdlib.utils.LinkedListNode
	 */
	insert(pos, node)
	{
		if(!this.head || !pos) {
			return this.push(node);
		}
		if(pos == this.head) {
			return this.unshift(node);
		}

		node.prev = pos.prev;
		node.next = pos;
		node.prev.next = node;
		pos.prev = node;
		
		this.length++;

		return node;
	}

	/**
	 * @param wdlib.utils.LinkedListNode node
	 * @return wdlib.utils.LinkedListNode
	 */
	push(node)
	{
		if(!this.head) {
			this.head = node;
		}

		if(this.tail) {
			this.tail.next = node;
		}

		node.prev = this.tail;
		this.tail = node;

		this.length++;

		return node;
	}

	/**
	 * @return wdlib.utils.LinkedListNode
	 */
	pop()
	{
		var node = this.tail;

		if(node) {
			this.tail = node.prev;
			if(this.tail) {
				this.tail.next = undefined;
			}
			this.length--;

			node.prev = undefined;
			node.next = undefined;
		}

		if(!this.length) {
			this.head = undefined;
		}

		return node;
	}

	/**
	 * @param wdlib.utils.LinkedListNode node
	 * @return wdlib.utils.LinkedListNode
	 */
	unshift(node)
	{
		if(!this.tail) {
			this.tail = node;
		}

		if(this.head) {
			this.head.prev = node;
		}

		node.next = this.head;
		this.head = node;

		this.length++;

		return node;
	}

	/**
	 * @return wdlib.utils.LinkedListNode
	 */
	shift()
	{
		var node = this.head;

		if(node) {
			this.head = node.next;
			if(this.head) {
				this.head.prev = undefined;
			}
			this.length--;

			node.prev = undefined;
			node.next = undefined;
		}

		if(!this.length) {
			this.tail = undefined;
		}

		return node;
	}

	/**
	 * @param wdlib.utils.LinkedListNode node
	 */
	erase(node)
	{
		if(!node) {
			return;
		}
		if(node === this.head) {
			this.shift();
		}
		else if(node === this.tail) {
			this.pop();
		}
		else {
			node.prev.next = node.next;
			node.next.prev = node.prev;
			this.length--;
		}
	}

	/**
	 * @param Function callback [optional]
	 * @param Object context [optional]
	 */
	clear(callback, context)
	{
		while(this.head) {
			var node = this.head;
			this.head = node.next;

			node.next = undefined;
			node.prev = undefined;

			if(callback) {
				callback.call(context, node);
			}
		}

		this.tail = undefined;
		this.length = 0;
	}

	/**
	 * @param Function callback
	 * @param wdlib.utils.LinkedListNode begin [optional]
	 * @param wdlib.utils.LinkedListNode end [optional]
	 * @param Object context [optional]
	 */
	static iterate(callback, begin, end, context)
	{
//		begin = begin || this.head;
//		end = end || this.tail;

		do {
			if(begin) {
				var node = begin;
				begin = begin.next;
				
				if(callback.call(context, node) === false) {
					return;
				}
			}
		} while(begin && begin != end);
	}

	/**
	 * @param Function callback
	 * @param wdlib.utils.LinkedListNode begin [optional]
	 * @param wdlib.utils.LinkedListNode end [optional]
	 * @param Object context [optional]
	 */
	static reverse_iterate(callback, begin, end, context)
	{
//		begin = begin || this.tail;
//		end = end || this.head;

		do {
			if(begin) {
				var node = begin;
				begin = begin.prev;

				if(callback.call(context, node) === false) {
					return;
				}
			}
		} while(begin && begin != end);
	}
}

exports.LinkedListNode = LinkedListNode;
exports.LinkedList = LinkedList;

}, ((wdlib.utils = wdlib.utils || {})));
