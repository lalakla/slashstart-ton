<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

$routes->addPlaceholder('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
$routes->addPlaceholder('email', '[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9_]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,5})');
$routes->addPlaceholder('module', '[A-Za-z0-9_]{2,32}');
$routes->addPlaceholder('slug', '[A-Za-z0-9_-]{1,128}');
$routes->addPlaceholder('signedmodule', '[A-Za-z0-9_]{2,32}-[A-Za-z0-9_]{2,32}');


$routes->setAutoRoute(false);


// Cron
// $routes->cli('/flows/process/(:segment)/(:slug)', '\Flows\Controllers\Process::process/$1/$2'); // заменено на новый тип
$routes->cli('/cron/ssl', '\App\Controllers\Cron::ssl');
$routes->cli('/cron/domains', '\App\Controllers\Cron::domains');
// $routes->get('/flows/process/test/(:slug)', '\Flows\Controllers\Process::test/$1'); // TEST
// $routes->get('/flows/process/(:segment)/(:slug)', '\Flows\Controllers\Process::process/$1/$2'); // TEST



// worker($type = 0, $id = 1, $test = 0, $limit = null, $subscriberId = null)
// $routes->cli('/services/messages/worker/(:segment)/(:slug)', '\Services\Controllers\MessagesQueue::worker/$1/$2'); // updated, disabled

// /opt/php74/bin/php /var/www/rplto/data/www/rplto.net/index.php services messages run_project_worker
$routes->cli('/services/messages/gearman_worker/(:slug)/(:slug)', '\Services\Controllers\MessagesQueue::geramanWorker/$1/$2');
$routes->cli('/services/messages/run_project_worker/(:slug)', '\Services\Controllers\MessagesQueue::runMessagesWorkers4Projects/$1');
$routes->cli('/services/messages/process_messages_worker/(:slug)/(:slug)', '\Services\Controllers\MessagesQueue::processProjectMessages/$1/$2');
$routes->cli('/services/tasks/process_tasks_worker/(:slug)', '\Services\Controllers\MessagesQueue::tasksWorker/$1');

// /opt/php74/bin/php /var/www/rplto/data/www/rplto.net/index.php flows process reset 1
$routes->cli('/flows/process/reset/(:slug)', '\Flows\Controllers\Process::resetWorkers/$1');
$routes->cli('/flows/process/manual/(:slug)/(:slug)', '\Flows\Controllers\Process::manualStart/$1/$2');

// /opt/php74/bin/php /var/www/rplto/data/www/rplto.net/index.php flows subscribers process 1
$routes->cli('/flows/subscribers/process/(:slug)', '\Flows\Controllers\Process::startWorkers/$1'); // [/(:slug) - /$2]
$routes->cli('/flows/subscribers/process/(:slug)/(:slug)', '\Flows\Controllers\Process::startWorkers/$1/$2');
// /opt/php74/bin/php /var/www/rplto/data/www/rplto.net/index.php flows campaigns process 1
$routes->cli('/flows/campaigns/process/(:slug)', '\Flows\Controllers\Process::startCampaigns/$1');


// $routes->get('/services/messages/worker/(:segment)/(:slug)', '\Services\Controllers\MessagesQueue::worker/$1/$2'); // TEST
// $routes->get('messages/worker/(:num)/(:num)', '\Services\Controllers\MessagesQueue::worker/$1/$2'); // TEST
// $routes->get('messages/worker/test', '\Services\Controllers\MessagesQueue::test');	// TEST
// $routes->get('messages/worker/testchannels/(:num)/(:num)', '\Services\Controllers\MessagesQueue::testchannels/$1/$2'); // TEST

// Referal page
$routes->get('/referal', '\Referal\Controllers\Referal::index', ['as' => 'referal', 'filter' => 'auth:before,logged']);
$routes->get('/referal/ref/(:slug)', '\Referal\Controllers\Referal::referalsUp/$1', ['filter' => 'auth:before,logged']);
$routes->get('/referel/level/(:slug)', '\Referal\Controllers\Referal::referalLevelUp/$1', ['filter' => 'auth:before,logged']);
$routes->get('/referal/received_to_active/(:slug)', '\Referal\Controllers\Referal::receivedToActive/$1', ['filter' => 'auth:before,logged']);
$routes->get('/referal/personal_data/(:slug)', '\Referal\Controllers\Referal::getPersonalData/$1', ['filter' => 'auth:before,logged']);
$routes->get('/referal/add_points/(:slug)', '\Referal\Controllers\Referal::addPoints/$1', ['filter' => 'auth:before,logged']);

$routes->get('/referal/(:slug)/(:slug)', '\Referal\Controllers\Referal::getReferal/$1/$2');
$routes->post('/referal/user_data', '\Referal\Controllers\Referal::personalDataSave');
$routes->post('/referal/terms_agreed', '\Referal\Controllers\Referal::termsAgreedSave');
$routes->post('/referal/user_moneyback', '\Referal\Controllers\Referal::moneyBack');

$routes->post('/referal/updateSettings', '\Referal\Controllers\Referal::updateSettings', ['filter' => 'auth:before,logged']);

$routes->group('workouts', ['namespace' => 'Workouts\Controllers'], function($routes)
{
	$routes->get('/', 'Workouts::home', ['as' => 'workouts-home']);
	$routes->get('(:slug)', 'Workouts::workout/$1', ['as' => 'workout-workout']);
	$routes->post('(:slug)/start', 'Workouts::workout/$1/start', ['as' => 'workout-start']);
	$routes->get('(:slug)/done', 'Workouts::workout/$1/done', ['as' => 'workout-done']);


	// $routes->get('(:slug)/(:slug)', 'Workouts::exercise/$1/$2', ['as' => 'workout-exercise']);

	
	$routes->get('(:slug)/(:slug)/prepare', 'Workouts::exercise/$1/$2/prepare', ['as' => 'workout-exercise-prepare']);
	$routes->post('(:slug)/(:slug)/start', 'Workouts::exercise/$1/$2/start', ['as' => 'workout-exercise-start']);

	$routes->get('(:slug)/(:slug)/set', 'Workouts::exercise/$1/$2/set', ['as' => 'workout-exercise-set-current']);
	$routes->post('(:slug)/(:slug)/set', 'Workouts::exercise/$1/$2/set', ['as' => 'workout-exercise-set']);
	
	$routes->get('(:slug)/(:slug)/rest', 'Workouts::exercise/$1/$2/rest', ['as' => 'workout-exercise-rest-current']);
	$routes->post('(:slug)/(:slug)/rest', 'Workouts::exercise/$1/$2/rest', ['as' => 'workout-exercise-rest']);

	




	$routes->post('reps', 'Workouts::reps', ['as' => 'workouts-reps-track']);
	
});





$routes->post('/facebook/webhook', 'Webhook::proxy/facebook', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);
$routes->get('/facebook/webhook', 'Webhook::proxy/facebook', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);


$routes->get('/learn/(:slug)', '\Lms\Controllers\Student::course/$1', ['as' => 'lms-course-share']);
if (IS_CLIENTS_SITE)
{
	$routes->get('slst-check-domain', '\App\Controllers\Settings::checkDomain');

	$routes->get('/apps/shop', 'AppShop::index', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);
	$routes->post('/apps/shop', 'AppShop::actions', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);

	$routes->get('/apps/referal', 'AppShop::referal', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);

	$routes->get('/apps/twilms', 'Student::authfromapp', ['namespace' => 'Lms\Controllers', 'filter' => 'minimize:after']);

	$routes->get('dashboard', '\Clients\Controllers\Dashboard::index', ['filter' => 'auth:before,logged']);
	$routes->group('learn', ['namespace' => 'Lms\Controllers', 'filter' => 'minimize:after'], function($routes)
	{
		$routes->get('/', 'Student::home', ['as' => 'lms-student-home']);
		$routes->get('logout', 'Student::logout', ['as' => 'lms-student-logout']);
		$routes->get('courses', 'Student::courses', ['as' => 'lms-student-courses']);
		$routes->get('(:slug)', 'Student::course/$1', ['as' => 'lms-student-course']);
		$routes->get('(:slug)/module/(:slug)', 'Student::module/$1/$2', ['as' => 'lms-student-module']);
		$routes->get('(:slug)/lesson/(:slug)', 'Student::lesson/$1/$2', ['as' => 'lms-student-lesson']);
	});



	$routes->get('/invoice', 'Invoices::create', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);
	$routes->get('/invoice/check', 'Invoices::check', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);
	$routes->get('/invoice/success', 'Invoices::success', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);
	$routes->get('/invoice/fail', 'Invoices::fail', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);
	$routes->get('/payment/success', 'Invoices::thanks', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);
	$routes->get('/payment/error', 'Invoices::error', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);
	$routes->get('/payment/disabled', 'Invoices::disabled', ['namespace' => 'Services\Controllers', 'filter' => 'minimize:after']);


	$routes->group('services', ['namespace' => 'Services\Controllers'], function($routes)
	{
		$routes->post('(:signedmodule)/webhook', 'Webhook::process/$1');
		$routes->post('(:signedmodule)/webhook/(:module)', 'Webhook::process/$1/$2');

		$routes->get('(:signedmodule)/webhook', 'Webhook::process/$1');
		$routes->get('(:signedmodule)/webhook/(:module)', 'Webhook::process/$1/$2');
	});


	$routes->get('l/(:slug)', 'Shortlinks::redirect/$1');
	$routes->get('c/(:slug)/(:segment)', 'Shortlinks::click/$1/$2');
	$routes->get('c/(:slug)/(:slug)/(:segment)', 'Shortlinks::click/$1/$2/$3');



	$routes->post('/widgets/(:slug)', 'Widgets::subscribe/$1', ['namespace' => 'Widgets\Controllers']);
	$routes->get('/widgets/(:slug)', 'Widgets::subscribe/$1', ['namespace' => 'Widgets\Controllers']);

	$routes->get('/s/(:slug)/(:slug)', 'Widgets::link/$1/$2', ['namespace' => 'Widgets\Controllers', 'filter' => 'minimize:after']);

	$routes->get('/widget/(:slug)', 'Widgets::widget/$1', ['namespace' => 'Widgets\Controllers', 'filter' => 'minimize:after']);
	$routes->get('(:slug)', 'Widgets::widget/$1', ['namespace' => 'Widgets\Controllers', 'filter' => 'minimize:after']);


	$routes->get('/', 'Widgets::home', ['namespace' => 'Widgets\Controllers']);


	return;
}



if (!IS_APP)
{
	// https://rplto.net/proxy/services/install/vk/token
	$routes->get('proxy/services/(.*)', 'Services::proxy', ['namespace' => 'Services\Controllers']);



}


// $routes->get('account/register', 'Clients\Controllers\RegistrationController::register', ['subdomain' => 'cp']);
// $routes->post('account/register', 'Clients\Controllers\RegistrationController::attemptRegister', ['subdomain' => 'cp']);




// VK APP
$routes->group('vkcom', ['namespace' => 'Vkcom\Controllers'], function($routes)
{
	$routes->get('/', 'Vkcom::index');
	$routes->get('auth', 'Vkcom::auth');
});


$routes->post('payment/yookassa', '\Clients\Controllers\Billing::processYookassa');
$routes->get('payment/yookassa', '\Clients\Controllers\Billing::processYookassa'); // TEST


/* Далее роуты приложения */

if (!IS_APP)  return;


// Subscribers
// GET /subscribers - Get account's subscribers
// POST /subscribers - Add new single subscriber
// GET /subscribers/(:id or :email) - Get single subscriber
// POST /subscribers/(:id or :email) - Update single subscriber
// GET /subscribers/search - Search for subscribers ?query=demo@mailerlite.com
// GET /subscribers/(:id or :email)/groups - Get groups subscriber belongs to
// GET /subscribers/(:id or :email)/activity - Get activity (clicks, opens, etc) of selected subscriber
// GET /subscribers/(:id or :email)/activity/:type - Get activity of selected subscriber by specified type (opens, clicks, etc)

// $routes->get('subscribers', 'Subscribers::list');
// $routes->post('subscribers', 'Subscribers::create');

// $routes->get('subscribers/(:email)', 'Subscribers::get/$1');
// $routes->get('subscribers/(:num)', 'Subscribers::get/$1');

// $routes->post('subscribers/(:email)', 'Subscribers::update/$1');
// $routes->post('subscribers/(:num)', 'Subscribers::update/$1');



// Admin
$routes->group('admin', ['subdomain' => ADMIN_SUBDOMAIN, 'namespace' => 'Admin\Controllers', 'filter' => 'auth:before,logged'], function($routes)
{
	$routes->get('/', 'Clients::index');
	$routes->post('/', 'Clients::update');

	$routes->get('export', 'Clients::export');


	$routes->get('projects/sql', 'Projects::sql');
	$routes->post('projects/sql', 'Projects::sql');
	$routes->get('projects/redis', 'Projects::redis');
	$routes->post('projects/redis', 'Projects::redis');

	$routes->get('projects/subscriptions', 'Projects::subscriptions');



	// $routes->get('globals', 'Globals::index');
	// $routes->post('globals', 'Globals::save');
	// $routes->post('globals/delete/(:num)', 'Globals::delete/$1');
});





$routes->post('uploader', 'Uploader::upload', ['filter' => 'auth:before,logged']);
$routes->get('qrcode', 'QR::image', ['as' => 'qr-image', 'filter' => 'auth:before,logged']);



$routes->get('settings/(:slug)', 'Settings::index/$1', ['filter' => 'auth:before,logged']);
$routes->get('settings', 'Settings::index', ['filter' => 'auth:before,logged']);
$routes->post('settings', 'Settings::update', ['filter' => 'auth:before,logged']);
$routes->post('settings/domain', 'Settings::domain', ['filter' => 'auth:before,logged']);
$routes->post('settings/users', 'Settings::users', ['filter' => 'auth:before,logged']);
$routes->post('settings/users/access', 'Settings::users_access', ['filter' => 'auth:before,logged']);
$routes->post('settings/users/delete/(:slug)', 'Settings::users_delete/$1', ['filter' => 'auth:before,logged']);

$routes->post('settings/tokens', 'Settings::tokens', ['filter' => 'auth:before,logged']);
$routes->post('settings/tokens/delete/(:slug)', 'Settings::tokens_delete/$1', ['filter' => 'auth:before,logged']);


// $routes->get('folders', 'Folders::index', ['filter' => 'auth:before,logged']); // все через попапы
$routes->post('folders', 'Folders::update', ['filter' => 'auth:before,logged']);
$routes->post('folders/move', 'Folders::move', ['filter' => 'auth:before,logged']);




// Audience
$routes->group('audience', ['namespace' => 'Subscribers\Controllers', 'filter' => 'auth:before,logged'], function($routes)
{
	$routes->get('/', 'Subscribers::index');
	$routes->post('/', 'Subscribers::update');
	$routes->post('(:num)', 'Subscribers::save/$1');
	$routes->post('tester/(:num)', 'Subscribers::toggle/$1/tester');
	$routes->post('delete/(:num)', 'Subscribers::delete/$1');

	$routes->get('import/csv', 'Subscribers::importCSV');

	$routes->get('groups', 'Groups::index');
	$routes->post('groups', 'Groups::save');
	$routes->post('groups/delete/(:num)', 'Groups::delete/$1');

	$routes->get('tags', 'Tags::index');
	$routes->post('tags', 'Tags::save');
	$routes->post('tags/delete/(:num)', 'Tags::delete/$1');

	$routes->get('attributes', 'Attributes::index');
	$routes->post('attributes', 'Attributes::save');
	$routes->post('attributes/delete/(:num)', 'Attributes::delete/$1');

	$routes->get('globals', 'Globals::index');
	$routes->post('globals', 'Globals::save');
	$routes->post('globals/delete/(:num)', 'Globals::delete/$1');
});


// Campaigns
$routes->group('campaigns', ['namespace' => 'Flows\Controllers', 'filter' => 'auth:before,logged'], function($routes)
{
	$routes->get('/', 'Campaigns::index');

	$routes->post('create', 'Campaigns::new');

	$routes->post('delete/(:num)', 'Campaigns::delete/$1');
	$routes->post('activate/(:num)', 'Campaigns::activate/$1');
	$routes->post('deactivate/(:num)', 'Campaigns::deactivate/$1');
	$routes->post('updatestats/(:num)', 'Campaigns::updatestats/$1');


});




// Widgets
$routes->group('widgets', ['namespace' => 'Widgets\Controllers', 'filter' => 'auth:before,logged'], function($routes)
{
	$routes->get('/', 'Widgets::index');
	$routes->get('edit/(:num)', 'Widgets::edit/$1');
	$routes->get('copy/(:num)', 'Widgets::copy/$1');
	$routes->post('edit/(:num)', 'Widgets::edit/$1');
	$routes->get('create/(:num)', 'Widgets::create/$1');
	$routes->post('delete/(:num)', 'Widgets::delete/$1');
});



// Workflows
$routes->group('flows', ['namespace' => 'Flows\Controllers', 'filter' => 'auth:before,logged'], function($routes)
{
	$routes->get('/', 'Flows::index');
	$routes->get('shop', 'Flows::shop');
	$routes->get('(:num)', 'Flows::builder/$1');
	$routes->post('(:num)', 'Flows::save/$1');
	$routes->get('copy/(:num)', 'Flows::copy/$1');
	$routes->get('share/(:num)', 'Flows::share/$1'); // TEST
	$routes->post('share/(:num)', 'Flows::share/$1');

	$routes->post('rename', 'Flows::rename');

	$routes->get('templates/(:slug)', 'Flows::templates/$1');
	$routes->get('templates', 'Flows::templates');
	$routes->get('template/(:slug)', 'Flows::template/$1');
	$routes->post('template', 'Flows::template');


	$routes->post('item/(:num)', 'Flows::save_item/$1');
	$routes->post('item/(:num)/delete', 'Flows::delete_item/$1');
	$routes->post('item/(:num)/create', 'Flows::create_item/$1');
	$routes->post('item/(:num)/create/(:num)', 'Flows::create_item/$1/$2');
	$routes->post('item/(:num)/stats', 'Flows::stats_item/$1');


	$routes->get('queue', 'Flows::queue');

	$routes->get('links', 'Flows::links');


	$routes->post('create', 'Flows::new');
	$routes->post('delete/(:num)', 'Flows::delete/$1');
	$routes->post('activate/(:num)', 'Flows::activate/$1');
	$routes->post('deactivate/(:num)', 'Flows::deactivate/$1');
	$routes->post('create/(:alpha)', 'Flows::create/$1');
	$routes->post('intdata/(:module)', 'Flows::intdata/$1');
	$routes->get('intdata/(:module)', 'Flows::intdata/$1');



	$routes->post('console/(:num)', 'Process::console/$1');

	$routes->post('request', 'Process::request');
});




// Workflows
$routes->group('inbox', ['namespace' => 'Inbox\Controllers', 'filter' => 'auth:before,logged'], function($routes)
{
	$routes->get('/', 'Inbox::inbox');
	$routes->post('send', 'Inbox::send');
	$routes->post('updates', 'Inbox::updates');
	$routes->post('chat/action/(:slug)', 'Inbox::chat_action/$1');

	$routes->get('comments', 'Inbox::comments');
	$routes->post('comments/delete/(:num)', 'Inbox::comments_delete/$1');
	$routes->post('comments/reply', 'Inbox::comments_reply/$1');


	$routes->get('(:module)', 'Inbox::inbox/$1');
	$routes->post('(:module)', 'Inbox::conversation/$1');
});



$routes->group('apps', ['namespace' => 'Services\Controllers', 'filter' => 'auth:before,logged'], function($routes)
{
	$routes->get('shop', 'AppShop::admin_index');
	$routes->post('shop', 'AppShop::admin_index');
	$routes->get('shop/product', 'AppShop::admin_product');
	$routes->get('shop/product/(:slug)', 'AppShop::admin_product/$1');
	$routes->post('shop/product', 'AppShop::admin_product');


	$routes->get('shop/orders', 'AppShop::admin_orders');
	$routes->get('shop/order/(:slug)', 'AppShop::admin_order/$1');
	$routes->post('shop/order', 'AppShop::admin_order');


	$routes->get('shop/categories', 'AppShop::admin_categories');
	$routes->post('shop/categories', 'AppShop::admin_categories');
	$routes->get('shop/category', 'AppShop::admin_category');
	$routes->get('shop/category/(:slug)', 'AppShop::admin_category/$1');
	$routes->post('shop/category', 'AppShop::admin_category');


	$routes->post('shop/import/yml', 'AppShop::admin_import_yml');
	$routes->post('shop/import/wb', 'AppShop::admin_import_wb');


	$routes->get('shop/config', 'AppShop::admin_config');
	$routes->post('shop/config', 'AppShop::admin_config');
});


$routes->group('services', ['namespace' => 'Services\Controllers', 'filter' => 'auth:before,logged'], function($routes)
{
	$routes->get('/', 'Services::index');
	$routes->get('install/(:slug)', 'Services::install/$1');
	$routes->get('install/(:slug)/(:module)', 'Services::install/$1/$2');
	$routes->get('install/(:slug)/(:module)/(:module)', 'Services::install/$1/$2/$3');
	$routes->get('config/(:slug)', 'Services::config/$1');


	$routes->post('config/(:slug)', 'Services::update/$1');
	$routes->post('install/(:slug)', 'Services::setup/$1');
	$routes->post('uninstall/(:slug)', 'Services::delete/$1');




	$routes->get('(:signedmodule)/oauth', 'Oauth::process/$1');
	$routes->get('(:signedmodule)/oauth/token', 'Oauth::process/$1/token');




	$routes->get('(:signedmodule)', 'Service::process/$1');
	$routes->get('(:signedmodule)/(:module)', 'Service::process/$1/$2');
	$routes->get('(:signedmodule)/(:module)/(:module)', 'Service::process/$1/$2/$3');
	$routes->post('(:signedmodule)', 'Service::process/$1');
	$routes->post('(:signedmodule)/(:module)', 'Service::process/$1/$2');
	$routes->post('(:signedmodule)/(:module)/(:module)', 'Service::process/$1/$2/$3');


});



$routes->get('billing', '\Clients\Controllers\Billing::index', ['filter' => 'auth:before,logged']);
$routes->get('billing/order', '\Clients\Controllers\Billing::order', ['filter' => 'auth:before,logged']);
$routes->get('billing/partners', '\Clients\Controllers\Billing::partners', ['filter' => 'auth:before,logged']);
$routes->get('billing/partners/help', '\Clients\Controllers\Billing::help', ['filter' => 'auth:before,logged']);
$routes->get('billing/transactions', '\Clients\Controllers\Billing::transactions', ['filter' => 'auth:before,logged']);
$routes->get('billing/partners/payout/details', '\Clients\Controllers\Billing::payout_details', ['filter' => 'auth:before,logged']);
$routes->post('billing/partners/payout/details', '\Clients\Controllers\Billing::payout_details', ['filter' => 'auth:before,logged']);
$routes->post('billing/partners/payout', '\Clients\Controllers\Billing::payout', ['filter' => 'auth:before,logged']);
$routes->post('billing/disable/(:slug)', '\Clients\Controllers\Billing::disable/$1', ['filter' => 'auth:before,logged']);
$routes->post('billing/promocode/create', '\Clients\Controllers\Billing::create_promocode', ['filter' => 'auth:before,logged']);
$routes->post('billing/promocode', '\Clients\Controllers\Billing::promocode', ['filter' => 'auth:before,logged']);


$routes->get('dashboard', '\Clients\Controllers\Dashboard::index', ['as' => 'dashboard', 'filter' => 'auth:before,logged']);
$routes->get('dashboard/masters', '\Clients\Controllers\Dashboard::masters', ['as' => 'dashboard-masters', 'filter' => 'auth:before,logged']);
$routes->get('dashboard/start', '\Clients\Controllers\Dashboard::start', ['as' => 'dashboard-start', 'filter' => 'auth:before,logged']);
$routes->get('dashboard/vk', '\Clients\Controllers\Dashboard::vk', ['as' => 'dashboard-vk', 'filter' => 'auth:before,logged']);
$routes->post('dashboard/ping', '\Clients\Controllers\Dashboard::ping', ['filter' => 'auth:before,logged']);
$routes->get('dashboard/ping', '\Clients\Controllers\Dashboard::ping', ['filter' => 'auth:before,logged']);


$routes->get('stats', '\Flows\Controllers\Stats::index', ['filter' => 'auth:before,logged']);

$routes->post('shortlink', 'Shortlinks::shorten', ['filter' => 'auth:before,logged']);


$routes->post('pages/message', 'Pages::message', ['filter' => 'auth:before,logged']);



$routes->get('/', '\Clients\Controllers\LoginController::login');



// if (!IS_APP) return;












/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}