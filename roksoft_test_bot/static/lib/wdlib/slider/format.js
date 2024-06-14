wdlib.module("wdlib/slider/format.js", [
	"wdlib/model/disposable.js"
],
function(exports) {
"use strict";

class Format extends wdlib.model.Disposable {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		super();
		
		this.slider = undefined;

		this.init(args);
	}

	/**
	 * @param Object args
	 */
	init(args)
	{
		args = args || {};
		this.defs = args;
		
		// slider total width
		this.slider_width = args.slider_width || 0;
		// slider total height
		this.slider_height = args.slider_height || 0;

		// slider content default position (left && top)
		this.left = args.left || 0;
		this.top = args.top || 0;

		// slider item width
		this.width = args.width || 0;
		
		// slider item height
		this.height = args.height || 0;

		// slider items padding
		this.padding = args.padding || 0;

		// scroller direction
		this.vertical = args.vertical || false;

		// if it need to cycle the slider
		this.cycle = args.cycle || false;
	}

	reset()
	{
		this.init(this.defs);
	}
	
	/**
	 * @param wdlib.utils.LinkedListNode<wdlib.slider.Item> node
	 * @param Object delta {width,height} [optional]
	 * @return Boolean
	 */
	checkVisibility(node, delta)
	{
		delta = delta || {};
		var rect = {
			left: this.slider.left() + node.data.left,
			top: this.slider.top() + node.data.top,
			width: this.width,
			height: this.height
		};
		//console.log("- checkVisibility rect",rect);
		if(
			(rect.left >= (0 + (delta.width < 0 ? delta.width : 0)) && rect.left < this.slider.width + (delta.width > 0 ? delta.width : 0))
			&& (rect.top >= (0 + (delta.height < 0 ? delta.height : 0)) && rect.top < this.slider.height + (delta.height > 0 ? delta.height : 0))
		) {
			return true;
		}

		return false;
	}

	/**
	 * @param int d
	 * @return int
	 */
	calcMovement(d)
	{
		return (-1) * Math.sign(d) * Math.abs(d) * ((this.vertical ? this.height : this.width) + this.padding);
	}

	/**
	 * @param wdlib.utils.LinkedListNode<wdlib.slider.Item> node
	 */
	onNodeAppended(node)
	{
		var _node = node.prev;
		node.data.idx = _node ? (_node.data.idx + 1) : 0;
		node.data.left = _node ? (_node.data.left + (!this.vertical ? (this.width + this.padding) : 0)) : 0;
		node.data.top = _node ? (_node.data.top + (this.vertical ? (this.height + this.padding) : 0)) : 0;

//		console.log("NODE APPENDED : ", node.data);
	}
	
	/**
	 * @param wdlib.utils.LinkedListNode<wdlib.slider.Item> node
	 */
	onNodePrepended(node)
	{
		var _node = node.next;
		node.data.idx = _node ? (_node.data.idx - 1) : 0;
		node.data.left = _node ? (_node.data.left - (!this.vertical ? (this.width + this.padding) : 0)) : 0;
		node.data.top = _node ? (_node.data.top - (this.vertical ? (this.height + this.padding) : 0)) : 0;

//		console.log("NODE APPENDED : ", node.data);
	}

	dispose()
	{
		super.dispose();

		this.slider = undefined;
	}
}

exports.Format = Format;

// ============================================================================

class FormatBatch extends Format {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		super(args);
	}

	/**
	 * @param Object args
	 */
	init(args)
	{
		args = args || {};
		super.init(args);

		this.cols = args.cols || 1;
		this.rows = args.rows || 1;

	}

	/**
	 * @param wdlib.utils.LinkedListNode<wdlib.slider.Item> node
	 */
	onNodeAppended(node)
	{
		var _node = node.prev;
		node.data.idx = _node ? (_node.data.idx + 1) : 0;

		if(!_node) {
			node.data.left = 0;
			node.data.top = 0;
			return;
		}

		var n = Math.floor(node.data.idx / this.cols);
		var _n = Math.floor(_node.data.idx / this.cols);

		if(n == _n) {
			node.data.left = _node.data.left + (this.width + this.padding);
			node.data.top = _node.data.top;
		}
		else {
			if(!this.vertical && (n % this.rows == 0)) {
				node.data.left = _node.data.left + (this.width + this.padding);
				node.data.top = _node.data.top - ((this.rows - 1) * (this.height + this.padding));
			}
			else {
				node.data.left = _node.data.left - ((this.cols - 1) * (this.width + this.padding));
				node.data.top = _node.data.top + this.height + this.padding;
			}
		}

//		console.log("NODE APPENDED : ", node.data);
	}
	/**
	 * @param wdlib.utils.LinkedListNode<wdlib.slider.Item> node
	 */
	onNodePrepended(node)
	{
		var _node = node.next;
		node.data.idx = _node ? (_node.data.idx - 1) : 0;

		if(!_node) {
			node.data.left = 0;
			node.data.top = 0;
			return;
		}

		var n = Math.floor(node.data.idx / this.cols);
		var _n = Math.floor(_node.data.idx / this.cols);

		if(n == _n) {
			node.data.left = _node.data.left - (this.width + this.padding);
			node.data.top = _node.data.top;
		}
		else {
			if(!this.vertical && (n % this.rows == 0)) {
				node.data.left = _node.data.left - (this.width + this.padding);
				node.data.top = _node.data.top + ((this.rows - 1) * (this.height + this.padding));
			}
			else {
				node.data.left = _node.data.left + ((this.cols - 1) * (this.width + this.padding));
				node.data.top = _node.data.top - this.height + this.padding;
			}
		}

//		console.log("NODE APPENDED : ", node.data);
	}

}

exports.FormatBatch = FormatBatch;

}, (wdlib.slider = wdlib.slider || {}));
