wdlib.module("wdlib/slider/scroller.js", [
	"jquery",
	"wdlib/model/disposable.js",
	"wdlib/slider/slider.js",
	"wdlib/slider/format.js",
	"wdlib/utils/linked_list.js",
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.slider.Scroller class

class Scroller extends wdlib.model.Disposable {

	/**
	 * @param jQuery slider
	 * @param wdlib.slider.Format format
	 */
	constructor(slider, format)
	{
		super();

		this.slider = slider;

		this.format = format;
		this.format.slider = this;

		this.width = this.format.slider_width || this.slider.width();
		this.format.slider_width = this.width;
		this.height = this.format.slider_height || this.slider.height();
		this.format.slider_height = this.height;

		this.slider.css({width: this.width, height: this.height});

		this.content = $(".scroller-content", this.slider);
		this.content.css({
			position: "relative",
			left: this.format.left,
			top: this.format.top
		});

		this.list = new wdlib.utils.LinkedList;
		this.current_node = undefined;

		var self = this;

		this.slider.on("scroll", function() {
			//console.log("SCROLLER SCROLL : ", arguments);

			var node = self.current_node || self.list.head;
			wdlib.utils.LinkedList.iterate(self._cb_scroll_complete, node, undefined, self);
			wdlib.utils.LinkedList.reverse_iterate(self._cb_scroll_complete, node.prev, undefined, self);
		});
	}

	/**
	 * @return int
	 */
	left()
	{
		return parseInt(this.content.css("left"));
	}
	/**
	 * @return int
	 */
	top()
	{
		return -this.slider.scrollTop();
	}

	/**
	 * @return int
	 */
	length()
	{
		return this.list.length;
	}

	/**
	 * @return wdlib.slider.Item 
	 */
	current()
	{
		return this.current_node ? this.current_node.data : undefined;
	}

	/**
	 * @param wdlib.slider.Item item
	 */
	append(item)
	{
		var node = new wdlib.utils.LinkedListNode(item);
		this.list.push(node);
		this.format.onNodeAppended(node);

		//console.log("append",item);

		if(this.format.checkVisibility(node))
		{
			//console.log("show",node);
			node.data.show();
			this.content.append(node.data.displayObject);
			this.current_node = this.current_node || node;
		}
		else {
			//console.log("!!!show",node);
			node.data._displayObject = $("<div />").css({
				"position": "absolute",
				"left": node.data.left,
				"top": node.data.top,
				"width": this.format.width,
				"height": this.format.height
			});
			this.content.append(node.data._displayObject);
		}
	}

	/**
	 * @param wdlib.slider.Item item
	 */
	remove(item)
	{
		var self = this;
		var removed = false;
		wdlib.utils.LinkedList.iterate(function(node) {
			if(node.data.idx == item.idx) {
				removed = true;
				if(node.data.displayObject) {
					node.data.displayObject.remove();
				}
				node.data.dispose();
				self.list.erase(node);

				return true;
			}

			if(removed) {
				self.format.onNodeAppended(node);
				var need_append = true;
				if(node.data.displayObject) {
					// node created
					// change left & top
					node.data.setPosition();
					need_append = false;
				}
				if(self.format.checkVisibility(node)) {
					node.data.show();
					if(need_append) {
						self.content.append(node.data.displayObject);
					}
				}
			}

			return true;
		}, this.list.head, undefined);
	}

	_cb_scroll_complete(node)
	{
		var visible = node.data.visible;
		
		if(this.format.checkVisibility(node)) {
			node.data.show();
			if(node.data._displayObject) {
				node.data._displayObject.after(node.data.displayObject);
				node.data._displayObject.remove();
				node.data._displayObject = undefined;
			}
			this.current_node = node;
		}
		else {
			node.data.hide();
		}

		return visible ? (true) : (node.data.visible ? true : false);
	}

	clear()
	{
		this.list.clear(function(node) {
			node.data.dispose();
			node.data = undefined;
		});
		this.current_node = undefined;

		this.items = [];

		this.format.reset();
		this.content.css({left: this.format.left, top: this.format.top});
		this.slider.scrollTop(0);
		this.content.empty();
	}

	dispose()
	{
		super.dispose();

		this.clear();
		this.format.dispose();

		this.slider.off("scroll");
	}
}

exports.Scroller = Scroller;
// ============================================================================

}, (wdlib.slider = wdlib.slider || {}));
