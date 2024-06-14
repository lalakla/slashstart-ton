wdlib.module("/js/preloader.js", [
	"wdlib/model/error.js",
	"wdlib/net/http.js"
],
function(exports) {
"use strict";

console.log("PRELOADER START");

var _errors_sent = 0;

window.onerror = function(msg, url, line, tab, err)
{
	if(msg == "Script error.") {
		// это какая-то ебланская ошибка, которая вылезает хз как и особо ни на что не влияет
		return;
	}

	let error = new wdlib.model.Error({
		error: msg,
		error_code: wdlib.model.error.ERROR,
		file: url,
		line: line,
		stack: (err ? err.stack : "") 
	});

	if(app.Main) {
		app.Main.onError(error);
		return;
	}

	if(_errors_sent < 2) {
		if(err && !err.stack) {
			err = JSON.stringify(err);
		}
		var str = "ERROR.preloader" + (wdlib.Config.IS_MOBILE ? "-mobile" : "") + ".onerror : ver: '" + wdlib.Config.VERSION + "', '" + msg + "', url: '" + url + "', line: "+ String(line) + ", err: '" + (err || '').toString() + "', stack: '" + (err ? err.stack : "") + "'";

		console.error("ERROR: ", err);
		console.error("SEND ERROR : ", str);

		wdlib.net.Http.send("/error/error", {
			viewer_platform: wdlib.Config.CURRENT_API,
			viewer_id: (app.Main && app.Main.apiUser) ? app.Main.apiUser.api_user_id : 0,
			errno: err.error_code,
			method: err.file,
			comment: str
		});

		_errors_sent ++;
	}
	else {
		console.error("GLOBAL ERROR : ", msg, url, line, tab, err, arguments);
	}

	return true;
}

var _preloader = document.getElementById("global-preloader");
// console.log("PRELOADER : ", _preloader);

if(_preloader) {
	var progress = _preloader.querySelector("[data-progress] [data-value]");

	var percent = 0;
	var total = 400;

	var _err_reload = undefined;

	wdlib.setOnLoad(function(status, src, stat, err) {
//		console.log("ON LOAD : ", status, src, stat);

		if(status === false) {

			var p = _preloader.querySelector("[data-progress]");
			var perr = document.createElement("p");
			perr.classList.add("error");
			perr.innerText = "Ошибка загрузки! Файл: " + src;
			p.after(perr);

			if(_err_reload === undefined) {
				_err_reload = document.createElement("p");
				_err_reload.classList.add("error");
				_err_reload.innerText = "Пожалуйста, обновите страничку!";
				perr.after(_err_reload);
			}

			window.onerror("PRELOAD ERROR : " + src, src, 0, 0, err);
		
			return;
		}

		total = total < stat.total ? stat.total : total;
		var _percent = (stat.loaded / total) * 100;
		percent = percent < _percent ? _percent : percent;
	
		if(percent > 100) {
			console.log("stat.loaded: ", stat.loaded, " stat.total: ", stat.total, " src: ", src);
		}

		progress.textContent = percent.toFixed(1) + "%";
	});
}

}, (this.app = this.app || {}).Preloader = {});
