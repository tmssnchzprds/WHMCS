<?php

namespace WHMCS\Module\Addon\HpFibuExport\Admin;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Smarty;

/**
 * Class Controller
 * @package WHMCS\Module\Addon\HpFibuExport\Admin
 */
class Controller
{

	/**
	 * Index action.
	 *
	 * @param array $vars Module configuration parameters
	 *
	 * @return string
	 */
	public function index($vars)
	{
		/**
		 * On Form Submit
		 */
		if ($_POST) {
			/**
			 * Convert DateTime jquery field to array
			 */
			$dates = explode(" - ", $_POST["date"]);
			foreach ($dates as $k => $v) {
				$dates[$k] = Carbon::parse($v)->toDateString();
			}
			/**
			 * Make Database Request exclude every Invoice that is a draft.
			 */
			$invoices = Capsule::table('tblinvoices')->whereBetween('date', [
				$dates[0],
				$dates[1],
			])->where('status', '!=', 'Draft');
			$invoices = $invoices->get();
			/**
			 * Create a new .zip Archive
			 */
			$zip = new \ZipArchive();
			if ($zip->open('../modules/addons/hp_fibu_export/tmp/tmp.zip', \ZipArchive::CREATE) !== TRUE) {
				exit('cannot open <tmp.zip>');
			}

			if (!function_exists('pdfInvoice')) {
				require_once ROOTDIR . "/includes/invoicefunctions.php";
			}
			foreach ($invoices as $k => $v) {
				$invoicenum = ($v->invoicenum == "") ? $v->id : $v->invoicenum;
				$zip->addFromString($invoicenum . ".pdf", \pdfInvoice($v->id));
			}
			$zip->close(); // Close .zip Archive
			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=Export vom " . Carbon::today()->format("d.m.Y") . ".zip");
			header("Content-length: " . filesize("../modules/addons/hp_fibu_export/tmp/tmp.zip"));
			header("Pragma: no-cache");
			header("Expires: 0");
			readfile("../modules/addons/hp_fibu_export/tmp/tmp.zip");
			unlink("../modules/addons/hp_fibu_export/tmp/tmp.zip");
			// Fix Exit
			exit();
		}
		/**
		 * Show View
		 */
		$smarty = new Smarty();
		if (isset($invoices)) {
			$smarty->assign('invoices', $invoices);
		}
		$smarty->assign('defaultStart', (new Carbon('first day of -3 months'))->format('d.m.Y'));
		$smarty->assign('defaultEnd', (new Carbon('last day of last month'))->format('d.m.Y'));
		$smarty->caching = false;
		$smarty->compile_dir = $GLOBALS['templates_compiledir'];
		$smarty->display(dirname(__FILE__) . '/../../templates/index.tpl');
	}
}
