<?php

// if (isset($_GET['__SHOW_ERRORS']))
	// define('ENVIRONMENT', 'development');
// else
	
define('ENVIRONMENT', 'development');



define('CLI_CMD', 'php /home/slashstart/public_html/index.php ');

define('CLI_CMD_RESTART_MESSAGES_SUPERVISORD', 'systemctl restart supervisord');


define('APP_SALT', ''); // Salt

define('PUBLIC_HTML_PATH', '/home/slashstart/public_html/');

define('STATIC_SERVER_URL', ''); // Url

define('APP_PREFIX', 'slstd_');

define('MYSQL_COMMON_HOST', 'localhost');
define('MYSQL_COMMON_BASE', '');
define('MYSQL_COMMON_USER', '');
define('MYSQL_COMMON_PASS', '');


define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', '');
define('MYSQL_PASS', '');

define('MYSQL_BASE_NAME_TPL', 'slashstart_');


define('SERVICE_URL', 'https://slstd.ru/');
define('HOST_SERVICE', 'slstd.ru');
define('HOST_CP', 'cp.slstd.ru');
define('HOST_APP', '-app.slstd.ru');
define('HOST_SYSTEM', '-domain.slstd.ru');
define('HOST_CLIENTS', '.slstd.ru');

define('ADMIN_SUBDOMAIN', 'a');


define('API_PREFIX', 'Slashstart');
define('API_PREFIX_SHORT', 'slst_');

define('URL_PREFIX_SHORT', 'slst-');

define('API_EXTERNAL_JS_URL', 'https://s.slstd.ru/slashstart.js');


define('SERVICE_EXTERNAL_ID_KEY', '');
define('SERVICES_WEBHOOK_QUERY_KEY', '');
define('SERVICES_QUERY_KEY', '');
define('SERVICES_OAUTH_QUERY_KEY', '');


define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);


define('FLOWS_LOCK_TIMEOUT', 5);
define('FLOWS_REQUESTS_LIMIT', 50);
define('FLOWS_REQUESTS_PERIOD', 59);
define('FLOWS_REQUESTS_BLOCK', 590);


define('FB_APP_ID', '');
define('FB_APP_KEY', '');
define('FB_APP_VERIFY_TOKEN', '');




define('VK_API_CB_SERVER_NAME', 'Slashstart.ru');

define('VK_APP_ID', '');
define('VK_APP_KEY', '');
define('VK_APP_SERVICE_KEY', '');


define('VK_AUTH_APP_ID', '');
define('VK_AUTH_APP_KEY', '');
define('VK_AUTH_APP_SERVICE_KEY', '');


define('YOOKASSA_ID', '');
define('YOOKASSA_KEY', '');



define('MAILERLITE_KEY', '');
define('MAILERLITE_LIST_REGISTRATION', '');



define('SYSTEM_CMD_NGINX_RELOAD', 'systemctl reload nginx');
define('SYSTEM_CMD_NAMED_RELOAD', 'systemctl reload named');


define('DOMAINS_NAMED_ZONES_FILE', '/var/named/domains.conf');
define('DOMAINS_NAMED_ZONES_FOLDER', '/var/named/domains/');
define('DOMAINS_NAMED_ZONE_TPL', '/var/named/template-new-prod');

define('DOMAINS_NGINX_SERVERS_FOLDER', '/etc/nginx/domains/');
define('DOMAINS_NGINX_SERVERS_TPL', '/etc/nginx/domains/template-new-prod');
define('DOMAINS_NGINX_SERVERS_TPL_SSL', '/etc/nginx/domains/template-ssl-prod');




define('PLATFORM_COPY_INSTAGRAM', "Автоматизация сделана\r\nв @slashstart.ru");



define('FLOWS_ACTION_NOTIFY_TELEGRAM_NAME', "");
define('FLOWS_ACTION_NOTIFY_TELEGRAM_TOKEN', "");

