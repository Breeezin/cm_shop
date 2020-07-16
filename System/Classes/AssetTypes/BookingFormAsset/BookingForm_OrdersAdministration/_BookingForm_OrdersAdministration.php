<?php
requireOnceClass('Administration');
class BookingForm_OrdersAdministration extends Administration {
	
	function exposeServices() {		
		$prefix = "BookingForm";
		return array_merge(Administration::exposeServicesUsing($prefix),array(						
			$prefix.'.OrderListSettings'	=>	array('method'	=>	'orderListSettings'),
			$prefix.'.ViewBooking'	=>	array('method'	=>	'viewBooking'),
			$prefix.'.SendEmail'	=>	array('method'	=>	'sendEmail'),
			$prefix.'.GetPaymentLink'	=>	array('method'	=>	'getPaymentLink'),
			$prefix.'.EnterAmount'	=>	array('method'	=>	'enterAmount'),
			$prefix.'.MarkPaid'	=>	array('method'	=>	'markPaid'),
			$prefix.'.Delete'	=>	array('method'	=>	'delete'),
		));		
	}
	
	function delete() {
		require('model_delete.php');
	}
	function sendEmail() {
		require('query_sendEmail.php');
		require('model_sendEmail.php');
		require('view_sendEmail.php');
	}	
	function markPaid() {
		require('model_markPaid.php');
	}
	function viewBooking() {
		require('query_viewBooking.php');
		require('model_viewBooking.php');
		require('view_viewBooking.php');
	}
	function getPaymentLink() {
		require('query_getPaymentLink.php');
		require('view_getPaymentLink.php');
	}
		
	function enterAmount() {
		require('query_enterAmount.php');	
		require('model_enterAmount.php');	
		require('view_enterAmount.php');	
	}
	
	function orderListSettings() {
	 	$prefix = "BookingForm";	
		$transactionListSettings = array(
			'JoinTable'			=> 'booking_form_bookings',
			'JoinTablePrefix'	=> 'Bo',		
			'BreadCrumb'		=> 'Orders',
			'tr_completed'		=>	false,	
			'DisplayFields'	=>	array('bo_date'),
			'DisplayFieldTitles'	=>	array('Received'),
			'OrderBy'			=>	array(				
										array('name' => 'Paid', 'field' => 'bo_paid'),
									),	
			'FilterBy'			=>	array(
										array('name' => 'Paid', 'filter' => 'bo_paid IS NOT NULL'),
										array('name' => 'Not Paid', 'filter' => 'bo_paid IS NULL'),
								),
			'Options'	=> array(
				'View Booking Details'	=>	array(					
					'URL'	=>	"javascript:res=window.open('index.php?act=$prefix.ViewBooking".ss_URLEncodedFormat("&bo_id=[bo_id]&tr_id=[tr_id]&as_id=[bo_as_id]")."&BreadCrumbs=[BreadCrumbs]','bookingDetails', 'height=480,width=580,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');res.focus();void(0);",				
				),
				'Payment Detail'	=>	array(
					'URL'	=>	"javascript:res=window.open('index.php?act=WebPayAdministration.DisplayPayment".ss_URLEncodedFormat("&tr_id=[tr_id]")."&BreadCrumbs=[BreadCrumbs]','paymentDetails', 'height=250,width=480,innerHeight=240,innerwidth=460,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');res.focus();void(0);",				
				),
				'Mark Paid'	=>	array(			
					'URL'	=>	'index.php?act='.$prefix.'.MarkPaid'.ss_URLEncodedFormat("&bo_id=[bo_id]&tr_id=[tr_id]&BackURL=[BackURL]"),				
					'FilterIndex'=>	"bo_paid",				
					'FilterType'=>	"strlen",									
					'Image'=>	$this->classDirectory.'/Templates/Images/paid.gif',				
				),
				'Delete'	=>	array(					
					'URL'	=>	'index.php?act='.$prefix.'.Delete'.ss_URLEncodedFormat("&bo_id=[bo_id]&tr_id=[tr_id]"),				
				),
			),
			
		);
/*	
				'Enter Amount'	=>	array(			
					'URL'	=>	"javascript:window.open('index.php?act=$prefix.EnterAmount".ss_URLEncodedFormat("&bo_id=[bo_id]&tr_id=[tr_id]&as_id=[bo_as_id]")."&BreadCrumbs=[BreadCrumbs]','littleWindow', 'height=480,width=580,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
					'FilterIndex'=>	"tr_charge_total",
					'FilterType'=>	"strlen",							
				),
			'Get Secure Link'	=>	array(			
					'URL'	=>	"javascript:window.open('index.php?act=$prefix.GetPaymentLink".ss_URLEncodedFormat("&bo_id=[bo_id]&tr_id=[tr_id]&as_id=[bo_as_id]")."&BreadCrumbs=[BreadCrumbs]','littleWindow', 'height=480,width=580,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
					'FilterIndex'=>	"bo_paid",				
					'FilterType'=>	"strlen",									
				),*/
		
		return $transactionListSettings;
	}
	

	function __construct() {			
		parent::__construct(array() );
	}
	
		/*
	function BookingForm_OrdersAdministration() {
		$this->Administration(array());
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
		));
	} */

}
?>
