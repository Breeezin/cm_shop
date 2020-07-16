<?php
requireOnceClass('FieldSet');
class WebPayConfigurationAdministration extends FieldSet {
	var $children = array();
	var $prefix;
	var $plural;
	var $singular;
	var $payment;
	var $tableDeleteFlag = NULL;
	var $tableOrderBy = array();
	var $tableDisplayFields;
//	var $tableSearchFields;
	var $tableDisplayFieldTitles = NULL;
	var $linkedTables = array();	
	var $tableSearchFields = array();
	var $configuration = '';
	var $currencySettings = '';
	var $chequeSettings = '';
	var $invoiceSettings = '';
	var $collectionSettings = '';
	var $directSettings = '';
	var $creditCardSettings = '';
	var $primaryKey	= 1;
	function inputFilter() {					
		
		$this->display->layout = 'Administration';
		$result = new Request('Security.Authenticate',array(
			'Permission'	=>	'CanAdministerAtLeastOneAsset',
		));		
	}
	
	function edit() {
		require("query_edit.php");
		require("model_edit.php");
		require("view_edit.php");
	}
	function chequeEdit() {
		require("model_chequeEdit.php");
		require("view_chequeEdit.php");
	}
	function directPaymentEdit() {
		require("model_directEdit.php");
		require("view_directEdit.php");
	}
	
	function creditCardEdit() {
		require("model_creditCardEdit.php");
		require("view_creditCardEdit.php");
	}
	function invoiceEdit() {
		require("model_invoiceEdit.php");
		require("view_invoiceEdit.php");
	}
	function collectionEdit() {
		require("model_collectionEdit.php");
		require("view_collectionEdit.php");
	}
		
	function exposeServices() {
		$prefix = 'web_pay_configuration';
		
		return array(			
			"$prefix.Edit"							=>		array('method' => 'edit'),						
			"WebPayChequeConfiguration.Edit"		=>		array('method' => 'chequeEdit'),						
			"WebPayInvoiceConfiguration.Edit"		=>		array('method' => 'invoiceEdit'),						
			"WebPayCollectionConfiguration.Edit"	=>		array('method' => 'collectionEdit'),						
			"WebPayCreditCardConfiguration.Edit"	=>		array('method' => 'creditCardEdit'),						
			"WebPayDirectPaymentConfiguration.Edit"	=>		array('method' => 'directPaymentEdit'),						
		);
	}
	
	function __construct($s = null) {
		parent::__construct();

		$settings = [
			'prefix'					=>	'WebPayConfigruation',
			'singular'					=>	'Web Pay configuration',
			'plural'					=>	'WebPayConfigurations',
			'tableName'					=>	'web_pay_configuration',
			'tablePrimaryKey'			=>	'wpc_id',
			'tableDisplayFields'		=>	array('wpc_id'),
			'tableOrderBy'				=>	array('wpc_id' => 'Default'),
			];
		if( $s )
			$settings = array_merge($s, $settings);
		
		foreach($settings as $property => $value) $this->{$property} = $value;		
		if (ss_optionExists('Invoice Payment Option')) {
			$this->addField(new CheckBoxField(array(
				'name'			=>	'wpc_can_invoice',
				'displayName'	=>	'Accept Invoice Payment',
				'note'			=>	'Please define payment note',			
				'required'		=>	false,			
			)));
		}if (ss_optionExists('Pay On Collection Payment Option')) {
			$this->addField(new CheckBoxField(array(
				'name'			=>	'wpc_use_collection',
				'displayName'	=>	'Accept Pay on Collection',
				'note'			=>	'Please define payment note',			
				'required'		=>	false,			
			)));
		}
		
		$this->addField(new CheckBoxField(array(
			'name'			=>	'wpc_use_cheque',
			'displayName'	=>	'Accept Cheque',
			'note'			=>	'Please define address for the cheque payment',			
			'required'		=>	false,			
		)));
		
		
		$this->addField(new CheckBoxField(array(
			'name'			=>	'wpc_use_credit_card',
			'displayName'	=>	'Accept CreditCard',
			'note'			=>	'Please define address for the cheque payment',			
			'required'		=>	false,			
		)));
		
		$this->addField(new CheckBoxField(array(
			'name'			=>	'wpc_direct_payment',
			'displayName'	=>	'Direct Payment',
			'note'			=>	'Please define account details for the direct payment',			
			'required'		=>	false,			
		)));
		
		/*
		$this->addField(new TextField(array(
			'name'			=>	'wpc_secure_server',
			'displayName'	=>	'Secure Server',
			'note'			=>	null,			
			'required'		=>	false,			
		)));
		*/
		
		$this->addField(new HiddenField(array(
			'name'			=>	'wpc_default_currency_details',
			'displayName'	=>	'Default Currency',
			'note'			=>	null,			
			'required'		=>	false,			
		)));
		
		
		/*
		$this->addField(new TextField(array(
			'name'			=>	'WePaCoChequePayTo',
			'displayName'	=>	'Paydable To',
			'note'			=>	null,
			'required'		=>	false,			
			'size'	=> 12, 'maxlength' =>225,
		)));
		*/
		/*
		$this->addField(new SelectField(array(
			'name'			=>	'WePaCoProcessorLink',
			'displayName'	=>	'Payment Method',
			'note'			=>	NULL,
			'multi'			=>	false,
			'required'		=>	false,
			'linkQueryAction'	=>	'ProcessorAdministration.Query',
			'linkQueryValueField'	=>	'wpp_id',
			'linkQueryDisplayField'	=>	'wpp_display_name',
			'linkTableName'		=>	NULL,
			'linkTableOurKey'	=>	NULL,
			'linkTableTheirKey'	=>	NULL,
		)));
		*/
		/*
		$this->addField(new MultiCheckField(array(
			'name'			=>	'CardTypes',
			'displayName'	=>	'Accept',
			'note'			=>	NULL,
			'required'		=>	TRUE,
			'linkQueryAction'	=>	'CreditCardTypeAdministration.Query',
			'linkQueryValueField'	=>	'cct_id',
			'linkQueryDisplayField'	=>	'cct_name',
			'linkTableName'		=>	'web_pay_configuration_credit_card_types',
			'linkTableOurKey'	=>	'wpcf_wpc_id',
			'linkTableTheirKey'	=>	'wpcf_cct_id',
		)));
		
		$this->addField(new CheckBoxField(array(
			'name'			=>	'WePaCoRequestCvC2',
			'displayName'	=>	'Request CvC2',
			'note'			=>	NULL,
			'required'		=>	FALSE,
			'verify'		=>	FALSE,
			'unique'		=>	FALSE,
		)));	
		*/			
	}	

}
?>
