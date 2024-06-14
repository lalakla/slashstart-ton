wdlib.module("/js/view/ton-connect/ui.js", [
	"tonconnect-ui",
	"wdlib/api/base.js",
	"wdlib/button/base.js",
	"wdlib/utils/flex.js",
],
function(exports) {
"use strict";

var tonConnect = undefined;

var tonDisconnected = undefined;
var tonTransaction = undefined;
var sendCoins = undefined;

// ===============================================================================
// app.view.App interface

exports.init = function(displayObject, _top_global_container)
{
	console.log("app.view.tonConnect.UI::init");

	tonDisconnected = new wdlib.utils.FlexContainer($(".ton-disconnect-container", _top_global_container));
	tonTransaction = new wdlib.utils.FlexContainer($(".ton-transaction-container", _top_global_container));
	sendCoins = new wdlib.button.Base($(".btn", tonTransaction.displayObject), {}, onSendCoinsClick);

	window.addEventListener("ton-connect-ui-connection-started", onConnectStarted);
	window.addEventListener("ton-connect-ui-connection-completed", onConnectComplete);
	window.addEventListener("ton-connect-ui-connection-restoring-completed", onConnectRestoreComplete);

	let params = {
		manifestUrl: wdlib.Config.PROJECT_URL + "/tonconnect-manifest.json",
		buttonRootId: displayObject.attr("id")
	};

	console.log("app.view.tonConnect.UI : connectParams : ", params);

	tonConnect = new TON_CONNECT_UI.TonConnectUI(params);

	let uiOptions = {
		language: "ru",
	}

	if(wdlib.api.isApi(wdlib.api.API_TELEGRAM)) {
		uiOptions["twaReturnUrl"] = wdlib.api.IApi.appurl();
	}

	console.log("app.view.tonConnect.UI : uiOptions : ", uiOptions);

	tonConnect.uiOptions = uiOptions;

	tonConnect.onStatusChange(function(wallet) {
		console.log("TON-CONNECT : ON-STATUS-CHANGED : ", wallet);
		if(wallet) {
			tonDisconnected.hide();
			tonTransaction.show();
		}
		else {
			tonDisconnected.show();
			tonTransaction.hide();
		}
	}, function(err) {
		console.error("TON-CONNECT : ON-STATUS-CHANGED : ERROR : ", err);
	});

	console.log("TON-CONNECT : wallet : ", tonConnect.wallet);
	if(!tonConnect.wallet) {
		tonDisconnected.show();
		tonTransaction.hide();
	}

//	tonConnect.connectionRestored.then(function(restored) {
//		console.log("TON-CONNECT : connection restored : ", restored);
//	});
}
// ===============================================================================

// ===============================================================================
// internal functions

async function onSendCoinsClick(btn)
{
	console.log("onSendCoinsClick : ", tonConnect.wallet);
	if(!tonConnect.wallet) {
		return;
	}

	const transaction = {
		validUntil: Math.floor(Date.now() / 1000) + 60, // 60 sec
		messages: [
			{
				address: "UQCOB-3BRuRHoOFakk2A87QAe4aDcM1qRpn4Wxt8uCwf3x7",
				amount: "1"
			}
		]
	};

	try {
		const result = await tonConnect.sendTransaction(transaction);
		console.log("TON-CONNECT : transaction result : ", result);
	}
	catch(e) {
		console.error("TON-CONNECT : transaction failed : ", e);
	}
}

function onConnectStarted(event)
{
	console.log("TON-CONNECT : onConnectStarted : ", event);
}
function onConnectComplete(event)
{
	console.log("TON-CONNECT : onConnectComplete : ", event);
}
function onConnectRestoreComplete(event)
{
	console.log("TON-CONNECT : onConnectRestoreComplete : ", event);
}
// ===============================================================================

}, ((this.app.view.tonConnect = this.app.view.tonConnect || {})).UI = {});
