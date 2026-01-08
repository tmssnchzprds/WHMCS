<?php

use WHMCS\Module\Addon\EasyFavicon\Admin\AdminDispatcher;
use WHMCS\Database\Capsule as DB;

if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

/**
 * @return array
 */
function easy_favicon_config()
{
	return array(
		'name' => 'Easy Favicon',
		'description' => 'Set favicons for client and admin areas without coding.',
		'author' => 'ZebraSnake',
		'language' => 'english',
		'version' => '1.14',
	);
}

/**
 * @return void
 */
function easy_favicon_activate()
{
	DB::table('tbladdonmodules')->updateOrInsert(
	 	array(
			'module' => 'easy_favicon',
			'setting' => 'access'
		),
	 	array(
			'value' => '1'
		)
	);
	$dir = __DIR__ . '/favicons';
	if (!file_exists($dir) || !is_dir($dir)) {
		mkdir($dir);
	}
	chmod($dir, 0777);
}

/**
 * @return string
 */
function easy_favicon_output($vars)
{
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';

	$dispatcher = new AdminDispatcher();
	$vars['_path'] = __DIR__;
	$response = $dispatcher->dispatch($action, $vars);
	echo $response;
}
