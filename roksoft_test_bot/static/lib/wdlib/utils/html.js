wdlib.module("wdlib/utils/html.js", [
],
function(exports) {
"use strict";

var _css_cache = new Set;
var _css_sheets = new Map;

/**
 * @param String selector
 * @return StyleSheet
 */
exports.getCssSheet = function(file)
{
	var sheet = _css_sheets.get(file);
	
	if(!sheet) {
		for(var i=0; i<document.styleSheets.length; ++i) {
			var _sheet = document.styleSheets[i];
			var _file = _sheet.href ? _sheet.href : "";
			if(_file.indexOf(file) !== -1) {
				// save _css_sheets cache
				_css_sheets.set(file, _sheet);

				sheet = _sheet;
				break;
			}
		}
	}

	return sheet;
}

/**
 * @param String selector
 * @param String file
 * @return Boolean
 */
exports.checkCssExisnace = function(selector, file)
{
	if(_css_cache.has(selector)) {
		return true;
	}

	var sheet = wdlib.utils.html.getCssSheet(file);

	if(!sheet) {
		// We can't find the specified stylesheet
		return false;
	}

	//Check the stylesheet for the specified selector
	var cssRules = sheet.rules ? sheet.rules : sheet.cssRules;
	for (var i=0; i<cssRules.length; ++i) {
		if(cssRules[i].selectorText == selector) {
			_css_cache.add(selector);
			return true;
		}
	}
	
	return false;
}

/**
 * @param String file
 * @param Function routine
 */
exports.cssIterate = function(file, routine)
{
	var sheet = wdlib.utils.html.getCssSheet(file);

	if(!sheet) {
		// We can't find the specified stylesheet
		return;
	}
	
	// iterate
	var cssRules = sheet.rules ? sheet.rules : sheet.cssRules;
	for(var i=0; i<cssRules.length; ++i) {
		if(!routine.call(undefined, cssRules[i])) {
			break;
		}
	}
}

}, ((wdlib.utils = wdlib.utils || {}).html = {}));
