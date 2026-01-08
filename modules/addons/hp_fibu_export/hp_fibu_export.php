<?php

require ROOTDIR . '/vendor/autoload.php';

use WHMCS\Module\Addon\HpFibuExport\Admin\AdminDispatcher;

if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

/**
 * Konfiguration des Addons
 * @return array
 */
function hp_fibu_export_config()
{
	return [
		// Display name for your module
		'name' => 'Invoice mass pdf export',
		// Description displayed within the admin interface
		'description' => 'Export your invoices as pdf files, grouped in a zip file.',
		// Module author name
		'author' => 'Hosting Pilot GmbH',
		// Default language
		'language' => 'english',
		// Version number
		'version' => '1.0',
		'fields' => [],
	];
}

/**
 * Admin-Adrea Output
 * Weiterleitung an den Controller.
 * @param $vars
 */
function hp_fibu_export_output($vars)
{
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

	$dispatcher = new AdminDispatcher();
	$response = $dispatcher->dispatch($action, $vars);
	echo $response;
}
