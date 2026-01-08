<?php

namespace WHMCS\Module\Addon\EasyFavicon\Admin;

use WHMCS\Cookie;

class Controller
{
	protected $msg = '';
	protected $msg_type = 'info';
	protected $path = __DIR__ . '/../../favicons/';

	protected $lang = array();
	protected $vars = array();

	public function __construct($vars)
	{
		$this->lang = $vars['_lang'];

		$this->vars = $vars;
		$ef_msg = Cookie::get('ef_msg');
		if ($this->msg = isset($this->lang[$ef_msg]) ? $this->lang[$ef_msg] : $ef_msg) {
			if ($ef_msg === 'dir_not_exists' || $ef_msg === 'dir_perms_error') {
				$this->msg = str_replace('%s', realpath(str_replace('favicons/', '', $this->path)) . '/favicons', $this->msg);
			}
			Cookie::delete('ef_msg');
		}
		if ($this->msg_type = Cookie::get('ef_msg_type')) {
			Cookie::delete('ef_msg_type');
		}
	}

	public function index()
	{
		if ($msg = $this->checkFolder()) {
			$this->msg = isset($this->lang[$msg]) ? str_replace('%s', realpath(str_replace('favicons/', '', $this->path)) . '/favicons', $this->lang[$msg]) : $msg;
			$this->msg_type = 'error';
		}
		return array(
			'lang' => $this->lang,
			'files' => $this->getFiles(),
			'admin_hash' => $this->getHash('admin'),
			'client_hash' => $this->getHash('client'),
			'path' => explode('/admin/', $_SERVER['REQUEST_URI'])[0],
			'msg' => $this->msg,
			'msg_type' => $this->msg_type,
			'vars' => $this->vars,
			'pagetitle' => $this->lang['mainpage_title'],
			'template' => 'mainpage'
		);
	}

	public function unset()
	{
		if ($msg = $this->checkFolder()) {
			return $this->error($msg);
		}
		$section = $_REQUEST['section'];
		if (!$section) {
			return $this->error('unset_error_empty_section');
		}
		if (!in_array($section, array('admin', 'client'))) {
			return $this->error('unset_error_invalid_section');
		}
		$file = $this->path . $section . '.ico';
		unlink($file);
		if (file_exists($file)) {
			return $this->error('unset_error');
		}
		return $this->success('unset_success');
	}

	public function set()
	{
		if ($msg = $this->checkFolder()) {
			return $this->error($msg);
		}
		$icon = $_REQUEST['icon'];
		$section = $_REQUEST['section'];
		if (!$icon) {
			return $this->error('set_error_empty_icon_name');
		}
		if (!file_exists($this->path . $icon)) {
			return $this->error('set_error_not_exists_icon');
		}
		if (!$section) {
			return $this->error('set_error_empty_section');
		}
		if (!in_array($section, array('admin', 'client'))) {
			return $this->error('set_error_invalid_section');
		}
		copy($this->path . $icon, $this->path . $section . '.ico');
		if (!file_exists($this->path . $section . '.ico')) {
			return $this->error('set_error');
		}
		return $this->success('set_success');
	}

	public function delete()
	{
		if ($msg = $this->checkFolder()) {
			return $this->error($msg);
		}
		$icon = $_REQUEST['icon'];
		$file_icon = $this->path . $icon;
		if (!$icon) {
			return $this->error('del_no_name');
		}
		if (!file_exists($file_icon)) {
			return $this->error('del_no_file');
		}
		$hash = explode('_source', $icon)[0];
		foreach (array('admin', 'client') as $section) {
			$file = $this->path . $section . '.ico';
			if (!file_exists($file)) {
				continue;
			}
			if ($hash != md5(file_get_contents($file))) {
				continue;
			}
			unlink($file);
			if (file_exists($file)) {
				return $this->error('del_error');
			}
		}
		unlink($file_icon);
		if (file_exists($file_icon)) {
			return $this->error('del_error');
		}
		return $this->success('del_success');
	}

	public function load()
	{
		if ($msg = $this->checkFolder()) {
			return $this->error($msg);
		}
		if (!$_FILES['file']) {
			return $this->error('load_no_file');
		}
		if ($_FILES['file']['type'] != 'image/x-icon') {
			return $this->error('load_no_icon_type');
		}
		if (!$_FILES['file']['size']) {
			return $this->error('load_empty_file');
		}
		$name = md5(file_get_contents($_FILES['file']['tmp_name'])) . '_source.ico';

		if (!move_uploaded_file($_FILES['file']['tmp_name'], $this->path . $name)) {
			return $this->error('load_error');
		}
		return $this->success('load_success');
	}

	protected function checkFolder()
	{
		if (!file_exists($this->path) || !is_dir($this->path)) {
			return 'dir_not_exists';
		}
		if (!is_writable($this->path)) {
			return 'dir_perms_error';
		}
		return '';
	}

	protected function getHash($section)
	{
		$file = $this->path . $section . '.ico';
		if (!file_exists($file)) {
			return '';
		}

		return md5(file_get_contents($file));
	}

	protected function getFiles()
	{
		if (!file_exists($this->path) || !is_dir($this->path)) {
			return array();
		}
		return preg_grep('~\_source.ico$~', scandir($this->path));
	}

	protected function error($msg)
	{
		$this->msg($msg, 'error');
	}

	protected function success($msg)
	{
		$this->msg($msg, 'success');
	}

	protected function msg($msg, $type)
	{
		Cookie::set('ef_msg_type', $type);
		Cookie::set('ef_msg', $msg);
		header('Location: addonmodules.php?module=easy_favicon');
	}
}
