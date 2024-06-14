wdlib.module("wdlib/model/user/photo.js", [
	"wdlib/model/base.js",
	"wdlib/utils/utils.js",
	"wdlib/utils/string.js"
],
function(exports) {
"use strict";

var PIC_TEST_PTRN = /^https{0,1}:\/\//i;

// ============================================================================
// wdlib.model.user.Photo class

class Photo extends wdlib.model.Base {

	constructor(args)
	{
		args = args || {};
		super(args);

		this.user_id = "";
		this.idx = 0;
		this.small_photo = wdlib.utils.appurl("/img/empty.png");
		this.big_photo = wdlib.utils.appurl("/img/empty.png");

		this.init(args);
	}

	/**
	 * @param Object args
	 */	
	init(args)
	{
		this.user_id = args.user_id || this.user_id;
		this.idx = args.idx || this.idx;

		if(args.small_photo && PIC_TEST_PTRN.test(args.small_photo)) {
			this.small_photo = args.small_photo;
		}
		else {
//			this.small_photo = wdlib.utils.appurl("/img/" + (this.sex == wdlib.api.FEMALE ? "woman-shadow.png" : "man-shadow.png") + "");
		}

		if(args.big_photo && PIC_TEST_PTRN.test(args.big_photo)) {
			this.big_photo = args.big_photo;
		}
		else {
			this.big_photo = this.small_photo;
		}

		// PHOTO hack
		this.small_photo = wdlib.utils.https(this.small_photo);
		if(this.small_photo.includes("&amp;")) {
			this.small_photo = wdlib.utils.string.htmlDecode(this.small_photo);
		}
		this.big_photo = wdlib.utils.https(this.big_photo);
		if(this.big_photo.includes("&amp;")) {
			this.big_photo = wdlib.utils.string.htmlDecode(this.big_photo);
		}
	}
}

exports.Photo = Photo;
// ============================================================================

}, ((wdlib.model = wdlib.model || {}).user = wdlib.model.user || {}));
