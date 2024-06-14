wdlib.module("wdlib/form/base.js", [
	"jquery",
	"wdlib/utils/utils.js",
	"wdlib/utils/flex.js"
],
function(exports) {
"use strict";

// ===============================================================================
// wdlib.form.Base class

class Base extends wdlib.utils.FlexContainer {

	constructor(displayObject)
	{
		super(displayObject);

		var self = this;

		this.displayObject.on("submit", function(event) {
			// validate form

			if(!self.validate()) {
				// some error
				event.preventDefault();
				return;
			}

			self.onSubmit(event);
		});
	}

	submit()
	{
		this.displayObject.submit();
	}

	onSubmit(event)
	{
	}

	validate()
	{
		var ok = true;

		return ok;
	}

	dispose()
	{
		super.dispose();

		this.displayObject.off("submit");
	}
}

exports.Base = Base;
// ===============================================================================

// ============================================================================
// wdlib.form.FormGroup class

class FormGroup extends wdlib.utils.FlexContainer {

	constructor(_displayObject)
	{
		super(_displayObject.parents(".form-group"));

		this.input = _displayObject;
		this.error = undefined;
	}

	clear()
	{
		this.input.val("");
		this.input.removeClass("is-invalid");
		if(this.error) {
			this.error.text("");
		}
	}

	val()
	{
		return this.input.val.apply(this.input, arguments);
	}
	intval()
	{
		return wdlib.utils.intval(this.val());
	}

	err(str)
	{
		if(str === false) {
			// remove error
			this.input.removeClass("is-invalid");
			this.displayObject.removeClass("is-invalid");
			return;
		}

		if(!this.error) {
			// find
			this.error = $(".invalid-feedback", this.displayObject);
			if(!this.error.length) {
				// create new one
				this.error = $("<div class=\"invalid-feedback\">").appendTo(this.displayObject);
			}
		}

		if(!this.input.hasClass("is-invalid")) {
			this.input.addClass("is-invalid");
			this.displayObject.addClass("is-invalid");
		}

		this.error.text(str);
	}
}

exports.FormGroup = FormGroup;

class FormGroupCheckbox extends FormGroup {

	constructor(_displayObject)
	{
		super(_displayObject);
	}

	val()
	{
		return this.isChecked() ? super.val() : 0;
	}

	isChecked()
	{
		return this.input.is(":checked");
	}
}

exports.FormGroupCheckbox = FormGroupCheckbox;
// ============================================================================

}, (wdlib.form = wdlib.form || {}));
