wdlib.module("/js/view/popup/base.js", [
	"/css/popup.css",
	"jquery",
	"template7",
	"bootstrap",
	"wdlib/model/disposable.js",
	"wdlib/button/base.js",
	{id: "base-popup-template", src: "/templates/popup/base.html", type: "template"}
],
function(exports) {
"use strict";

var _tmpl = undefined;
var _popup_id = 0;

//=============================================================================
// app.view.popup methods

/**
 * CREATE POPUP METHOD
 * @param className _class_
 * @param Object args
 * 
 * @return app.view.bpopup.Base
 */
exports.create = function(_class_, args)
{
	// console.log("CREATE POPUP : _class_=", _class_);

	var popup = new _class_(args);
	popup.createPopup();
	
	return popup;
}

exports.nextPopupId = function()
{
	return ++_popup_id;
}

//=============================================================================

// ============================================================================
// app.view.popup.Base ABSTRACT [PRE BASE] POPUP CLASS

class Abstract extends wdlib.model.Disposable {

	constructor(args)
	{
		super();

		this.popup_id = app.view.popup.nextPopupId();
		this.popup_active = false;

		this.popup = undefined;
		this.content = undefined;

		this._modal_props = {
			keyboard: false
		}
		this._modal = undefined;
	}

	createPopup(data)
	{
	}

	dispose()
	{
//		console.log("app.view.popup.Abstract : DISPOSE : ", this);

		super.dispose();

		this.popup.off("hidden.bs.modal");

//		this.popup.modal("dispose");
		this._modal.dispose();
	}
	_close()
	{
		this.onClose();

		this.dispose();
		app.view.Main.closePopup(this);
	}
	close()
	{
		if(this.popup_active) {
//			this.popup.modal("hide");
			this._modal.hide();
		}
		else {
			this._close();
		}
	}

	modal()
	{
//		this.popup.modal(this._modal_props);

		this._modal = new bootstrap.Modal(this.popup.get(0), this._modal_props);
	}

	awake()
	{
//		console.log("app.view.popup.Abstract : AWAKE : ", this);

		this.popup_active = true;

		var self = this;

		// add listeners to show & hide popup
		this.popup.on("hidden.bs.modal", function(data) {
//			console.log("app.view.popup.Abstract : HIDDEN : ", data);
			self._close();
		});

//		this.popup.modal("show");
		this._modal.show();
	}
	sleep()
	{
//		console.log("app.view.popup.Abstract : SLEEP : ", this);

		this.popup_active = false;

		this.popup.off("hidden.bs.modal");

//		this.popup.modal("hide");
		this._modal.hide();
	}

	onClose()
	{
	}
}

exports.Abstract = Abstract;
//=============================================================================

// ============================================================================
// app.view.popup.Base BASE POPUP CLASS

class Base extends app.view.popup.Abstract {

	constructor(args)
	{
		super(args);

		this._modal_props = Object.assign(this._modal_props, {
			keyboard: true
		});
	}

	createPopup(data)
	{
		data = Object.assign({
		}, data);

		if(_tmpl === undefined) {
			// compile template
			_tmpl = Template7.compile($("#base-popup-template").html());
		}

		this.popup = $(_tmpl(data));
		this.content = $(".modal-content", this.popup);
	}
}

exports.Base = Base;
// ============================================================================

}, (this.app.view.popup = this.app.view.popup || {}));
