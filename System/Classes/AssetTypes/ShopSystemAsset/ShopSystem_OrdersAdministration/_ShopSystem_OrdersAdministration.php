<?php
requireOnceClass('Administration');
class ShopSystem_OrdersAdministration extends Administration {
	
	function exposeServices() {		
		$prefix = "ShopSystem";
		return array_merge(Administration::exposeServicesUsing($prefix),array(						
			$prefix.'.OrderListSettings'	=>	array('method'	=>	'orderListSettings'),
			$prefix.'.ViewInvoice'	=>	array('method'	=>	'viewInvoice'),
			$prefix.'.MarkPaid'	=>	array('method'	=>	'markPaid'),			
			$prefix.'.MarkPaidNotShipped'	=>	array('method'	=>	'markPaidNotShipped'),
			$prefix.'.UnmarkPaid'	=>	array('method'	=>	'unmarkPaid'),
			$prefix.'.UnmarkPaidNotShipped'	=>	array('method'	=>	'unmarkPaidNotShipped'),
			$prefix.'.MarkShipped'	=>	array('method'	=>	'markShipped'),
			$prefix.'.MarkInsuredTraced'	=>	array('method'	=>	'markInsuredTraced'),
			$prefix.'.Delete'	=>	array('method'	=>	'delete'),
			$prefix.'.SendEmail'	=>	array('method'	=>	'sendEmail'),	
			$prefix.'.SendCustomEmail'	=>	array('method'	=>	'sendCustomEmail'),
			$prefix.'.unMarkAsProxy'	=>	array('method'	=>	'unMarkAsProxy'),	
			$prefix.'.markAsProxy'	=>	array('method'	=>	'markAsProxy'),	
			$prefix.'.killEmail'	=>	array('method'	=>	'killEmail'),	
			$prefix.'.unkillEmail'	=>	array('method'	=>	'unkillEmail'),	
			$prefix.'.MarkProperty'	=>	array('method'	=>	'markProperty'),
			$prefix.'.UnmarkProperty'	=>	array('method'	=>	'unmarkProperty'),
			$prefix.'.TransformAddressChecked'	=>	array('method'	=>	'transformAddressChecked'),
			$prefix.'.TransformOrderAvailability'	=>	array('method'	=>	'transformOrderAvailability'),
			$prefix.'.TransformChargeList'	=>	array('method'	=>	'transformChargeList'),
			$prefix.'.TransformManualCharge'	=>	array('method'	=>	'transformManualCharge'),
			$prefix.'.TransformShippingDate'	=>	array('method'	=>	'transformShippingDate'),
			$prefix.'.MassSendCustomEmail'	=>	array('method'	=>	'massSendCustomEmail'),
		));		
	}
	
	function markproperty() {
		require('model_markProperty.php');	
	}
	function unmarkproperty() {
		require('model_unmarkProperty.php');	
	}
	
	function delete() {
		require('model_delete.php');
	}
	function markShipped() {
		require('model_markShipped.php');
	}
	function markInsuredTraced() {
		require('model_markInsuredTraced.php');
		require('view_markInsuredTraced.php');
	}
	function massSendCustomEmail() {
		require('model_massSendCustomEmail.php');
	}
	function transformAddressChecked() {
		require('model_transformAddressChecked.php');
	}
	function transformOrderAvailability() {
		require('model_transformOrderAvailability.php');
	}
	function transformChargeList() {
		require('model_transformChargeList.php');
	}
	function transformManualCharge() {
		require('model_transformManualCharge.php');
	}
	function transformShippingDate() {
		require('model_transformShippingDate.php');
	}
	function markPaid() {
		$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
		$customFolder = $rootFolder.'Custom/Classes/ShopSystem_OrdersAdministration';		
		$name = 'model_markPaid.php';
		if (file_exists($customFolder.'/'.$name)) {			
			include($customFolder."/".$name);
		} else {
			require('model_markPaid.php');
		}
	}	
	function markPaidNotShipped() {
		require('model_markPaidNotShipped.php');
	}
	function unmarkPaid() {
		require('model_removePaid.php');
	}
	function unmarkPaidNotShipped() {
		require('model_removePaidNotShipped.php');
	}
	function viewInvoice() {
		require('query_viewInvoice.php');
		require('view_viewInvoice.php');
	}

	function killEmail() {
		$this->param('or_id');
		$this->param('BackURL');

		$Q_Order = getRow("
				SELECT us_email, or_purchaser_email FROM shopsystem_orders join users on or_us_id = us_id
				WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");

		//query( "insert into unusable_emails (email_address) values ('".$Q_Order['or_purchaser_email']."')");
		query( "insert into unusable_emails (email_address) values ('".$Q_Order['us_email']."')");
		header( 'Location: '.$this->ATTRIBUTES['BackURL'] );
	}

	function unkillEmail() {
		$this->param('or_id');
		$this->param('BackURL');

		$Q_Order = getRow("
				SELECT us_email, or_purchaser_email FROM shopsystem_orders join users on or_us_id = us_id
				WHERE or_id = ".safe($this->ATTRIBUTES['or_id'])."
			");
		query( "delete from  unusable_emails where email_address like '".$Q_Order['us_email']."'");
		header( 'Location: '.$this->ATTRIBUTES['BackURL'] );
	}

	function unMarkAsProxy() {
		$this->param('tr_id');
		$this->param('BackURL');

		$Q_Order = getRow("
				SELECT tr_ip_address FROM transactions
				WHERE tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
			");

		if( strlen( $Q_Order['tr_ip_address'] ) )
			query( "delete from proxy_addresses where ip_address = '".$Q_Order['tr_ip_address']."'");
		header( 'Location: '.$this->ATTRIBUTES['BackURL'] );
	}

	function markAsProxy() {
		$this->param('tr_id');
		$this->param('BackURL');

		$Q_Order = getRow("
				SELECT tr_ip_address FROM transactions
				WHERE tr_id = ".safe($this->ATTRIBUTES['tr_id'])."
			");

		if( strlen( $Q_Order['tr_ip_address'] ) )
			query( "insert into proxy_addresses (ip_address) values ('".$Q_Order['tr_ip_address']."')");
		header( 'Location: '.$this->ATTRIBUTES['BackURL'] );
	}

	function sendEmail() {
		require('query_sendEmail.php');	
		require('model_sendEmail.php');	
		require('view_sendEmail.php');	
	}

	function sendCustomEmail() {
		require('query_sendCustomEmail.php');	
		require('model_sendCustomEmail.php');	
		require('view_sendCustomEmail.php');	
	}	

	function orderListSettings() {
	 	$prefix = "ShopSystem";	
	 	
	 	$options = array();
	 	
		if (ss_optionExists('Shop Advanced Ordering')) {
			if( ss_adminCapability( ADMIN_VIEW_ORDER ) )
				$options['View Order']	=	array(					
					'URL'	=>	"javascript:window.open('index.php?act=$prefix.ViewOrder".ss_URLEncodedFormat("&or_id=[or_id]&tr_id=[tr_id]&as_id=[or_as_id]")."&BreadCrumbs=[BreadCrumbs]','_blank', 'height=800,width=1000,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
				);

/*			$options['Send Email']	=	array(			
					'URL'	=>	"index.php?act=$prefix.SendEmail".ss_URLEncodedFormat("&or_id=[or_id]&tr_id=[tr_id]&as_id=[or_as_id]&BackURL=[BackURL]")."&BreadCrumbs=[BreadCrumbs]",				
			);
*/
			$options['Mark Not New'] = array(			
				'FilterIndex'=>	"or_not_new",				
				'FilterType'=>	"strlen",									
				'Image'=>	$this->classDirectory.'/Templates/Images/new.gif',				
			);

/*			if( ss_adminCapability( ADMIN_ORDER_STATUS ) )	*/
			$options['Mark Invoiced'] = array(			
				'FilterIndex'=>	"or_invoiced",				
				'FilterType'=>	"not strlen",									
				'Image'=>	$this->classDirectory.'/Templates/Images/invoiced.gif',				
			);

			$options['Mark Paid/Not Shipped'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkPaidNotShipped'.ss_URLEncodedFormat("&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_paid_not_shipped",				
				'FilterType'=>	"strlen",									
				'Image'=>	$this->classDirectory.'/Templates/Images/notshipped.gif',				
			);
			$options['Mark Paid/Not Shipped+Email'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkPaidNotShipped'.ss_URLEncodedFormat("&or_id=[or_id]&SendEmail=1&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_paid_not_shipped",				
				'FilterType'=>	"strlen",									
			);
			$options['Remove Paid/Not Shipped'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkPaidNotShipped'.ss_URLEncodedFormat("&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_paid_not_shipped",				
				'FilterType'=>	"not strlen",									
			);

			$options['Mark Card Denied'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkProperty'.ss_URLEncodedFormat("&Property=CardDenied&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_card_denied",				
				'FilterType'=>	"strlen",									
				'Image'=>	$this->classDirectory.'/Templates/Images/carddenied.gif',				
			);
			$options['Remove Card Denied'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkProperty'.ss_URLEncodedFormat("&Property=CardDenied&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_card_denied",				
				'FilterType'=>	"not strlen",									
			);

			$options['Mark Cancelled'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkProperty'.ss_URLEncodedFormat("&Property=Cancelled&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_cancelled",				
				'FilterType'=>	"strlen",									
				'Image'=>	$this->classDirectory.'/Templates/Images/cancelled.gif',				
			);
			$options['Remove Cancelled'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkProperty'.ss_URLEncodedFormat("&Property=Cancelled&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_cancelled",				
				'FilterType'=>	"not strlen",									
			);

			$options['Mark Reshipment'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkProperty'.ss_URLEncodedFormat("&Property=Reshipment&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_reshipment",				
				'FilterType'=>	"strlen",									
				'Image'=>	$this->classDirectory.'/Templates/Images/reshipment.gif',				
			);
			$options['Remove Reshipment'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkProperty'.ss_URLEncodedFormat("&Property=Reshipment&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_reshipment",				
				'FilterType'=>	"not strlen",									
			);

			if( ss_adminCapability( ADMIN_EDIT_ORDER ) )
				$options['Edit Charge Details']	=	array(					
					'URL'	=>	"javascript:window.open('index.php?act=$prefix.AcmeEdit".ss_URLEncodedFormat("&or_id=[or_id]&tr_id=[tr_id]&as_id=[or_as_id]")."&BreadCrumbs=[BreadCrumbs]','editChargeTotal', 'height=480,width=580,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
				);

			$options['Mark Standby'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkProperty'.ss_URLEncodedFormat("&Property=Standby&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_standby",				
				'FilterType'=>	"strlen",									
				'Image'=>	$this->classDirectory.'/Templates/Images/standby.gif',				
			);
			$options['Remove Standby'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkProperty'.ss_URLEncodedFormat("&Property=Standby&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_standby",				
				'FilterType'=>	"not strlen",									
			);
/*					'FilterIndex'=>	"OrNotified",				
					'FilterType'=>	"strlen",
					'Image'=>	ss_absolutePathToURL($this->classDirectory).'/Templates/Images/notified.gif',				*/

			$options['Mark Paid'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkPaid'.ss_URLEncodedFormat("&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_paid",				
				'FilterType'=>	"strlen",									
				'Image'=>	$this->classDirectory.'/Templates/Images/paid.gif',				
			);
			$options['Remove Paid'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkPaid'.ss_URLEncodedFormat("&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_paid",				
				'FilterType'=>	"not strlen",									
			);

			$options['Out Of Stock'] = array(			
				'FilterIndex'=>	"or_out_of_stock",				
				'FilterType'=>	"not strlen",				
				'Image'=>	$this->classDirectory.'/Templates/Images/outofstock.gif',				
			);
			$options['Remove Out Of Stock'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkProperty'.ss_URLEncodedFormat("&Property=OutOfStock&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_out_of_stock",
				'FilterType'=>	"not strlen",									
			);

			$options['Mark Shipped'] = array(			
					'FilterIndex'=>	"or_shipped",				
					'FilterType'=>	"not strlen",				
					'Image'=>	$this->classDirectory.'/Templates/Images/shipped.gif',				
				);
			$options['Remove Shipped'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkProperty'.ss_URLEncodedFormat("&Property=Shipped&or_id=[or_id]&BackURL=[BackURL]"),				
				'FilterIndex'=>	"or_shipped",				
				'FilterType'=>	"not strlen",				
			);

			$options['IP/Computer Report'] = array(			
				'URL'	=>	"javascript:window.open('index.php?act=ShopSystem.ExtCustomerReport&or_id=[or_id]','customerreport', 'height=800,width=1000,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",		
			);

			$options['Customer Report'] = array(			
				'URL'	=>	"javascript:window.open('index.php?act=ShopSystem.ExtCustomerReport&or_id=[or_id]&customer=1','customerreport', 'height=800,width=1000,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",		
			);

/*
			$options['Customer Points'] = array(			
				'URL'	=>	"javascript:window.open('index.php?act=ShopSystem.AcmePointsDisplay&or_id=[or_id]','customerpoints', 'height=480,width=580,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",		
			);
			$options['Order Points'] = array(			
				'URL'	=>	"javascript:window.open('index.php?act=ShopSystem.AcmePointsDisplayOrder&or_id=[or_id]','orderpoints', 'height=480,width=580,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",		
			);

			$options['Lottery'] = array(			
				'FilterIndex'=>	"or_lottery",				
				'FilterType'=>	"not strlen",									
				'Image'=>	$this->classDirectory.'/Templates/Images/lottery.gif',				
			);
*/

			$options['ChargeList'] = array(			
				'FilterIndex'=>	"or_charge_list",				
				'FilterType'=>	"",									
				'Image'=>	$this->classDirectory.'/Templates/Images/chargelist.gif',				
			);

			
		/*
		else {
			$options['View Invoice'] = array(					
				'URL'	=>	"javascript:window.open('index.php?act=$prefix.ViewInvoice".ss_URLEncodedFormat("&or_id=[or_id]&tr_id=[tr_id]&as_id=[or_as_id]")."&BreadCrumbs=[BreadCrumbs]','littleWindow', 'height=480,width=580,innerHeight=460,innerwidth=560,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
			);
		}
		*/
		
			// Standard options
			$options['Payment Detail'] = array(
				'URL'	=>	"javascript:window.open('index.php?act=WebPayAdministration.DisplayPayment".ss_URLEncodedFormat("&tr_id=[tr_id]")."&BreadCrumbs=[BreadCrumbs]','littleWindow', 'height=250,width=480,innerHeight=240,innerwidth=460,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
			);

			$options['Mark Tracked'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkProperty'.ss_URLEncodedFormat("&Property=TrackedAndTraced&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_tracked_and_traced",				
				'FilterType'=>	"strlen",
				'Image'=>	$this->classDirectory.'/Templates/Images/trackedandtraced.gif',				
			);
			$options['Remove Tracked'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkProperty'.ss_URLEncodedFormat("&Property=TrackedAndTraced&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_tracked_and_traced",				
				'FilterType'=>	"not strlen",
			);

			$options['Mark Actioned'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkProperty'.ss_URLEncodedFormat("&Property=Actioned&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_actioned",				
				'FilterType'=>	"strlen",
				'Image'=>	$this->classDirectory.'/Templates/Images/actioned.gif',				
			);
			$options['Remove Actioned'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.UnmarkProperty'.ss_URLEncodedFormat("&Property=Actioned&or_id=[or_id]&BackURL=[BackURL]&tr_id=[tr_id]"),				
				'FilterIndex'=>	"or_actioned",
				'FilterType'=>	"not strlen",									
			);

	/*		not helpful
			$options['Mark Returned'] = array(			
				'URL'	=>	'index.php?act='.$prefix.'.MarkProperty'.ss_URLEncodedFormat("&Property=Returned&or_id=[or_id]&BackURL=[BackURL]"),				
				'FilterIndex'=>	"or_shipped",
				'FilterType'=>	"not strlen",
			);
	*/

			$options['Transaction Response Codes'] = array(			
				'URL'	=>	"javascript:window.open('index.php?act=WebPayAdministration.DisplayTransactionResult".ss_URLEncodedFormat("&tr_id=[tr_id]")."&BreadCrumbs=[BreadCrumbs]','littleWindow', 'height=250,width=480,innerHeight=240,innerwidth=460,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
			);
			$options['Delete'] = array(					
				'URL'	=>	'index.php?act='.$prefix.'.Delete'.ss_URLEncodedFormat("&or_id=[or_id]&tr_id=[tr_id]"),				
			);

			if( ss_adminCapability( ADMIN_PRODUCT_PRICING ) )
				$options['Show Profit Calculations'] = array(			
						'URL'	=>	"javascript:window.open('index.php?act=$prefix.AcmeCalculateOrderProfit".ss_URLEncodedFormat("&or_id=[or_id]")."','littleWindow', 'height=250,width=480,innerHeight=240,innerwidth=460,location=no,menuBar=yes,personalbar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');void(0);",				
				);	
		}
		$transactionListSettings = array(
			'JoinTable'			=> 'shopsystem_orders',
			'JoinTablePrefix'	=> 'or',		
			'BreadCrumb'		=> 'Orders',	
			'OrderBy'			=>	array(				
										array('name' => 'Paid', 'field' => 'or_paid'),
										array('name' => 'Paid Not Shipped', 'field' => 'or_paid_not_shipped'),
										array('name' => 'Shipped', 'field' => 'or_shipped'),
										array('name' => 'Insured and Traced', 'field' => 'or_tracked_and_traced'),
									),	
			'FilterBy'			=>	array(
										array('name' => 'Paid', 'filter' => 'or_paid IS NOT NULL'),
										array('name' => 'Not Paid', 'filter' => 'or_paid IS NULL'),
										array('name' => 'Shipped', 'filter' => 'or_shipped IS NOT NULL'),
										array('name' => 'Not Shipped', 'filter' => 'or_shipped IS NULL'),
								),
			'Options'	=>	$options,
			'MultiSiteFilter'	=>	'or_site_folder',
		);
		
		if (ss_optionExists('Shop Advanced Ordering')) {
			/*$transactionListSettings['OrderBy']			=	array(				
										array('name' => 'Paid', 'field' => 'or_paid'),
										array('name' => 'Shipped', 'field' => 'or_shipped'),
										array('name' => 'Insured and Traced', 'field' => 'or_tracked_and_traced'),
										array('name' => 'Invoiced', 'field' => 'or_invoiced'),
									);	
			$transactionListSettings['FilterBy'] =	array(
										array('name' => 'Paid', 'filter' => 'or_paid IS NOT NULL'),
										array('name' => 'Not Paid', 'filter' => 'or_paid IS NULL'),
										array('name' => 'Shipped', 'filter' => 'or_shipped IS NOT NULL'),
										array('name' => 'Not Shipped', 'filter' => 'or_shipped IS NULL'),
										array('name' => 'Invoiced', 'filter' => 'or_invoiced IS NOT NULL'),
										array('name' => 'Not Invoiced', 'filter' => 'or_invoiced IS NULL'),
			);*/
			array_push($transactionListSettings['OrderBy'],array('name' => 'Invoiced', 'field' => 'or_invoiced'));

			$transactionListSettings['FilterBy']	 =		array(
										array('name' => 'Unprocessed', 'filter' => 'or_paid IS NULL and or_shipped IS NULL and or_cancelled IS NULL and or_standby IS NULL and or_paid_not_shipped IS NULL and or_card_denied IS NULL'),
										array('name' => 'Paid', 'filter' => 'or_paid IS NOT NULL and tr_total > 0'),
										array('name' => 'Not Paid', 'filter' => 'or_paid IS NULL'),
										array('name' => 'Shipped', 'filter' => 'or_shipped IS NOT NULL'),
										array('name' => 'Not Shipped', 'filter' => 'or_shipped IS NULL'),
										array('name' => 'Invoiced', 'filter' => 'or_invoiced IS NOT NULL'),
										array('name' => 'Not Invoiced', 'filter' => 'or_invoiced IS NULL'),
										array('name' => 'Paid/Not Shipped', 'filter' => 'or_paid_not_shipped IS NOT NULL'),
										array('name' => 'Cancelled', 'filter' => 'or_cancelled IS NOT NULL'),
										array('name' => 'Card Denied', 'filter' => 'or_card_denied IS NOT NULL'),
										array('name' => 'Standby', 'filter' => 'or_standby IS NOT NULL'),
										array('name' => 'New', 'filter' => 'or_not_new IS NULL'),
										array('name' => 'Reshipment', 'filter' => 'or_reshipment IS NOT NULL'),
										array('name' => 'Refund', 'filter' => 'or_id in (select rfd_or_id from shopsystem_refunds)'),
										array('name' => 'Out of Stock', 'filter' => 'or_out_of_stock IS NOT NULL'),
										array('name' => 'Bank Transferred', 'filter' => 'bt_address IS NOT NULL'),
										array('name' => 'Slipped through the cracks', 'filter' => '((or_not_new IS NULL or or_not_new != 2) and or_paid_not_shipped IS NOT NULL and or_cancelled IS NULL and or_out_of_stock IS NULL) OR (or_paid_not_shipped IS NOT NULL and or_cancelled IS NULL and or_id not in (select oi_or_id from shopsystem_order_items))'),
			);

			$transactionListSettings['VendorFilterBy']	 =		array(
			/*
										array('name' => 'Las Palmas', 'filter' => 'or_basket REGEXP \'pr_ve_id.;N\''),
										array('name' => 'Accessories', 'filter' => 'or_basket REGEXP \'pr_ve_id.;s:1:.1.\''),
										array('name' => 'Swiss', 'filter' => 'or_basket REGEXP \'pr_ve_id.;s:1:.2.\''),
			*/
										array('name' => 'Las Palmas', 'filter' => 'or_id in (select DISTINCT op_or_id from ordered_products, shopsystem_products where pr_ve_id IS NULL and op_pr_id = pr_id )'),
										array('name' => 'Accessories', 'filter' => 'or_id in (select DISTINCT op_or_id from ordered_products, shopsystem_products where pr_ve_id = \'1\' and op_pr_id = pr_id )'),
										array('name' => 'Swiss', 'filter' => 'or_id in (select DISTINCT op_or_id from ordered_products, shopsystem_products where pr_ve_id = \'2\' and op_pr_id = pr_id )'),
										array('name' => 'Marbella', 'filter' => 'or_id in (select DISTINCT op_or_id from ordered_products, shopsystem_products where pr_ve_id = \'4\' and op_pr_id = pr_id )'),
										array('name' => 'Ravi', 'filter' => 'or_id in (select DISTINCT op_or_id from ordered_products, shopsystem_products where pr_ve_id = \'5\' and op_pr_id = pr_id )'),
			);

			$gwQ = query( "select * from payment_gateways" );

			$transactionListSettings['PaymentGatewayFilterBy']	 =	array();
			while ($gwR = $gwQ->fetchRow( ) )
				$transactionListSettings['PaymentGatewayFilterBy'][] = array('name' => $gwR['pg_name'], 'filter' => "tr_bank = {$gwR['pg_id']}" );

			$transactionListSettings['ArchiveFilterBy']	 =		array(
										array('name' => 'Current', 'filter' => 'or_archive_year IS NULL' ),
										array('name' => 'Archived', 'filter' => 'or_archive_year IS NOT NULL' ),
			);
			$archiveYearResult = query( "select distinct or_archive_year as year from shopsystem_orders order by 1 desc" );
			while( $row = $archiveYearResult->fetchRow() )
				if( $row['year'] != NULL )
					$transactionListSettings['ArchiveFilterBy'][] = array('name' => $row['year'], 'filter' => 'or_archive_year = '.$row['year'] );

			$transactionListSettings['SearchFields']	=	array('or_purchaser_email','or_basket','tr_payment_details_szln');
		}
		
		return $transactionListSettings;
	}
	
	
	function __construct() {
		parent::__construct(array());
//		$this->Administration(array());
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
