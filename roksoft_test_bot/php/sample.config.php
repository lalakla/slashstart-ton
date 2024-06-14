<?php

$path = ".";
$wdlib = "$path/libs/wdlib-php";
$autoload = array(
	"MyDB" => "$path/libs/MyDB/",
	"TelegramBot" => "$path/lib/TelegramBot/",
);

$config = array(
	"domain" => "http://chat-bot-test.lalakla.ru",
	"project_root" => __DIR__,
	"project_name" => "CHAT-BOT-TEST",
	"project_url" => "http://chat-bot-test.lalakla.ru",

// STATIC URLs ------------------------------------------------------
	// CDN
	//"static_url" => "http://73505069-48c9-447b-9515-774a9411ca5c.selcdn.net/vkelka",
	"lib_url" => "http://73505069-48c9-447b-9515-774a9411ca5c.selcdn.net",
	// MY
	"static_url" => "http://chat-bot-test.lalakla.ru/static",
//	"lib_url" => "http://static5.lalakla.ru",
// ------------------------------------------------------------------
);
// app version
$config["app_version"] = "app-test";
$config["app_url"] = "http://chat-bot-test.lalakla.ru/static/{$config["app_version"]}"; // MY STATIC
//$config["app_url"] = "http://73505069-48c9-447b-9515-774a9411ca5c.selcdn.net/need4me/{$config["app_version"]}"; // CDN

// common libraries versions
$config["wdlib_version"] = "test";
$config["jquery_version"] = "3.7.1";
$config["jquery_ui_version"] = "1.13.0";
$config["tonconnect_ui_version"] = "2.0.0";
$config["bootstrap_version"] = "5.2.3";
$config["phaser_version"] = "3.70.0";
$config["howler_version"] = "2.2.3";
$config["dayjs_version"] = "1.10.7";
$config["template7_version"] = "1.4.1";
$config["vk_bridge_version"] = "2.12.2";

$config["templates"] = $config["project_root"]."/templates";

$config["apis"] = array(
	"local" => array(
		"app_id" => "583",
		"server_key" => "------------------------------",
		"client_key" => "-----------------------",
		"name" => "local"
	),
	// Telegram Bot api
	"telegram" => array(
		"app_id" => "roksoft_test_bot",
		"web_app_id" => "app",
		"server_key" => "------------------------------------------------",
		"name" => "telegram",
	)
);

// database
$config["mydb_storage"] = array(
    "stats" => array (
        "host" => "localhost",
        "user" => "root",
        "pass" => "",
        "db_name" => "chat_bot_test_stats"
    ),
    "default" => array (
        "host" => "localhost",
        "user" => "root",
        "pass" => "",
        "db_name" => "chat_bot_test",
        "charset" => "utf8mb4",
    )
);

$config["mydb_storage_util"] = array(
    "WDLIB" => array(
        "Model" => "default"
    ),
    "Model" => "default"
);

$config["memcached"] = array(
//	"persistent_id" => "chatbot.test",
	"prefix" => "chatbot.test",
	"servers" => array(
		"default" => array(
			"host" => "/tmp/memcached.sock",
			"port" => 0
		)
	)
);
