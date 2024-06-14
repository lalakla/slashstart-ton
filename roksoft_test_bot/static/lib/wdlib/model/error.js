wdlib.module("wdlib/model/error.js", [
	"wdlib/model/base.js"
],
function(exports) {
"use strict";

// ============================================================================
// ERROR constants

exports.error = {
	OK: 0,
	ERROR: -1,
	ERROR_NOT_FOUND: -3,
	ERROR_ACCESS: -4,
	ERROR_AUTH: -5,
	ERROR_INVALID_DATA: -6,
	ERROR_NOT_ENOUGH: -7,
	ERROR_ALREADY: -9,
	ERROR_ABUSE: -10,
	ERROR_FLOOD: -11,
	ERROR_BANNED: -12,
	ERROR_BUSY: -13,
	ERROR_LIMIT: -14,
	ERROR_TOTAL_LIMIT: -15,
	ERROR_API: -16,
	ERROR_REDIRECT: -17,
	ERROR_SIG: -20
}

// ============================================================================

class Error extends wdlib.model.Base {

	/**
	 * @param Object args
	 */
	constructor(args)
	{
		args = args || {};
		super(args);

		this.error = "";
		this.error_code = 0;
		this.stack = undefined;
		this.file = "";
		this.line = 0;
		this.data = {};

		args.stack = args.stack || (new window.Error).stack;

		this.init(args);
	}
	/**
	 * @param Object args
	 */
	init(args)
	{
		this.error = args.error || args.message || "";
		this.error_code = args.error_code || wdlib.model.error.ERROR;
		this.stack = args.stack || undefined;
		this.file = args.file || args.fileName || "";
		this.line = args.line || args.lineNumber || 0;
		this.data = args.data || {};
		this.data = args.hasOwnProperty("error_data") ? args.error_data : this.data;
	}
}

exports.Error = Error;

}, (wdlib.model = wdlib.model || {}));
