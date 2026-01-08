<?php

use WHMCS\Database\Capsule as DB;

function hook_easy_favicon_AdminAreaPage($vars)
{
	if (strpos($_SERVER['REQUEST_URI'], 'addonmodules.php?module=easy_favicon') === false) {
		return;
	}
	$file = __DIR__ . '/lang/' . $vars['adminLanguage'] . '.php';
	if (file_exists($file)) {
		include $file;
	}
	else {
		include __DIR__ . '/lang/english.php';
	}

	return array(
		'pagetitle' => $_ADDONLANG['mainpage_title'] . ' v' . easy_favicon_config()['version']
	);
}

function easy_favicon_get_icon_html($path, $name, $end = '" />')
{
	$file = __DIR__ . '/favicons/' . $name . '.ico';
	$path = trim($path, '/');
	if (file_exists($file)) {
		return '<link rel="shortcut icon" href="'
			. ($path ? '/' . $path : '')
			. '/modules/addons/easy_favicon/favicons/'
			. $name
			. '.ico?'
			. md5(file_get_contents($file))
			. $end;
	}
}

function hook_easy_favicon_AdminAreaHeadOutput($vars)
{
	return easy_favicon_get_icon_html($vars['WEB_ROOT'], 'admin');
}

function hook_easy_favicon_ClientAreaPageViewInvoice($vars)
{
	if ($icon = easy_favicon_get_icon_html($vars['WEB_ROOT'], 'client', '')) {
		return array(
			'charset' => $vars['charset'] . '" />' . "\n\t" . $icon
		);
	}
}

function hook_easy_favicon_ClientAreaHeadOutput($vars)
{
	return easy_favicon_get_icon_html($vars['WEB_ROOT'], 'client');
}

add_hook('AdminAreaPage', 1, 'hook_easy_favicon_AdminAreaPage');
add_hook('AdminAreaHeadOutput', 1, 'hook_easy_favicon_AdminAreaHeadOutput');
add_hook('ClientAreaHeadOutput', 1, 'hook_easy_favicon_ClientAreaHeadOutput');
add_hook('ClientAreaPageViewInvoice', 1, 'hook_easy_favicon_ClientAreaPageViewInvoice');
