wdlib.module("wdlib/utils/worker.js", [
],
function(exports) {
"use strict";

// ============================================================================
// wdlib.utils.worker interface

exports.create = function(workerUrl, callback, sameOrigin = true)
{
	var worker = null;

	try {
		if((typeof SharedWorker !== "undefined") && (worker = new SharedWorker(workerUrl))) {
			worker.onerror = function(e) {
				e.preventDefault();

				console.error("wdlib.utils.worker : ERROR CREATE WOERKER at ", workerUrl);
				worker = null;
			};
		}
	}
	catch(e) {
		console.error("wdlib.utils.worker : ERROR CREATE WOERKER at ", workerUrl, e);
		worker = null;
	}

	callback.call(undefined, worker);
}

// ============================================================================
// internal functions

function createWorkerFallback(workerUrl, callback)
{
	return fetchWorkerData(workerUrl, callback);

	/*
	var worker = null;

	try {
		var blob;
		try {
			blob = new Blob(["importScripts('" + workerUrl + "');"], { "type": 'application/javascript' });
		}
		catch(e) {
			var blobBuilder = new (window.BlobBuilder || window.WebKitBlobBuilder || window.MozBlobBuilder)();
			blobBuilder.append("importScripts('" + workerUrl + "');");
			blob = blobBuilder.getBlob('application/javascript');
		}
		var url = window.URL || window.webkitURL;
		var blobUrl = url.createObjectURL(blob);
		worker = new SharedWorker(blobUrl);
	}
	catch (e1) {
		//if it still fails, there is nothing much we can do
		console.error("wdlib.utils.worker : CREATE WORKER ERROR : ", e1.toString(), e1);
	}
	return worker;
	*/
}

function fetchWorkerData(workerUrl, callback)
{
	var worker = null;

	var _err = function(e)
	{
		console.error("wdlib.utils.worker : FAIL : ", workerUrl, e);
		callback.call(undefined, null);
	}

	try {
		fetch(workerUrl, {
		}).then(function(response) {
			if(!response.ok) {
				throw new Error("error while request : " + workerUrl);
			}
			return response.text();
		}).then(function(data) {
			// console.log("wdlib.utils.worker: LOADED : ", data);
			var blob;
			try {
				blob = new Blob([data], {"type": 'application/javascript'});
			}
			catch(e) {
				var blobBuilder = new (window.BlobBuilder || window.WebKitBlobBuilder || window.MozBlobBuilder)();
				blobBuilder.append(data);
				blob = blobBuilder.getBlob('application/javascript');
			}
			var url = window.URL || window.webkitURL;
			var blobUrl = url.createObjectURL(blob);
			worker = new SharedWorker(blobUrl);
			callback.call(undefined, worker);
		}).catch(function(e) {
			_err.call(undefined, e);
		});
	}
	catch(e) {
		_err.call(undefined, e);
	}

	return null;
}

function testSameOrigin(url)
{
	var loc = window.location;
	var a = document.createElement('a');
	a.href = url;
	return a.hostname === loc.hostname && a.port === loc.port && a.protocol === loc.protocol;
}

// ============================================================================

}, ((wdlib.utils = wdlib.utils || {}).worker = {}));
