<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

require_once(__DIR__ . '/Translator.php');


require_once(__DIR__ . '/config.' . APP_ENVIRONMENT_CONFIGS . '.php');



define('REQUEST_URI', getenv('REQUEST_URI'));

define('HOST_CURRENT', preg_replace('#^www\.#si', '', getenv('HTTP_HOST')));

// login.app.rplto.net - админка
// login.rplto.net - сайт, без авторизации
//

/**
 * subdomains
 */
// Если поддомен или свой домен
if (strpos(HOST_CURRENT, HOST_APP) > 0 && HOST_CP != HOST_CURRENT)
{
	define('IS_APP', 1);
	define('IS_CLIENTS_SITE', 0);


	$domain = str_replace(HOST_APP, '', HOST_CURRENT);
	$domain = preg_replace('#[^0-9a-z\.-]#si', '', $domain);

	define('PROJECT_DOMAIN', $domain);
	unset($domain);

}
elseif ((strpos(HOST_CURRENT, HOST_CLIENTS) > 0 || strpos(HOST_CURRENT, HOST_SERVICE) === false) && HOST_CP != HOST_CURRENT)
{
	define('IS_APP', 0);
	define('IS_CLIENTS_SITE', 1);

	define('PROJECT_DOMAIN_IS_CUSTOM', strpos(HOST_CURRENT, HOST_CLIENTS) > 0 ? 0 : 1);


	if (strpos(HOST_CURRENT, HOST_SERVICE) === false)
	{
		$domain = HOST_CURRENT;
	}
	else
	{
		$domain = str_replace(HOST_CLIENTS, '', HOST_CURRENT);
	}


	$domain = preg_replace('#[^0-9a-z\.-]#si', '', $domain);

	define('PROJECT_DOMAIN', $domain);
	unset($domain);
}
else
{
	define('IS_APP', 0);
	define('IS_CLIENTS_SITE', 0);

	define('PROJECT_DOMAIN', '');
}





define('PROJECT_NAME', 'Slashstart');
define('PROJECT_EMAIL', 'hello@slashstart.ru');

define('MAILER_FROM_EMAIL', 'info@slashstart.ru');
define('MAILER_FROM_NAME', 'Slashstart.ru');




define('LOCATION_COUNTRY', 'ru');


define('PROJECT_UUID_COOKIE', 'rpltoid');


define('CHANNEL_TYPE_EMAIL', 1);
define('CHANNEL_TYPE_SMS', 2);
define('CHANNEL_TYPE_VK', 3);
define('CHANNEL_TYPE_TELEGRAM', 4);
define('CHANNEL_TYPE_FACEBOOK', 5);
define('CHANNEL_TYPE_WHATSAPP', 6);
define('CHANNEL_TYPE_VIBER', 7);
define('CHANNEL_TYPE_INSTAGRAM', 8);

define('CHANNEL_TYPE_MAILERLITE', 100);
define('CHANNEL_TYPE_SENLER', 101);


define('MESSAGE_TYPE_TEXT', 1);
define('MESSAGE_TYPE_PHOTO', 2);
define('MESSAGE_TYPE_VIDEO', 3);
define('MESSAGE_TYPE_AUDIO', 4);
define('MESSAGE_TYPE_FILE', 5);

define('MESSAGE_TYPE_POST', 10);
define('MESSAGE_TYPE_STORY', 11);
define('MESSAGE_TYPE_LIVE', 12);
define('MESSAGE_TYPE_COMMENT', 13);

define('MESSAGE_TYPE_BCLICK', 20);
define('MESSAGE_TYPE_SCLICK', 21);
define('MESSAGE_TYPE_LCLICK', 22);

define('MESSAGE_TYPE_SYSTEM', 30); 


define('MESSAGES_WORKERS_COUNT', 10);
define('SERVICES_MESSANGER_WORKERS', 100);
define('SERVICES_MESSANGER_WORKERS_LIMIT', 10);
define('SERVICES_MESSANGER_THREADS_TELEGRAM', 10); // WORKERS / THREADS потоков будет. 1 thread будет обрабатывать Х заданий worker-ов


define('CHANNEL_FILE_TYPE_PHOTO', 1);
define('CHANNEL_FILE_TYPE_VIDEO', 2);
define('CHANNEL_FILE_TYPE_AUDIO', 3);
define('CHANNEL_FILE_TYPE_DOCUMENT', 4);


define('STAT_DAILY_SUBSCRIBERS', 1);
define('STAT_DAILY_TRIGGERS', 2);
define('STAT_DAILY_MESSAGESOUT', 3);
define('STAT_DAILY_MESSAGESIN', 4);
define('STAT_DAILY_WIDGETHITS', 5);



define('MESSANGER_PAYLOAD_KEY', 'egrnkvch32055c853a1874db53d20cf03d9d17c1');
define('INVOICES_SALT', 'egrnkvch05d9d17c15c853a18732054dbcf53d20');



define('WIDGET_TYPE_MICROLP',	1);
define('WIDGET_TYPE_FORM',		2);
define('WIDGET_TYPE_WEBINAR',	14);




define('GEARMAN_SERVER', '127.0.0.1');
define('GEARMAN_PORT', '4730');




define('VK_APP_API_VERSION', '5.130');



define('CRON_DIR', __DIR__ . '/../cron/');



define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_PORT', 22122);
define('MEMCACHE_WEIGHT', 1);
define('MEMCACHE_RAW', false);


define('AWS_S3_REGION', 'ru-1');
// define('AWS_S3_ENDPOINT', 'https://s3.storage.selcloud.ru');
define('AWS_S3_ENDPOINT', 'https://c.slashstart.ru');
define('AWS_S3_USERNAME', '151752_Slst');
define('AWS_S3_PASSWORD', '|?GmX]4G`g|?GmX]4G`g');
define('AWS_S3_BUCKET', 'ssst');


define('QUEUES_PROJECTS_MESSAGES_COUNT', 'qpmc');
define('QUEUES_PROJECT_MESSAGES', 'qpm:%s');
define('QUEUES_PROJECT_MESSAGES_USER', 'qpm:%s:%s');
define('QUEUES_PROJECTS_MESSAGES_RUN_WORKERS', 'qpmrw');

define('QUEUES_ACTIVE_FLOWS', 'qpf');
define('QUEUES_CAMPAIGNS', 'qc');


define('DNS_NS_1', 'ns1.slashstart.ru');
define('DNS_NS_2', 'ns2.slashstart.ru');
define('DNS_NS_CNAME', 'app.slashstart.ru.');


define('PUSHER_APP_ID', '1415149');
define('PUSHER_KEY', '818e392226cfe07d82db');
define('PUSHER_SECRET', '1c47cfddd6e4734df82d');
define('PUSHER_CLUSTER', 'eu');



define('INSTAGRAM_PROXY_URL', 'http://92.243.27.127/a.php');


require_once(__DIR__ . '/Cache.php');
require_once(__DIR__ . '/Platform.php');
require_once(__DIR__ . '/Common.DB.php');
require_once(__DIR__ . '/misc.php');

