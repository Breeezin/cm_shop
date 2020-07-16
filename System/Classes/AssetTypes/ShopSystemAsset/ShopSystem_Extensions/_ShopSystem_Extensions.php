<?php
class ShopSystem_Extensions extends plugin {
	
	function exposeServices() {		
		$prefix = "ShopSystem";
		return array(						
			$prefix.'.Lottery'	=>	array('method'	=>	'lottery'),
			$prefix.'.LotterySelectProduct'	=>	array('method'	=>	'lotterySelectProduct'),
			$prefix.'.ViewAcmeInvoice'	=>	array('method'	=>	'viewAcmeInvoice'),
			$prefix.'.ViewAcmeInvoiceBulk'	=>	array('method'	=>	'viewAcmeInvoiceBulk'),
			$prefix.'.ViewAcmeAbono'	=>	array('method'	=>	'viewAcmeAbono'),
			$prefix.'.AcmePackingList'	=>	array('method'	=>	'acmePackingList'),
			$prefix.'.ExternalPackingList'	=>	array('method'	=>	'externalPackingList'),
			$prefix.'.AcmeStockCheckList'	=>	array('method'	=>	'acmeStockCheckList'),
			$prefix.'.BrokenBoxCheckList'	=>	array('method'	=>	'brokenBoxCheckList'),
			$prefix.'.RecentChangesList'	=>	array('method'	=>	'recentChangesList'),
			$prefix.'.AcmeDUAPreparationList'	=>	array('method'	=>	'acmeDUAPreparationList'),
			$prefix.'.AcmeDUAPreparationListExport'	=>	array('method'	=>	'acmeDUAPreparationListExport'),
			$prefix.'.AcmeSupplierOrdersReport'	=>	array('method'	=>	'acmeSupplierOrdersReport'),
			$prefix.'.AcmeInvoiceReport'	=>	array('method'	=>	'acmeInvoiceReport'),
			$prefix.'.AcmeCEDOPReport'	=>	array('method'	=>	'acmeCEDOPReport'),
			$prefix.'.AcmeGenerateSupplierOrderSheet'	=>	array('method'	=>	'acmeGenerateSupplierOrderSheet'),
			$prefix.'.AcmeGenerateExternalOrderSheet'	=>	array('method'	=>	'acmeGenerateExternalOrderSheet'),
			$prefix.'.AcmeGenerateStockNews'	=>	array('method'	=>	'acmeGenerateStockNews'),
			$prefix.'.ViewOrder'	=>	array('method'	=>	'viewOrder'),
			$prefix.'.StockOrderList'	=>	array('method'	=>	'stockOrderList'),
			$prefix.'.ExternalOrderList'	=>	array('method'	=>	'externalOrderList'),
			$prefix.'.AddOrderNote'	=>	array('method'	=>	'addOrderNote'),
			$prefix.'.AddIssueEntry'	=>	array('method'	=>	'addIssueEntry'),
			$prefix.'.AcmeComplete' => array('method' => 'complete'),
			$prefix.'.AcmeAutoNewsletterPreview' => array('method' => 'autoNewsletterPreview'),
			$prefix.'.AcmeAutoNewsletterSend' => array('method' => 'autoNewsletterSend'),
			$prefix.'.AcmePointsDisplay' => array('method' => 'pointsDisplay'),
			$prefix.'.AcmePointsDisplayOrder' => array('method' => 'pointsDisplayOrder'),
			$prefix.'.AcmeAutoDashboard' => array('method' => 'autoDashboard'),
			$prefix.'.AcmeMinStock' => array('method' => 'minStock'),
			$prefix.'.AcmeCalculateOrderProfit' => array('method' => 'calculateOrderProfit'),
			$prefix.'.AcmeEdit' => array('method' => 'edit'),
			$prefix.'.AcmeTextCreateLotteryOrder' => array('method' => 'testCreateLotteryOrder'),
			$prefix.'.AcmeShippingReport' => array('method' => 'shippingReport'),
			$prefix.'.AcmeBank' => array('method' => 'bank'),
			$prefix.'.AcmeDailyReport' => array('method' => 'dailyReport'),
			$prefix.'.AcmeDashBoardEmail'	=>	array('method' => 'dashboardEmail'),
			$prefix.'.AcmeSpecialsTextEmail'	=>	array('method' => 'specialsTextEmail'),
			$prefix.'.AcmeSendLotteryWinnerEmail'	=>	array('method' => 'sendLotteryWinnerEmail'),
			$prefix.'.ExtCustomerReport'	=>	array('method' => 'customerReport'),
			$prefix.'.AcmeSendGiftEmail'	=>	array('method' => 'sendGiftEmail'),
			$prefix.'.AcmeAccountingReport'	=>	array('method'	=>	'acmeAccountingReport'),
			$prefix.'.AcmeCreateAbono'	=>	array('method'	=>	'acmeCreateAbono'),
			$prefix.'.BulkInvoice'	=>	array('method'	=>	'bulkInvoice'),
			$prefix.'.SwapVendor'	=>	array('method'	=>	'swapVendor'),
			$prefix.'.QueryGateway'	=>	array('method'	=>	'queryGateway'),
			$prefix.'.FixSupplierInvoices'	=>	array('method'	=>	'queryFixSupplierInvoices'),
		);		
	}

	function queryFixSupplierInvoices() {
		require('query_fixSupplierInvoices.php');
	}

	function queryGateway() {
		require('query_queryGateway.php');	
		require('model_queryGateway.php');	
		require('view_queryGateway.php');	
	}	

	function swapVendor() {
		require( 'model_swapVendor.php');
	}

	function bulkInvoice() {
		require( 'query_bulkInvoice.php');
	}

	function acmeCreateAbono() {
		require('model_acmeCreateAbono.php');	
	}
	
	function customerReport() {
		require('query_customerReport.php');	
	}
	
	function sendGiftEmail() {
		require('model_sendGiftEmail.php');	
	}
	
	function sendLotteryWinnerEmail() {
		require('model_sendLotteryWinnerEmail.php');	
	}
	
	function dashboardEmail() {
		require('model_dashboardEmail.php');	
	}
	
	function specialsTextEmail() {
		require('query_specialsTextEmail.php');	
	}
	
	function bank() {
		require('model_bank.php');	
		require('view_bank.php');	
	}
	

	function dailyReport() {
		require('query_dailyReport.php');	
		require('model_dailyReport.php');	
		require('view_dailyReport.php');	
	}	
	
	function shippingReport() {
		require('query_shippingReport.php');	
		require('model_shippingReport.php');	
		require('view_shippingReport.php');	
	}
	
	function testCreateLotteryOrder() {
		require('model_testcreatelotteryorder.php');	
		die('test');
	}
	
	function edit() {
		require('query_edit.php');	
		require('model_edit.php');	
		require('view_edit.php');	
	}
	
	function calculateOrderProfit() {
		require('model_calculateOrderProfit.php');	
	}
	
	function lottery() {
		require('query_lottery.php');
		require('model_lottery.php');
		require('view_lottery.php');	
	}
	
	function lotterySelectProduct() {
		require('query_lotterySelectProduct.php');
		require('model_lotterySelectProduct.php');
		require('view_lotterySelectProduct.php');	
	}	

	function autoDashBoard() {
		require('query_autoDashboard.php');
		require('view_autoDashboard.php');
	}

	function minStock() {
		require('query_minStock.php');
		require('view_minStock.php');
	}

	function autoNewsletterPreview() {
		require('query_autoNewsletterPreview.php');
	}

	function autoNewsletterSend() {
		require('query_autoNewsletterSend.php');
	}

	function complete() {
		$this->display->title = 'Complete';
		print('You may now close this window.');	
	}
	function pointsDisplayOrder() {
		require('query_pointsDisplayOrder.php');
		require('view_pointsDisplayOrder.php');
	}		
	function pointsDisplay() {
		require('query_pointsDisplay.php');
		require('view_pointsDisplay.php');
	}	

	function recentChangesList() {
		require('query_recentChangesList.php');
		require('view_recentChangesList.php');
	}	

	function acmeDUAPreparationList() {
		require('query_acmeDUAPreparationList.php');
		require('view_acmeDUAPreparationList.php');
	}	

	function acmeDUAPreparationListExport() {
		require('query_acmeDUAPreparationList.php');
		require('view_acmeDUAPreparationListExport.php');
	}	
	function acmePackingList() {
		require('query_acmePackingList.php');
		require('view_acmePackingList.php');
	}	
	function externalPackingList() {
		require('query_externalPackingList.php');
		require('view_externalPackingList.php');
	}	
	function acmeStockCheckList() {
		require('query_acmeStockCheckList.php');
		require('view_acmeStockCheckList.php');
	}	
	function brokenBoxCheckList() {
		require('query_brokenBoxCheckList.php');
		require('view_brokenBoxCheckList.php');
	}	
	function acmeSupplierOrdersReport() {
		require('query_acmeSupplierOrdersReport.php');
		require('view_acmeSupplierOrdersReport.php');
	}	
	function acmeInvoiceReport() {
		require('query_acmeInvoiceReport.php');
		require('view_acmeInvoiceReport.php');
	}
	function acmeCEDOPReport() {
		require('query_acmeCEDOPReport.php');
		require('view_acmeCEDOPReport.php');
	}
	function acmeGenerateSupplierOrderSheet() {
		if( ss_adminCapability( ADMIN_PACKING_ROOM ) )
			require('model_acmeGenerateSupplierOrderSheet.php');
	}
	function acmeGenerateExternalOrderSheet() {
		if( ss_adminCapability( ADMIN_PACKING_ROOM ) )
			require('model_acmeGenerateExternalOrderSheet.php');
	}
	function acmeGenerateStockNews() {
		require('query_generateStockNews.php');
	}
	function viewAcmeInvoiceBulk() {
		require('query_viewAcmeInvoiceBulk.php');
		require('view_viewAcmeInvoiceBulk.php');
	}
	function viewAcmeInvoice() {
		require('query_viewAcmeInvoice.php');
		require('view_viewAcmeInvoice.php');
	}
	function viewAcmeAbono() {
		require('query_viewAcmeAbono.php');
		require('view_viewAcmeAbono.php');
	}	
	function viewOrder() {
		if( ss_adminCapability( ADMIN_VIEW_ORDER ) )
		{
			require('query_viewOrder.php');
			require('model_viewOrder.php');
			require('view_viewOrder.php');
		}
	}
	function externalOrderList() {
		require('query_externalOrderList.php');	
		require('view_externalOrderList.php');	
	}
	function stockOrderList() {
		require('query_stockOrderList.php');	
		require('view_stockOrderList.php');	
	}

	function addOrderNote() {
		require('model_addOrderNote.php');	
	}
	
	function addIssueEntry() {
		require('model_addIssueEntry.php');	
	}
	

	function acmeAccountingReport() {
		require('query_acmeAccountingReport.php');
		require('view_acmeAccountingReport.php');
	}		
	
	function inputFilter() {
		parent::inputFilter();
		$this->param('BreadCrumbs','Administration');
		$this->display->layout = 'Administration';

		if( array_key_exists('act',$this->ATTRIBUTES)
		  && ( $this->ATTRIBUTES['act'] == 'ShopSystem.AcmeCalculateOrderProfit' ) )
		{
			ss_log_message( $_SERVER['REMOTE_ADDR']." calling calc profit" );
			if($_SERVER['REMOTE_ADDR'] == '67.231.16.54')
				return;
		}

		// Must be able to Administer something to access these Actions
		if (array_key_exists('act',$this->ATTRIBUTES))
			$result = new Request('Security.Authenticate',array( 'Permission'	=>	'CanAdministerAtLeastOneAsset',));

/*
		if (array_key_exists('act',$this->ATTRIBUTES)) {
			if ( $this->ATTRIBUTES['act'] != 'ShopSystem.AcmeAutoNewsletterPreview'
			 and $this->ATTRIBUTES['act'] != 'ShopSystem.AcmeAutoNewsletterSend'
			 and $this->ATTRIBUTES['act'] != 'ShopSystem.AcmeAutoNewsletterSendBatch'
			 and $this->ATTRIBUTES['act'] != 'ShopSystem.AcmeAutoNewsletterSendSh'
			 and $this->ATTRIBUTES['act'] != 'ShopSystem.AcmeSendGiftEmail') {
				$result = new Request('Security.Authenticate',array(
						'Permission'	=>	'CanAdministerAtLeastOneAsset',
				));
			}
		}
*/
		
	}	
	
	function __construct() {
		parent::__construct();
//		$this->Plugin();
		//$this->Administration(array());
		/*
		$assetID = null;
		
		if (!strlen($this->assetLink)) {
			if (array_key_exists("as_id", $_REQUEST)) {
				$assetID = $_REQUEST['as_id'];			
			} else if (array_key_exists("assetLink", $_REQUEST)) {
				$assetID = $_REQUEST['assetLink'];			
			}			
		}
		
		$this->Administration(array(
			'prefix'					=>	'ShopSystem',
			'singular'					=>	'Order',
			'plural'					=>	'Orders',
			'tableName'					=>	'shopsystem_orders',
			'tablePrimaryKey'			=>	'or_id',
			'tableDisplayFields'		=>	array('or_id', 'or_recorded', 'or_purchaser_firstname', 'or_purchaser_lastname','or_total'),
			'tableDisplayFieldTitles'	=>	array('ID', 'Ordered', 'First Name', 'Last Name', 'Total'),
			'tableOrderBy'				=>	array('or_recorded' => 'Ordered', 'or_purchaser_firstname, or_purchaser_lastname' => 'Name'),
			'tableAssetLink'			=>	'or_as_id',
			'assetLink'					=>	$assetID,
		));*/
	}

}
?>
