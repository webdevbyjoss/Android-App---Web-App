<?php

/**
 * Set timezone
 */
date_default_timezone_set('Europe/Helsinki');

/*
 * Define the application environment
 */
$env = getenv('APPLICATION_ENV');
defined('APPLICATION_ENV')
	|| define('APPLICATION_ENV', (empty($env) ? 'production' : $env));

/**
 * Its bad practice to have email hardcoded here :) 
 * FIXME: Move this to configuration
 */
define('ADMIN_EMAIL', 'joseph.chereshnovsky@gmail.com,oleksiy.oliynyk@gmail.com');

if ('development' == APPLICATION_ENV) {
	/*
	 * Put errors ON for debugging this file
	 */
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	define('DEBUG_ENABLE', true);
	define('DISABLE_FULL_PAGE_CACHE', true);
	
	// $paths = explode(PATH_SEPARATOR, get_include_path());
	$paths[] = '/usr/share/php/libzend-framework-php/';
	
} else {
	define('DEBUG_ENABLE', false);
	define('DISABLE_FULL_PAGE_CACHE', false);
}

/*
 * Define the absolute/relative paths to the library path, the app library path,
 * app path and the database configuration path
 */
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application') );
define('APPLICATION_CACHE', realpath(APPLICATION_PATH . '/../tmp'));
define('APPLICATION_LIBRARY_PATH', realpath(APPLICATION_PATH . '/../library'));

/*
 * Set the include paths to point to the new defined paths
 */
$paths[] = APPLICATION_LIBRARY_PATH;
set_include_path(implode(PATH_SEPARATOR, $paths));

// lets measure the time of script execution
// and run the application initialization
require_once 'Custom/Starter.php';
$Starter = new Custom_Starter(APPLICATION_PATH, APPLICATION_ENV);
$Starter->run();