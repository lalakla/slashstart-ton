wdlib.module("/js/view/popup/local-user.js", [
	"/css/popup/local-user.css",
	"wdlib/button/base.js",
	"wdlib/utils/utils.js",
	"/js/view/popup/base.js",
	{id: "local-user-popup-template", src: "/templates/popup/local-user.html", type: "template"}
],
function(exports) {
"use strict";

var _tmpl = undefined;

// ============================================================================
// app.view.popup.LocalUser class

class LocalUser extends app.view.popup.Base {

	constructor(args)
	{
		super(args);

		this.users = args.users || [];
		this.callback = args.callback;
	}
	
	createPopup()
	{
		super.createPopup();

		if(_tmpl === undefined) {
			// compile template
			_tmpl = Template7.compile($("#local-user-popup-template").html());
		}

		// big popup
		$(".modal-dialog", this.popup).addClass("modal-lg");

		// add custom class
		this.popup.addClass("local-user-popup");

		var data = {
			title: "Выбери пользователя",
			users: this.users
		};
		this.content.append(_tmpl(data));

		var self = this;

		$(".user", this.content).each(function() {
			var btn = undefined;
			var idx = wdlib.utils.intval($(this).attr("user-idx"));
			var user = self.users[idx];
			self.addDisposable(btn = new wdlib.button.Base($(this), {context: self, data: {user: user}}, self.onClick));
			btn.displayObject.attr("title", user.name + " [id: " + user.remote_id + "]");
		});
	}

	onClick(btn)
	{
//		console.log("ON USER CLICK : ", btn.data.user);

		if(this.callback) {
			this.callback.call(null, btn.data.user);
			this.close();
		}
	}
}

exports.LocalUser = LocalUser;
// ============================================================================

}, (this.app.view.popup = this.app.view.popup || {}));
