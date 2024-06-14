wdlib.module("wdlib/slider/slider.js", [
	"jquery",
	"wdlib/model/disposable.js",
	"wdlib/slider/format.js",
	"wdlib/utils/linked_list.js",
	"wdlib/utils/utils.js"
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.slider.Item - base slider item

class Item extends wdlib.model.Disposable {

	constructor()
	{
		super();

		this.displayObject = undefined;
		this._displayObject = undefined;
		this.visible = false;

		this.idx = 0;
		this.left = 0;
		this.top = 0;
	}

	create()
	{
		if(!this.displayObject) {
			this.createDisplayObject();
			this.setPosition();
		}
	}

	setPosition()
	{
		if (!this.displayObject) {
			return;
		}
		this.displayObject.css({
			"position" : "absolute",
			"left": this.left,
			"top": this.top
		});
	}

	createDisplayObject()
	{
	}

	onShow()
	{
//		console.log("SliderItem : onShow");
	}
	onHide()
	{
//		console.log("SliderItem : onHide");
	}

	/**
	 * @return Boolean
	 */
	show()
	{
		if(this.visible) {
			return false;
		}

		this.visible = true;

		if(!this.displayObject) {
			this.create();
		}

		this.onShow();

		return true;
	}

	/**
	 * @return Boolean
	 */
	hide()
	{
		if(!this.visible) {
			return false;
		}

		this.visible = false;
		this.onHide();

		return true;
	}
}

exports.Item = Item;
// ============================================================================

// ============================================================================
// wdlib.slider.Slider

class Slider extends wdlib.model.Disposable {

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

		this.width = this.format.slider_width || this.slider.width()/*css("width")*/;
		this.format.slider_width = this.width;
		this.height = this.format.slider_height || this.slider.height()/*css("height")*/;
		this.format.slider_height = this.height;

//		this.slider.css({width: String(this.width)+"px", height: String(this.height)+"px"});
		this.slider.css({width: this.width, height: this.height});

		this._left = wdlib.utils.intval(this.format.left);
		this._top = wdlib.utils.intval(this.format.top);
		this.content = $(".slider-content", this.slider);
		this.content.css({left: this._left, top: this._top});

		this.list = new wdlib.utils.LinkedList;
		this.current_node = undefined;

		this.animate = false;
		this._count_new_visible = 0;
		this._movement = 0;
		this._movement_delta = {};

	}

	/**
	 * @return int
	 */
	left()
	{
		return this._left;
		//return parseInt(this.content.css("left"));
	}
	/**
	 * @return int
	 */
	top()
	{
		return this._top;
		//return parseInt(this.content.css("top"));
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
		//console.log("slider append");
		var node = new wdlib.utils.LinkedListNode(item);
		this._append(node);
	}
	_append(node)
	{
		this.list.push(node);
		this.format.onNodeAppended(node);

		if(this.format.checkVisibility(node)) {
			node.data.show();
			this.content.append(node.data.displayObject);

			this.current_node = this.current_node || node;
		}
	}
	
	/**
	 * @param wdlib.slider.Item item
	 */
	prepend(item)
	{
		if(this.list.empty()) {
			this.append(item);
			return;
		}
		
		var node = new wdlib.utils.LinkedListNode(item);
		this._prepend(node);
	}
	_prepend(node)
	{
		this.list.unshift(node);
		this.format.onNodePrepended(node);

		if(this.format.checkVisibility(node)) {
			node.data.show();
			this.content.append(node.data.displayObject);

			this.current_node = this.current_node || node;
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

	/**
	 * @return wdlib.slider.Item
	 */
	remove_front()
	{
		var node = this.list.shift()
		return node ? node.data : undefined;
	}

	/**
	 * @param int d
	 * @param Function _on_complete [optional, default = undefined]
	 * @param Boolean _cycle_check [optional, default=true]
	 * @return int
	 */
	move(d, _on_complete = undefined, _cycle_check = true)
	{
		if(this.list.empty()) {
			this._movement = 0;
			return this._movement;
		}

		this._count_new_visible = 0;

		// calculate movement throw format
		this._movement = this.format.calcMovement(d);

		if(this._movement != 0) {
			// check if any new items to show exists
			this._movement_delta = {};
			this._movement_delta[(this.format.vertical ? "height" : "width")] = (-1) * this._movement;

			if(this._movement < 0) {
				wdlib.utils.LinkedList.iterate(this._cb_check_new_visible, this.current_node || this.list.head, undefined, this);
			}
			else {
				wdlib.utils.LinkedList.reverse_iterate(this._cb_check_new_visible, this.current_node || this.list.tail, undefined, this);
			}

			this._movement = this._count_new_visible ? this._movement : 0;
		}

		if(this._movement != 0) {
			// animate moving

			this.animate = true;

			var params = {};
			//params[(this.format.vertical ? "top" : "left")] = ((this._movement < 0) ? "-=" : "+=") + String(Math.abs(this._movement));
			if(this.format.vertical) {
				params.top = this._top + this._movement;
			}
			else {
				params.left = this._left + this._movement;
			}

			var self = this;
			
			this.content.animate(params,
				{
					duration: 500,
					specialEasing: {
						"left": "linear",
						"top": "linear"
					},
					complete: function()
					{
						self._top = wdlib.utils.intval(params.top);
						self._left = wdlib.utils.intval(params.left);

						self.animate = false;
						
						var node = self.current_node || self.list.head;
						wdlib.utils.LinkedList.iterate(self._cb_anim_complete, node, undefined, self);
						wdlib.utils.LinkedList.reverse_iterate(self._cb_anim_complete, node.prev, undefined, self);

						if(_on_complete !== undefined) {
							_on_complete.call();
						}
					}
				}
			);
		}
		
		if(this._movement === 0 && this.format.cycle === true && _cycle_check === true) {
			let _nodes = [];
			if(d > 0) {
				_nodes.push(this.list.shift());
			}
			else {
				_nodes.push(this.list.pop());
			}
			for(let j=0; j<_nodes.length; ++j) {
				if(d > 0) {
					this._append(_nodes[j]);
				}
				else {
					this._prepend(_nodes[j]);
				}
				_nodes[j].data.setPosition();
			}
			this._movement = this.move(d, _on_complete, false);
		}

		return this._movement;
	}
	_cb_check_new_visible(node)
	{
		if(!node.data.visible) {
			if(this.format.checkVisibility(node, this._movement_delta)) {
				var _need_add = !(node.data.displayObject);
				node.data.show();
				if(_need_add) {
					this.content.append(node.data.displayObject);
				}
				this._count_new_visible++;
			}
		}
		return node.data.visible;
	}
	_cb_anim_complete(node)
	{
		if(!node.data.visible) {
			return false;
		}

		if(!this.format.checkVisibility(node)) {
			node.data.hide();
		}
		else {
			this.current_node = node;
		}
		return true;
	}

	clear()
	{
		if(this.animate) {
			this.content.stop();
			this.animate = false;
		}

		this.list.clear(function(node) {
			node.data.dispose();
			node.data = undefined;
		});
		this.current_node = undefined;

		this.items = [];

		this.format.reset();
		this._left = wdlib.utils.intval(this.format.left);
		this._top = wdlib.utils.intval(this.format.top);
		this.content.css({left: this._left, top: this._top});
		this.content.empty();
	}

	dispose()
	{
		this.clear();
		this.format.dispose();

		super.dispose();
	}
}

exports.Slider = Slider;
// ============================================================================

}, (wdlib.slider = wdlib.slider || {}));
