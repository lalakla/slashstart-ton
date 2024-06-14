wdlib.module("wdlib/utils/math.js", [
],
function(exports) {
"use strict";

var __operators = new Map;
var __functions = new Map;
var __left_bracket = undefined;
var __right_bracket = undefined;

// ============================================================================
// wdlib.utils.math public interface

exports.execute = function(str, vars)
{
	var p = new Parser;
	return p.execute(str, vars);
}

// ============================================================================

// ============================================================================
// internal classes

class Parser {

	execute(str, vars)
	{
		var result = 0;

		this.buffer = "";
		this.stack = [];
		this.ostack = [];

		this.vars = undefined;

		if(vars) {
			this.vars = new Map;
			for(var k in vars) {
				this.vars.set(k, new Variable({name: k, value: vars[k]}));
			}
		}

		for(var i=0; i<str.length; ++i) {
			var ch = str.charAt(i);

			switch(ch) {
				case ' ':
				case ',':
					this.point();
					break;
				case '(':
					this.point();
					this.ostack.push(__left_bracket);
					break;
				case ')':
					this.point();
					while(this.ostack.length && this.ostack[this.ostack.length - 1].name != '(') {
						this.step();
					}
					this.ostack.pop();
					break;
				default:
					var oo = undefined;
					if((oo = __operators.get(ch))) {
						this.point();
						this.opoint(oo);
					}
					else {
						this.buffer += String(ch);
					}
					break;
			}
		}

		this.point();
		while(this.ostack.length) {
			this.step();
		}

		if(this.stack.length) {
			result = this.stack[this.stack.length - 1];
		}

		return result;
	}

	point()
	{
		if(this.buffer == "") {
			return;
		}

		var oo = undefined;
		if((oo = __functions.get(this.buffer))) {
			this.buffer = "";
			this.opoint(oo);
		}
		else {
			if(this.vars && (oo = this.vars.get(this.buffer))) {
				this.stack.push(oo.value);
			}
			else {
				this.stack.push(parseFloat(this.buffer));
			}
			this.buffer = "";
		}
	}

	opoint(oo)
	{
		if(this.ostack.length) {
			var loo = this.ostack[this.ostack.length - 1];
			if(loo.prio < oo.prio) {
				this.ostack.push(oo);
			}
			else {
				while(this.ostack.length && (loo = this.ostack[this.ostack.length - 1]) && loo.prio >= oo.prio) {
					this.step();
				}
				this.ostack.push(oo);
			}
		}
		else {
			this.ostack.push(oo);
		}
	}

	step()
	{
		var oo = undefined;

		if(this.ostack.length && (oo = this.ostack[this.ostack.length - 1]) && oo.func) {
			oo = this.ostack.pop();

			var args = [];
			var acount = oo.args_count;
			while(acount) {
				args.push(this.stack.pop());
				acount--;
			}

			this.stack.push(oo.func.apply(undefined, args));
		}
	}
}

class Operator {

	constructor(data)
	{
		data = data || {};

		this.prio = 0;
		this.name = "";
		this.args_count = 0;
		this.func = undefined;

		this.init(data);
	}

	init(data)
	{
		this.prio = data.prio || 0;
		this.name = data.name || "";
		this.args_count = data.args_count || 0;
		this.func = data.func || undefined;
	}
}

class Variable {

	constructor(data)
	{
		data = data || {};

		this.name = "";
		this.value = 0;

		this.init(data);
	}

	init(data)
	{
		this.name = data.name || "";
		this.value = parseFloat(data.value || 0);
	}
}

// ============================================================================

// ============================================================================
// init global

// single symbol operatos
__operators.set("+", new Operator({prio: 1, name: "+", args_count: 2, func: function(a2, a1) {
//	console.log(String(a1) + " + " + String(a2));
	return a1 + a2;
}}));
__operators.set("-", new Operator({prio: 1, name: "-", args_count: 2, func: function(a2, a1) {
//	console.log(String(a1) + " - " + String(a2));
	return a1 - a2;
}}));
__operators.set("*", new Operator({prio: 2, name: "*", args_count: 2, func: function(a2, a1) {
//	console.log(String(a1) + " * " + String(a2));
	return a1 * a2;
}}));
__operators.set("/", new Operator({prio: 2, name: "/", args_count: 2, func: function(a2, a1) {
//	console.log(String(a1) + " / " + String(a2));
	return a1 / a2;
}}));

// brackets
__left_bracket = new Operator({prio: 0, name: "("});
__right_bracket = new Operator({prio: 0, name: ")"});

// functions
__functions.set("exp", new Operator({prio: 3, name: "exp", args_count: 1, func: function(a1) {return Math.exp(a1);}}));
__functions.set("ln", new Operator({prio: 3, name: "ln", args_count: 1, func: function(a1) {return Math.log(a1);}}));
__functions.set("log", new Operator({prio: 3, name: "log", args_count: 2, func: function(a2, a1) {return (Math.log(a1) / Math.log(a2));}}));
__functions.set("pow", new Operator({prio: 3, name: "pow", args_count: 2, func: function(a2, a1) {
//	console.log("pow(" + String(a1) + " , " + String(a2) + ")");
	return Math.pow(a1, a2);
}}));
__functions.set("atan", new Operator({prio: 3, name: "atan", args_count: 1, func: function(a1) {return Math.atan(a1);}}));

// ============================================================================

}, ((wdlib.utils = wdlib.utils || {})).math = {});
