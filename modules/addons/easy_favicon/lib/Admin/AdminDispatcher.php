<?php

namespace WHMCS\Module\Addon\EasyFavicon\Admin;

class AdminDispatcher
{

	/**
	 * @param string $action
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function dispatch($action, $vars)
	{
		if (!$action) {
			$action = 'index';
		}

		$controller = new Controller($vars);

		if (is_callable(array($controller, $action))) {
			return $this->view($controller->$action());
		}

		return '<p>Invalid action requested. Please go back and try again.</p>';
	}

	private function view($__data)
	{
		if (!$__data) {
			$__data = array();
		}
		extract($__data);

		$__path = __DIR__ . '/../../templates/admin/' . $template . '.php';
		if (!file_exists($__path)) {
			return 'Template not found';
		}

		ob_start();
		eval('?>' . file_get_contents($__path));
		$__output = ob_get_contents();
		ob_end_clean();

		return $__output;
	}
}
