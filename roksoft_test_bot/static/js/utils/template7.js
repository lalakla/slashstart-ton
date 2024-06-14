wdlib.module("/js/utils/template7.js", [
	"template7",
	"dayjs",
	"wdlib/utils/utils.js",
	"wdlib/utils/string.js",
	"wdlib/utils/date.js",
],
function(exports) {
"use strict";

Template7.registerHelper("appurl", function(url, options) {
	return wdlib.utils.appurl(url);
});
Template7.registerHelper("gender", function(male, female, user, options) {
	var _user = app.Main.currentUser;
	if(arguments.length == 4) {
		_user = arguments[2];
	}

	return (_user.sex == wdlib.api.MALE) ? male : female
});
Template7.registerHelper("genderPattern", function(str, user, options) {
	var _user = app.Main.currentUser;
	if(arguments.length == 3) {
		_user = arguments[1];
	}

	return wdlib.utils.string.genderPattern(_user.sex, str);
});
Template7.registerHelper("plural", function(val, s1, s2, s3, options) {
	return wdlib.utils.string.plural(val, s1, s2, s3);
});
Template7.registerHelper("nl2br", function(val, options) {
	return wdlib.utils.string.nl2br(val);
});
Template7.registerHelper("timeout2str", function(val, options) {
	console.log("TIMEOUT2STR : ", val);
	return wdlib.utils.date.timeout2str(val);
});
Template7.registerHelper("substr", function(val, s1, s2, options) {
	let p = 0;
	let l = s1;
	if(arguments.length == 4) {
		p = s1;
		l = s2;
	}
	return String(val).substr(p, l);
});
Template7.registerHelper("date_full", function(val, options) {
	let d = undefined;
	if(val instanceof Date) {
		d = new dayjs(val);
	}
	else {
		d = dayjs.unix(val);
	}
	return d.format("HH:mm DD.MM.YYYY");
});
Template7.registerHelper("date", function(val, options) {
	let d = undefined;
	if(val instanceof Date) {
		d = new dayjs(val);
	}
	else if(val instanceof dayjs) {
		d = val;
	}
	else {
		d = dayjs.unix(val);
	}
	return d.format("DD.MM.YYYY");
});


function _user(user_id, prop = undefined)
{
	var user = app.model.user.getUser(user_id);
	var prop = prop || "id";

	return (user && user.hasOwnProperty(prop)) ? user[prop] : "";
}
Template7.registerHelper("_user", function(user_id, options) {
	var prop = "id";
	if(arguments.length >= 3) {
		prop = arguments[1];
	}

	return _user(user_id, prop);
});

/**
 * делаем хелпер  switch-case
 * позволяет делать такие шаблоны
 *
 * {{#switch url}}
 *       {{#case '/' '/home'}} <!--url for both of '/' and '/home' -->
 *           first case
 *       {{/case}}
 *       {{#case '/about'}}
 *           second case
 *       {{/case}}
 *       {{#default}}
 *           DEFAULT CASE
 *      {{/default}}
 * {{/switch}}
 *
 * взято отсюда
 * https://forum.framework7.io/t/using-switch-case-in-template7/4680/4
 */
Template7.registerHelper('switch', function (value, options) {
	this._switch_value_ = value;
	this._switch_break_ = false;

	let html = options.fn(this);

	delete this._switch_break_;
	delete this._switch_value_;
	return html;
});
Template7.registerHelper('case', function (value, options) {
	let args = Array.prototype.slice.call(arguments);
	options = args.pop();
	let caseValues = args;

	if (this._switch_break_ || caseValues.indexOf(this._switch_value_) === -1) {
		return '';
	} else {
		this._switch_break_ = true;
		return options.fn(this);
	}
});
Template7.registerHelper('js_case', function (expression, options) {
	if(this._switch_break_) {
		return "";
	}

	var data = options.data;
	var func;
	var execute = expression;
	('index first last key').split(' ').forEach(function (prop) {
		if (typeof data[prop] !== 'undefined') {
			var re1 = new RegExp(("this.@" + prop), 'g');
			var re2 = new RegExp(("@" + prop), 'g');
			execute = execute
				.replace(re1, JSON.stringify(data[prop]))
				.replace(re2, JSON.stringify(data[prop]));
		}
	});
	if (options.root && execute.indexOf('@root') >= 0) {
		execute = Template7Utils.parseJsVariable(execute, '@root', options.root);
	}
	if (execute.indexOf('@global') >= 0) {
		execute = Template7Utils.parseJsVariable(execute, '@global', Template7Context.Template7.global);
	}
	if (execute.indexOf('../') >= 0) {
		execute = Template7Utils.parseJsParents(execute, options.parents);
	}
	if (execute.indexOf('return') >= 0) {
		func = "(function(){" + execute + "})";
	} else {
		func = "(function(){return (" + execute + ")})";
	}

	var caseValues = String(eval(func).call(this));
//	if (caseValues.indexOf(this._switch_value_) !== -1) {
	if (caseValues == this._switch_value_) {
		this._switch_break_ = true;
		return options.fn(this, options.data);
	}
	else {
		return '';
	}
});
Template7.registerHelper('default', function (options) {
	if (!this._switch_break_) {
		return options.fn(this);
	} else {
		return '';
	}
});

Template7.registerHelper('isset', function (value, options) {
	let args = Array.prototype.slice.call(arguments);
	options = args.pop();

	let k = args[1];
	let d = args.length > 2 ? args[2] : undefined;

	let r = wdlib.utils.object.isset(value, k, d);
	if(r) {
		this.value = r;
		return options.fn(this, options.data);
	}

	return '';
});

}, ((this.app.utils = this.app.utils || {})));
