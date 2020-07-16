<?php

requireOnceClass('FieldSet');
class WebPay extends FieldSet {
	var $webPayConfig = null;
	var $children = array();
	var $prefix;
	var $plural;
	var $singular;
	var $payment;
	var $tableDeleteFlag = NULL;
	var $tableOrderBy = array();
	var $tableDisplayFields;
	var $tableDisplayFieldTitles = NULL;
	var $linkedTables = array();	
	var $tableSearchFields = array();
	
	
	function __construct($settings = array()) 
    {
		parent::__construct();
		//$this->FieldSet();
		
		$tableDisplayFields = array();
		$tableDisplayFieldTitles = array();

		if (ss_optionExists('Shop Acme Rockets')) 
		{
				array_push($tableDisplayFields,'TrSelect');
				array_push($tableDisplayFieldTitles,'Select');
		}

		array_push($tableDisplayFields,'tr_id');
		array_push($tableDisplayFieldTitles,'Order ID');

		array_push($tableDisplayFields,'or_not_new');
		array_push($tableDisplayFieldTitles,'AddrChecked');

		array_push($tableDisplayFields,'tr_fraud_score');
		array_push($tableDisplayFieldTitles,'Score');

		array_push($tableDisplayFields,'tr_timestamp');
		array_push($tableDisplayFieldTitles,'Date');

		array_push($tableDisplayFields,'tr_client_name');
		array_push($tableDisplayFieldTitles,'Name');

//		array_push($tableDisplayFields,'tr_currency_link');
//		array_push($tableDisplayFieldTitles,'Order Total');

		if (ss_optionExists('Shop Orders Hide Charge Total') === false) 
        {
			array_push($tableDisplayFields,'tr_charge_total');
			array_push($tableDisplayFieldTitles,'Amount Charged');
		}
		
		if( ss_adminCapability( ADMIN_PRODUCT_PRICING ) )
        {
			array_push($tableDisplayFields,'tr_profit');
			array_push($tableDisplayFieldTitles,'Profit');
//			array_push($tableDisplayFields,'or_profit');
//			array_push($tableDisplayFieldTitles,'CM');
			array_push($tableDisplayFields,'or_site_folder');
			array_push($tableDisplayFieldTitles,'Website');
			array_push($tableDisplayFields,'pg_name');
			array_push($tableDisplayFieldTitles,'Pay By');
			if ( array_key_exists( 'FilterBy', $_POST ) &&  ($_POST['FilterBy'] == 'bt_address IS NOT NULL')  )
			{
				array_push($tableDisplayFields,'bt_address');
				array_push($tableDisplayFieldTitles,'Address');
				array_push($tableDisplayFields,'bt_account');
				array_push($tableDisplayFieldTitles,'Sending Account');
				array_push($tableDisplayFields,'bt_received');
				array_push($tableDisplayFieldTitles,'Sent Amount');
			}

		}

		array_push($tableDisplayFields,'or_follow_up_status');
		array_push($tableDisplayFieldTitles,'FollowUp');


/*
		if (ss_optionExists('Shop Orders Hide Pay By') === false) 
        {
			array_push($tableDisplayFields,'tr_payment_method');
			array_push($tableDisplayFieldTitles,'Pay By');
		}
*/

		$settings = array_merge($settings, array(
				'prefix'					=>	'WebPay',
				'singular'					=>	'Web Payment',
				'plural'					=>	'Web Payments',
				'tableName'					=>	'transactions',
				'tablePrimaryKey'			=>	'tr_id',
				'tableTimeStamp'			=>	'tr_timestamp',
				'tableDisplayFields'		=>	$tableDisplayFields,
				'tableDisplayFieldTitles'	=>	$tableDisplayFieldTitles,
				'tableSearchFields'			=>	array('tr_id','tr_client_name'),
				'tableOrderBy'				=>	array('tr_reference' => 'Code'),
		));
		
		foreach($settings as $property => $value)
            $this->{$property} = $value;		
		
		// get web pay configuration, it will be used for which payment method to display etc
		
		$this->webPayConfig = getRow("SELECT * FROM web_pay_configuration WHERE wpc_id = 1");				
		//$this->webPayConfig = getRow("SELECT * FROM NOWebPayConfigurations WHERE wpc_id = 1");				
	}	
	
	function inputFilter($forAdmin = false) 
    {					
		if ($forAdmin) 
        {
			$this->display->layout = 'Administration';
			$result = new Request('Security.Authenticate',array(
				'Permission'	=>	'CanAdministerAtLeastOneAsset',
			));
		}		
	}
	function getPaymentNZDTotal() 
    {		
		return require("model_paymentNZDTotal.php");
	}
	function updateNewPayment($transactionType) 
    {
		require('model_updateNewPayment.php');
	}
	
	function paymentOption() 
    {
		return require('view_paymentOption.php');
	}
	
	function newChequePayment() 
    { 		
		require('query_newChequePayment.php');		
		require('model_newChequePayment.php');
		require('view_newChequePayment.php');		
	}
	function newConfirmPayment() 
    { 		
		require('query_newConfirmPayment.php');		
		require('model_newConfirmPayment.php');
		//require('view_newConfirmPayment.php');		
	}
	function newDirectPayment() 
    { 		
		require('query_newDirectPayment.php');		
		require('model_newDirectPayment.php');
		require('view_newDirectPayment.php');		
	}
	function newInvoicePayment() 
    { 		
		require('query_newInvoicePayment.php');		
		require('model_newInvoicePayment.php');
		require('view_newInvoicePayment.php');		
	}
	function newCollectionPayment() 
    { 		
		require('query_newCollectionPayment.php');		
		require('model_newCollectionPayment.php');
		require('view_newCollectionPayment.php');		
	}
	function newCreditCardPayment() 
    {
		
		require('query_newCreditCardPayment.php');		
		require('model_newCreditCardPayment.php');
		require('view_newCreditCardPayment.php');
	}

	function displayPayment() 
    {
		$this->inputFilter(true);
		require('query_displayPayment.php');				
		require('view_displayPayment.php');
	}
	function displayTransactionResult() 
    {
		$this->inputFilter(true);
		require('query_displayTransactionResult.php');				
		require('view_displayTransactionResult.php');
	}
	
	function listPayment() 
    {
	/*
        if( $_SERVER['REQUEST_METHOD'] == 'POST' )        // this is a POST, translate it into a simple GET so refresh works properly
        {
            $newURL = ($_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://") 
                        . array_shift(explode(':',$_SERVER['HTTP_HOST'])) 
                        . ($_SERVER['SERVER_PORT'] != 80?':'.$_SERVER['SERVER_PORT']:'') 
                        . $_SERVER['REQUEST_URI'];

			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_POST );
			reset( $_POST );
            while (list($key,$val) = each($_POST))
                $newURL .= '&'.$key.'='.$val;

			ss_log_message( $newURL );
            header( 'Location:'.$newURL );
        }
		*/
        
		if( !ss_adminCapability( ADMIN_ORDER_LIST ) )
		{
			ss_log_message( "Admin unable to ADMIN_ORDER_LIST" );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SESSION );
			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
			die;
		}
		$this->inputFilter(true);
		require('query_list.php');
		$customFilePath = expandPath('Custom/Classes/SecureWebPayment/view_list.php');
		if (file_exists($customFilePath)) 
        {		
			require($customFilePath);			
		}
        else 
        {
			require('view_list.php');		
		}
	}
	
	function processPayment() 
    {
		$this->inputFilter(true);
		require('model_process.php');		
	}
	
	
	// insert a transaction record into the db table so stores total price, reference, client name and 
	function preparePayment() 
    {
		$this->display->layout = 'None';
		return require('model_preparePayment.php');
	}
	
	function markPaid() 
    {
		$this->display->layout = 'None';
		return require('model_markPaid.php');
	}
	
	function getChargePriceFromDefaultSettings($originPrice, $originCurrency, $returnWithSymbol = true) 
    {		
		return require('model_chargePriceFromDefaultSettings.php');
	}
	
	function deletePayment() 
    {
		$this->inputFilter(true);
		$this->display->layout = 'None';
		
		require('model_deletePayment.php');
	}
	
	function showCVV2() 
    {
		$this->display->layout = 'popup';
		require("view_showCVV2.php");
	}
	function exposeServices() 
    {
		$prefix = 'WebPay';
		
		return array(
			"{$prefix}Administration.DeletePayment"	=>		array('method' => 'deletePayment'),			
			"$prefix.Options"						=>		array('method' => 'paymentOption'),			
			"$prefix.ByCheque"						=>		array('method' => 'newChequePayment'),			
			"$prefix.ByConfirm"						=>		array('method' => 'newConfirmPayment'),			
			"$prefix.ByInvoice"						=>		array('method' => 'newInvoicePayment'),			
			"$prefix.ByCollection"					=>		array('method' => 'newCollectionPayment'),			
			"$prefix.ByCreditCard"					=>		array('method' => 'newCreditCardPayment'),			
			"$prefix.ByDirectPayment"				=>		array('method' => 'newDirectPayment'),			
			"{$prefix}Administration.List"		=>		array('method' => 'listPayment'),			
			"{$prefix}Administration.ProcessPayment"	=>		array('method' => 'processPayment'),			
			"{$prefix}Administration.DisplayPayment"	=>		array('method' => 'displayPayment'),			
			"{$prefix}Administration.DisplayTransactionResult"	=>		array('method' => 'displayTransactionResult'),			
			"$prefix.PreparePayment"			=>		array('method' => 'preparePayment'),			
			"$prefix.MarkPaid"			=>		array('method' => 'markPaid'),			
			"$prefix.ShowCVV2"			=>		array('method' => 'showCVV2'),			
			
		);
	}
}
?>
