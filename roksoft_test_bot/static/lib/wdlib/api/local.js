wdlib.module("wdlib/api/local.js", [
	"wdlib/api/base.js",
	"wdlib/model/user/user.js",
	"wdlib/utils/object.js"
],
function(exports) {
"use strict";

var _users = {};

var FAKE_GIRLS_LIST = [
	[2,"Маша",		18,		"Москва",		 		"http://static5.lalakla.ru/img/females/2331110924.jpg", "Козерог"],
	[4,"Наташа",	19,		"Санкт-Петербург",		"http://static5.lalakla.ru/img/females/4.jpg", "Овен"],
	[6,"Света",		20,		"Екатеринбург",			"http://static5.lalakla.ru/img/females/6.jpg", "Телец"],
	[8,"Даша",		21,		"Новосибирск",			"http://static5.lalakla.ru/img/females/8.jpg", "Водолей"],
	[10,"Ирина",	22,		"Иркутск",				"http://static5.lalakla.ru/img/females/10.jpg", "Козерог"],
	[12,"Катя",		23, 	"Хабаровск",			"http://static5.lalakla.ru/img/females/12.jpg", "Лев"],
	[14,"Ольга",	16,		"Владивосток",			"http://static5.lalakla.ru/img/females/14.jpg", "Весы"],
	[16,"Юля",		0,		"Ростов-на-Дону",		"http://static5.lalakla.ru/img/females/16.jpg", "Скорпион"],
	[18,"Яна",		18,		"Сочи",					"http://static5.lalakla.ru/img/females/18.jpg", "Дева"],
	[20,"Инна",		19,		"Петрозаводск",			"http://static5.lalakla.ru/img/females/20.jpg", "Рак"],
	[22,"Инга",		20,		"Мурманск",				"http://static5.lalakla.ru/img/females/22.jpg", "Весы"],
	[24,"Аня",		21, 	"Рязань",				"http://static5.lalakla.ru/img/females/24.jpg", "Близнецы"],
	[26,"Таня",		22, 	"Тверь",				"http://static5.lalakla.ru/img/females/26.jpg", "Стрелец"],
	[28,"Вера",		23,		"Пенза",				"http://static5.lalakla.ru/img/females/28.jpg", "Рыбы"],
	[30,"Надежда",	24, 	"Тольятти",				"http://static5.lalakla.ru/img/females/30.jpg", "Водолей"],
	[32,"Любовь",	25, 	"Ульяновск",			"http://static5.lalakla.ru/img/females/32.jpg", "Лев"],
	[34,"Жанна",	19, 	"Львов",				"http://static5.lalakla.ru/img/females/34.jpg", "Телец"],
	[36,"Альбина",	20, 	"Донецк",				"http://static5.lalakla.ru/img/females/36.jpg", "Скорпион"],
	[38,"Полина",	21, 	"Днепропетровск",		"http://static5.lalakla.ru/img/females/38.jpg", "Дева"],
	[40,"Кира",		22, 	"Ялта",					"http://static5.lalakla.ru/img/females/40.jpg", "Рыбы"],
	[42,"Моника",	23, 	"Одесса",				"http://i03.fotocdn.net/s3/213/gallery_260/372/2281665492.jpg", "Близнецы"],
	[44,"Мелисса",	24, 	"Минск",				"http://i04.fotocdn.net/s3/155/gallery_260/85/2287883418.jpg", "Водолей"],
	[46,"Вероника",	25, 	"Гродно",				"http://i03.fotocdn.net/s3/51/gallery_260/322/2312847666.jpg", "Рак"],
	[48,"Анжелика",	18, 	"Таллин",				"http://i01.fotocdn.net/s3/88/gallery_260/302/2312842583.jpg", "Козерог"],
	[50,"Марина",	19, 	"Нарва",				"http://i02.fotocdn.net/s2/175/gallery_260/292/2230002606.jpg", "Скорпион"],
	[52,"Кристина",	20, 	"Рига",					"http://i02.fotocdn.net/s3/48/gallery_260/222/2285428015.jpg", "Стрелец"]
];

var FAKE_MEN_LIST = [
	[1,"Александр",		18,		"Москва",		 		"http://static5.lalakla.ru/img/1p2.jpg", "Овен"],
	[3,"Алексей",		19,		"Санкт-Петербург",		"http://static5.lalakla.ru/img/326706351.jpg", "Водолей"],
	[5,"Андрей",		20, 	"Екатеринбург",			"http://static5.lalakla.ru/img/5.jpg", "Скорпион"],
	[7,"Борис",			21,		"Новосибирск",			"http://static5.lalakla.ru/img/7.jpg", "Рак"],
	[9,"Богдан",		22,		"Иркутск",				"http://static5.lalakla.ru/img/9.jpg", "Близнецы"],
	[11,"Владислав",	24,		"Владивосток",			"http://static5.lalakla.ru/img/11.jpg", "Овен"],
	[13,"Виктор",		25,		"Ростов-на-Дону",		"http://static5.lalakla.ru/img/13.jpg", "Рыбы"],
	[15,"Глеб",			18,		"Сочи",					"http://static5.lalakla.ru/img/15.jpg", "Стрелец"],
	[17,"Герман",		19,		"Петрозаводск",			"http://static5.lalakla.ru/img/17.jpg", "Скорпион"],
	[19,"Геннадий",		20,		"Мурманск",				"http://static5.lalakla.ru/img/19.jpg", "Водолей"],
	[21,"Денис",		21, 	"Рязань",				"http://static5.lalakla.ru/img/21.jpg", "Лев"],
	[23,"Егор",			32, 	"Тверь",				"http://static5.lalakla.ru/img/23.jpg", "Телец"],
	[25,"Игорь",		23,		"Пенза",				"http://static5.lalakla.ru/img/25.jpg", "Весы"],
	[27,"Илья",			24, 	"Тольятти",				"http://static5.lalakla.ru/img/27.jpg", "Козерог"],
	[29,"Иннокентий",	25, 	"Ульяновск",			"http://static5.lalakla.ru/img/29.jpg", "Лев"],
	[31,"Карл",			18, 	"Киев",					"http://static5.lalakla.ru/img/31.jpg", "Рыбы"],
	[33,"Кирилл",		19, 	"Львов",				"http://static5.lalakla.ru/img/33.jpg", "Телец"],
	[35,"Лев",			20, 	"Донецк",				"http://static5.lalakla.ru/img/35.jpg", "Скорпион"],
	[37,"Мефодий",		21, 	"Днепропетровск",		"http://static5.lalakla.ru/img/37.jpg", "Близнецы"],
	[39,"Марат",		22, 	"Ялта",					"http://static5.lalakla.ru/img/39.jpg", "Телец"],
	[41,"Никита",		23, 	"Одесса",				"http://static5.lalakla.ru/img/41.jpg", "Стрелец"],
	[43,"Николай",		24, 	"Минск",				"http://static5.lalakla.ru/img/43.jpg", "Водолей"],
	[45,"Олег",			25, 	"Гродно",				"http://static5.lalakla.ru/img/45.jpg", "Рак"],
	[47,"Остап",		18, 	"Таллин",				"http://static5.lalakla.ru/img/47.jpg", "Рыбы"],
	[49,"Петр",			19, 	"Нарва",				"http://static5.lalakla.ru/img/49.jpg", "Овен"],
	[51,"Прохор",		20, 	"Рига",					"http://static5.lalakla.ru/img/51.jpg", "Телец"]
];

var _user = {
	remote_id: "1",
	platform: wdlib.Config.CLIENT_PLATFORM,
	name: "",
	sex: 0,
	pic: "",
	big_pic: "",
	age: 0,
	city: wdlib.api.DEFAULT_CITY,
	anketa_link: "",
	has_mobile: 0
};
var _users = {};

// ============================================================================
// wdlib.api.LocalApi class

class LocalApi extends wdlib.api.Base {

	constructor(args)
	{
		super(args);

		this.partner_url = "http://localhost";
		this._extra = args.extra || "";

		this.viewer_id = args.viewer_id || this.viewer_id;
	}

	appurl(extra)
	{
		return "" + (extra ? extra : ""); 
	}
	extra()
	{
		return this._extra;
	}

	init(callback)
	{
		super.init(callback);

		// selecting random user from fake men or girls list
		var id = parseInt(this.viewer_id);
		//console.log("========== id ", id);
		var pos = 0;
		if(id % 2 == 0) {
			// choose girl
			pos = (id / 2 - 1) % FAKE_GIRLS_LIST.length;
			_user.name = FAKE_GIRLS_LIST[pos][1];
			_user.age = FAKE_GIRLS_LIST[pos][2];
			_user.city = FAKE_GIRLS_LIST[pos][3];
			_user.pic = FAKE_GIRLS_LIST[pos][4];
			_user.big_pic = _user.pic;
			_user.sex = wdlib.api.FEMALE;
		//console.log("========== FEMALE ");
		}
		else {
			// choose man
			pos = ((id - 1) / 2) % FAKE_MEN_LIST.length;
			_user.name = FAKE_MEN_LIST[pos][1];
			_user.age = FAKE_MEN_LIST[pos][2];
			_user.city = FAKE_MEN_LIST[pos][3];
			_user.pic = FAKE_MEN_LIST[pos][4];
			_user.big_pic = _user.pic;
			_user.sex = wdlib.api.MALE;
		//console.log("========== MALE ");
		}

		_user.remote_id = this.viewer_id;
		_user.platform = wdlib.Config.CLIENT_PLATFORM;

		setTimeout(function() {
			// API INIT OK
			console.log("LOCAL API: init ok");
			callback.call(null, _user);
		}, 500);
	}

	/**
	 * @param String user_id
	 * @param Function callback
	 */
	getProfile(user_id, callback)
	{
		// check internal cache
		if(_users[user_id]) {
			callback.call(null, _users[user_id]);
			return;
		}

		var user = {
			remote_id: user_id,
			platform: wdlib.Config.CLIENT_PLATFORM,
			name: "",
			sex: 0,
			pic: "",
			big_pic: "",
			age: 0,
			city: wdlib.api.DEFAULT_CITY,
			anketa_link: "",
			has_mobile: 0
		};

		user_id = parseInt(user_id);
		
		var arr;
		if(user_id % 2 == 0) {
			// try girls
			arr = FAKE_GIRLS_LIST.slice();
			user.sex = wdlib.api.FEMALE;
		}
		else {
			// try boys
			arr = FAKE_MEN_LIST.slice();
			user.sex = wdlib.api.FEMALE;
		}

		for(var i=0; i<arr.length; ++i) {
			if(user_id == arr[i][0]) {
				// user found
				user.name = arr[i][1];
				user.age = arr[i][2];
				user.city = arr[i][3];
				user.pic = arr[i][4];
				user.big_pic= arr[i][4];
				user.is_app_user = (Math.random() < 0.5) ? 1 : 0;
				break;
			}
		}

		setTimeout(function() {
			_users[user.remote_id] = user;
			callback.call(null, user);
		}, 300);
	}
	
	/**
	 * @param String user_id
	 * @param Function callback
	 */
	getUserPhotos(user_id, callback)
	{
		var arr;
		if(parseInt(user_id) % 2 == 0) {
			// try girls
			arr = FAKE_GIRLS_LIST.slice();
		}
		else {
			// try boys
			arr = FAKE_MEN_LIST.slice();
		}

		setTimeout(function() {
			var photos = [];
			for(var i=0; i<arr.length; ++i) {
				var photo = {
					small: arr[i][4],
					medium: arr[i][4],
					big: arr[i][4]
				}
				photos.push(photo);
			}

			callback.call(null, user_id, photos);
		}, 500);
	}

	/**
	 * @param Function callback
	 */
	getOffers(callback)
	{
		var offers = [
			{
				offer_id: 1,
				ttl: 3600
			}
		];
		setTimeout(function() {
			callback.call(undefined, offers);
		}, 1000);
	}

	/**
	 * @param int limit
	 * @param Function callback
	 */
	getFriends(limit, callback)
	{
		var arr = FAKE_GIRLS_LIST.concat(FAKE_MEN_LIST);
		wdlib.utils.object.shuffle(arr);

		setTimeout(function() {
			var users = [];
			for(var i=0; i<arr.length && limit; ++i) {
				var user = {
					remote_id: arr[i][0],
					platform: wdlib.Config.CLIENT_PLATFORM,
					name: arr[i][1],
					sex: (arr[i][0] % 2) ? wdlib.api.MALE : wdlib.api.FEMALE,
					age: arr[i][2],
					city: arr[i][3],
					pic: arr[i][4],
					big_pic: arr[i][4],
					anketa_link: "http://localhost/app/love/index.html?user_remote_id=" + arr[i][0],
					has_mobile: 0,
					is_app_user: (Math.random() < 0.5) ? 1 : 0
				};

				// remove current_user
				if(user.remote_id == _user.remote_id) {
					continue;
				}

				users.push(user);

				limit--;
			}

			callback.call(null, users);
		}, 500);
	}

	billingDialog(gold, amount, callback, name, desc, pic, extra)
	{
		callback.call(null, amount, wdlib.api.BILLING_TRUE);
	}

	/**
	 * @param String mess
	 * @param Array uids
	 * @param Function callback
	 * @param Object extra
	 */
	messageDialog(mess, uids, callback, extra)
	{
		setTimeout(function() {

			var sended = undefined;
			
			var r = ((Math.random() * 1000) % 100);
			console.log("wdlib.api.local.Api: messageDialog: ", r);

			if(r < 50) {
				sended = uids;
			}

			callback.call(undefined, sended);
		}, 1000);
	}

	setAppSize(width, height)
	{
	}
}

exports.LocalApi = LocalApi;

// ============================================================================

// ============================================================================
// STATIC HELPER FUNCTIONS

exports.getTestUsers = function(/*int*/ N)
{
	var users = [];
	var m = 0;
	var w = 0;
	var sex = wdlib.api.MALE;

	for(var i=0; i<N && m<FAKE_MEN_LIST.length && w<FAKE_GIRLS_LIST.length; ++i) {
		var data;
		if(i % 2 == 0) {
			data = FAKE_MEN_LIST[m];
			sex = wdlib.api.MALE;
			m++;
		}
		else {
			data = FAKE_GIRLS_LIST[w];
			sex = wdlib.api.FEMALE;
			w++;
		}
		var user = new wdlib.model.user.User;
		user.init({
			//id: data[0],
			remote_id: data[0],
			platform: wdlib.Config.CLIENT_PLATFORM,
			sex: sex,
			name: data[1],
			age: data[2],
			city: data[3],
			pic: data[4],
			big_pic: data[4],
			is_app_user: 1
		});
		users.push(user);
	}

	return users;
}
		
exports.getTestUsersBySex = function(/*int*/N, /*int*/sex)
{
	var users = [];
	var list = (sex == wdlib.api.MALE ? FAKE_MEN_LIST : FAKE_GIRLS_LIST);

	for (var i = 0; users.length < N && i < list.length; ++i) {
		var data = list[i];
		if(wdlib.model.currentCountCurrentUser.USER.id == data[0]) continue;

		var user = new wdlib.model.user.User;
		user.init({
			//id: data[0],
			remote_id: data[0],
			platform: wdlib.Config.CLIENT_PLATFORM,
			sex: sex,
			name: data[1],
			age: data[2],
			city: data[3],
			pic: data[4],
			bigPic: data[4],
			zodiac_name: data[5],
			is_app_user: 1
		});
		users.push(user);
	}
	return users;
}
// ============================================================================

}, (wdlib.api = wdlib.api || {}).local = {});
