<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * -------------------------------------------------------------------
 * AUTO-LOADER
 * -------------------------------------------------------------------
 * This file defines the namespaces and class maps so the Autoloader
 * can find the files as needed.
 *
 * NOTE: If you use an identical key in $psr4 or $classmap, then
 * the values in this file will overwrite the framework's values.
 */

class Autoload extends AutoloadConfig
{

	/**
	 * -------------------------------------------------------------------
	 * Namespaces
	 * -------------------------------------------------------------------
	 * This maps the locations of any namespaces in your application to
	 * their location on the file system. These are used by the autoloader
	 * to locate files the first time they have been instantiated.
	 *
	 * The '/app' and '/system' directories are already mapped for you.
	 * you may change the name of the 'App' namespace if you wish,
	 * but this should be done prior to creating any namespaced classes,
	 * else you will need to modify all of those classes for this to work.
	 *
	 * Prototype:
	 *
	 *   $psr4 = [
	 *       'CodeIgniter' => SYSTEMPATH,
	 *       'App'	       => APPPATH
	 *   ];
	 *
	 * @var array
	 */
	public $psr4 = [
		APP_NAMESPACE 	=> APPPATH, // For custom app namespace
		'Config'      	=> APPPATH . 'Config',
		'Channels'	  	=> APPPATH . 'ThirdParty/channels',
		'Flows'	  	  	=> ROOTPATH . 'platform/flows',
		'Subscribers'	=> ROOTPATH . 'platform/subscribers',
		'Services'		=> ROOTPATH . 'platform/services',
		'Clients'		=> ROOTPATH . 'platform/clients',
		'Inbox'			=> ROOTPATH . 'platform/inbox',
		'Vkcom'			=> ROOTPATH . 'platform/vkcom',
		'Widgets'		=> ROOTPATH . 'platform/widgets',
		'Admin'			=> ROOTPATH . 'platform/admin',
		'Api'			=> ROOTPATH . 'platform/api',
		'Lms'			=> ROOTPATH . 'platform/lms',
		'Users'			=> ROOTPATH . 'platform/users',
		'Referal'		=> ROOTPATH . 'platform/referal',

		'Workouts'			=> ROOTPATH . 'platform/workouts',

	];

	/**
	 * -------------------------------------------------------------------
	 * Class Map
	 * -------------------------------------------------------------------
	 * The class map provides a map of class names and their exact
	 * location on the drive. Classes loaded in this manner will have
	 * slightly faster performance because they will not have to be
	 * searched for within one or more directories as they would if they
	 * were being autoloaded through a namespace.
	 *
	 * Prototype:
	 *
	 *   $classmap = [
	 *       'MyClass'   => '/path/to/class/file.php'
	 *   ];
	 *
	 * @var array
	 */
	public $classmap = [
		// 'ChannelTransport' => APPPATH . 'ThirdParty/channels/ChannelTransport.php'
	];
}
