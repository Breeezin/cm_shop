<?php
requireOnceClass('Administration');
class CustomPaymentsAdministration extends Administration {

	function exposeServices() {		
		$prefix ='CustomPayment';
		return	array(
			"$prefix.OrderListSettings"		=>	array('method'	=>	'orderListSettings'),
			"$prefix.MarkPaid"		=>	array('method'	=>	'markPaid'),
			"$prefix.Delete"		=>	array('method'	=>	'delete'),
			"$prefix.ViewFormSubmit"		=>	array('method'	=>	'viewFormSubmit'),						
		);		
	}
	
	
	function viewFormSubmit() {
		require('view_viewFormSubmit.php');
	}
			
	function delete() {	
		require('model_delete.php');
	}		
	function markPaid() {			
		require('model_markPaid.php');
	}
		
	function orderListSettings() {

		$transactionListSettings = array(
			'JoinTable'			=> 'CustomPayments',
			'JoinTablePrefix'	=> 'CuPa',		
			'BreadCrumb'		=> 'Custom Payments',		
			'Options'	=> array(
				'View Detail'	=>	array(					
					'URL'	=>	"javascript:window.open('index.php?act=CustomPayment.ViewFormSubmit".ss_URLEncodedFormat("&CuPaID=[CuPaID]")."&BreadCrumbs=[BreadCrumbs]','littleWindow', 'height=180,width=480,innerHeight=160,innerwidth=460,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
				),
				'Payment Detail'	=>	array(
					'URL'	=>	"javascript:window.open('index.php?act=WebPayAdministration.DisplayPayment".ss_URLEncodedFormat("&tr_id=[tr_id]")."&BreadCrumbs=[BreadCrumbs]','littleWindow', 'height=250,width=480,innerHeight=240,innerwidth=460,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
				),
				
				'Mark Paid'	=>	array(
					'URL'	=>	'index.php?act=CustomPayment.MarkPaid'.ss_URLEncodedFormat("&tr_id=[tr_id]&CuPaID=[CuPaID]&as_id=[CuPaAssetLink]"),
			'Image'=> str_replace('www/htdocs/','',$this->classDirectory).'/Templates/Images/paid.gif',
				),
				'Transaction Response Codes' => array(			
					'URL'	=>	"javascript:window.open('index.php?act=WebPayAdministration.DisplayTransactionResult".ss_URLEncodedFormat("&tr_id=[tr_id]")."&BreadCrumbs=[BreadCrumbs]','littleWindow', 'height=250,width=480,innerHeight=240,innerwidth=460,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",
				),
				'Delete'	=>	array(					
					'URL'	=>	'index.php?act=CustomPayment.Delete'.ss_URLEncodedFormat("&tr_id=[tr_id]&as_id=[CuPaAssetLink]"),				
				),
			)
		);
		return $transactionListSettings;
			
	}

	function __construct() {			
		parent::__construct(array() );
	}

}
?>
