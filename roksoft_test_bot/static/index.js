
// TEST VERSION
wdlib.Config.VERSION = "local";
wdlib.Config.PROJECT_URL = "https://chat-bot-test.lalakla.ru";
wdlib.Config.PROJECT_DOMAIN = "https://chat-bot-test.lalakla.ru";

// RELEASE VERSION
//wdlib.Config.VERSION = "2.0.0";
//wdlib.Config.PROJECT_URL = "https://pravolevo.lalakla.ru/release";
//wdlib.Config.PROJECT_DOMAIN = "https://pravolevo.lalakla.ru";

wdlib.Config.STATIC_URL = ".";
wdlib.Config.APP_URL = ".";
wdlib.Config.HEAVY_IMAGE_URL = wdlib.Config.APP_URL;
wdlib.Config.CDN = "https://73505069-48c9-447b-9515-774a9411ca5c.selcdn.net";

wdlib.Config.JQUERY_VERSION = "3.7.1";
wdlib.Config.JQUERY_UI_VERSION = "1.13.2";
wdlib.Config.BOOTSTRAP_VERSION = "5.2.3";
wdlib.Config.TONCONNECT_UI_VERSION = "2.0.5";
wdlib.Config.TEMPLATE7_VERSION = "1.4.1";
wdlib.Config.HOWLER_VERSION = "2.2.3";
wdlib.Config.DAYJS_VERSION = "1.10.7";
wdlib.Config.VKBRIDGE_VERSION = "2.12.2";

wdlib.Config.screen = {	
	width: document.documentElement.clientWidth,
	height: document.documentElement.clientHeight,
	realPixelRatio: window.devicePixelRatio,
	pixelRatio: window.devicePixelRatio,
	retinaScale: 1,
}

// это какой далбоебизм - есть варианты с дробным этим числом
// и вот было такое - что на компе с числом 1.5 - округление сильно помоголо
// а вот на телефоне, с числом 2.65 - наоборот, лучше стало когда окргуление убрал
if(wdlib.Config.screen.pixelRatio < 2) {
	wdlib.Config.screen.pixelRatio = 1;
}

wdlib.Config.CURRENT_API = 0;
wdlib.Config.CURRENT_USER_ID = "1";
wdlib.Config.CLIENT_KEY = "8K2rxSUn7xtU21j2pnxp";

wdlib.Config.IS_TEST = true;
//wdlib.Config.NO_BILLING = true;

wdlib.module.config({
//	debug: true,
	base: ".",
	map: {
		"heavy-image" : wdlib.Config.HEAVY_IMAGE_URL,
		"wdlib" : wdlib.Config.APP_URL + "/lib/wdlib",
		"jquery": wdlib.Config.CDN + "/libs/jquery/" + wdlib.Config.JQUERY_VERSION + "/jquery.min.js",
		"jquery-ui": wdlib.Config.CDN + "/libs/jquery/ui/" + wdlib.Config.JQUERY_UI_VERSION + "/jquery-ui.min.js",
//		"tonconnect-ui": wdlib.Config.CDN + "/libs/tonconnect/ui/" + wdlib.Config.TONCONNECT_UI_VERSION + "/tonconnect-ui.min.js",
//		"tonconnect-ui": "https://unpkg.com/@tonconnect/ui@0.0.9/dist/tonconnect-ui.min.js",
//		"tonconnect-ui": "https://unpkg.com/@tonconnect/ui@2.0.0/dist/tonconnect-ui.min.js",
		"tonconnect-ui": "https://unpkg.com/@tonconnect/ui@2.0.0/dist/tonconnect-ui.min.js",
//		"tonconnect-ui": "https://unpkg.com/@tonconnect/ui@latest/dist/tonconnect-ui.min.js",
		"bootstrap": wdlib.Config.CDN + "/libs/bootstrap/" + wdlib.Config.BOOTSTRAP_VERSION + "/js/bootstrap.min.js",
		"template7": wdlib.Config.CDN + "/libs/template7/" + wdlib.Config.TEMPLATE7_VERSION + "/dist/template7.js",
		"howler": wdlib.Config.CDN + "/libs/howler/" + wdlib.Config.HOWLER_VERSION + "/dist/howler.min.js",
		"dayjs": wdlib.Config.CDN + "/libs/dayjs/" + wdlib.Config.DAYJS_VERSION + "/dayjs.min.js",
		"vk.bridge": wdlib.Config.CDN + "/libs/vk/bridge/" + wdlib.Config.VKBRIDGE_VERSION + "/browser.min.js",
	}
});

wdlib.queue("/js/preloader.js").queue("jquery").queue("bootstrap").queue("/js/main.js")
.then(function() {
	app.Main.appstart();
});
