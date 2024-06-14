wdlib.module("/js/view/popup/alert.js", [
	"wdlib/button/base.js",
	"/js/view/popup/base.js",
	{id: "alert-popup-template", src: "/templates/popup/alert.html", type: "template"}
],
function(exports) {
"use strict";

var _tmpl = undefined;

// ============================================================================
// app.view.popup.Alert class

class Alert extends app.view.popup.Base {

	constructor(args)
	{
		super(args);

		this.title = args.title;
		this.text = args.text;
		this._callback = args.callback;
	}

	createPopup()
	{
		super.createPopup();
		
		if(_tmpl === undefined) {
			// compile template
			_tmpl = Template7.compile($("#alert-popup-template").html());
		}

		// small popup
		$(".modal-dialog", this.popup).addClass("modal-sm");

		var data = {
			title: this.title,
			text: this.text
		};
		this.content.append($(_tmpl(data)));

		// ok button
		this.addDisposable(new wdlib.button.Base($(".btn-primary", this.content), {context: this}, function(btn) {
			this.close();
		}));

		// close popup button
		this.addDisposable(new wdlib.button.Base($(".close-popup-button", this.content), {context: this}, function(btn) {
			this.close();
		}));
	}

	dispose()
	{
		//console.log("ALERT_POPUP : DISPOSE");

		super.dispose();
	}

	onBtnOkClick()
	{
		this.close();
	}

	onClose()
	{
		var ff = this._callback;

		super.onClose();

		if(ff) {
			ff.call();
		}
	}
}

exports.Alert = Alert;
// ============================================================================

}, (this.app.view.popup = this.app.view.popup || {}));
