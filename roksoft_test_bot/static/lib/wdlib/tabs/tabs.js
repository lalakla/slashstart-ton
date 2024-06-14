wdlib.module("wdlib/tabs/tabs.js", [
	"jquery",
	"wdlib/model/disposable.js",
	"wdlib/button/base.js"
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.tabs.Tab - base tab class

class Tab extends wdlib.model.Disposable {

	constructor()
	{
		super();

		this.active = false;
		
		this.btn = undefined;
		this.index = undefined;
		this.displayObject = undefined;
	}

	activate()
	{
		this.active = true;
	}
	deactivate()
	{
		this.active = false;
	}

	dispose()
	{
		super.dispose();

		if(this.btn) {
			this.btn.dispose();
			this.btn = undefined;
		}
	}
}

exports.Tab = Tab;
// ============================================================================

// ============================================================================
// wdlib.tabs.Content - base content class

class Content extends wdlib.model.Disposable {

	constructor()
	{
		super();

		this.active = false;
		this.index = undefined;
		this.displayObject = undefined;
	}

	show()
	{
		this.active = true;

		if(!this.displayObject) {
			this.createDisplayObject();
		}
	}
	hide()
	{
		this.active = false;
	}

	createDisplayObject()
	{
	}
}

exports.Content = Content;
// ============================================================================

// ============================================================================
// wdlib.tabs.Manager - tabs manager class

class Manager extends wdlib.model.Disposable {

	constructor()
	{
		super();

		this._items = [];
		this._active = undefined;
	}

	/**
	 * @return wdlib.tabs.Content
	 */
	active()
	{
		return this._active ? {
			tab: this._active.tab,
			content: this._active.content
		} : undefined;
	}

	/**
	 * @param wdlib.tabs.Tab tab
	 * @param wdlib.tabs.Content content
	 * @return Object {tab, content}
	 */
	add(tab, content)
	{
		var item = new Item(tab, content);
		this._items.push(item);

		item.tab.index = this._items.length - 1;
		item.content.index = this._items.length - 1;

		item.tab.btn = new wdlib.button.Base(item.tab.displayObject, {context: this, data: {index: item.tab.index}}, this.onTabClick);
		return item;
	}

	/**
	 * @param int idx
	 */
	open(idx)
	{
		var item = this._items[idx];
		if(!item) {
			console.log("wdlib.tabs.Manager::open: ERROR, incorrect idx ", idx);
			return;
		}

		if(item.tab.active) {
			// already opened
			// do nothing
			return;
		}

		if(this._active) {
			this._active.hide();
		}

		this._active = item;
		this._active.show();
	}

	onTabClick(btn)
	{
		this.open(btn.data.index);
	}

	dispose()
	{
		super.dispose();

		for(var i=0; i<this._items.length; ++i) {
			this._items[i].dispose();
		}
		this._items = undefined;
		this._active = undefined;
	}
}

exports.Manager = Manager;
// ============================================================================

// ============================================================================
// internal Manager's Item class
class Item extends wdlib.model.Disposable {

	/**
	 * @param wdlib.tabs.Tab tab
	 * @param wdlib.tabs.Content content
	 */
	constructor(tab, content)
	{
		super();

		this.tab = tab;
		this.content = content;
	}

	show()
	{
		this.tab.activate();
		this.content.show();
	}
	hide()
	{
		this.tab.deactivate();
		this.content.hide();
	}

	dispose()
	{
		super.dispose();

		this.tab.dispose();
		this.content.dispose();

		this.tab = undefined;
		this.content = undefined;
	}
}
// ============================================================================

}, (wdlib.tabs = wdlib.tabs || {}));
