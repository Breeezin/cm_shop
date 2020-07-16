<?php

class ShopSystem_ShippingCharges extends Plugin {

	var $prefix = 'ShopSystem_ShippingCharges';
	var $singular = 'Shipping Charge';
	var $plural = 'Shipping Charges';
	var $tableName = 'shopsystem_shipping_charges';
	var $tablePrimaryKey = 'ssc_id';
	var $parentTable = null;
	var $tableAssetLink = null;
	var $assetLink = null;
	var $hideNewButton = ' ';
	var $tableDisplayFieldTitles = array('Reference','Date','Total Cost','Date Paid');
	var $tableDisplayFields = array('ssc_reference','ssc_date','ssc_amount','ssc_paid');
	var $tableTimeStamp = null;
	var $fields = array();
	
	function shopsystem_supplier_order_sheets() {
		$this->pluginDirectory = dirname(__FILE__);
		$this->Plugin();
	}

	function inputFilter() {
		parent::inputFilter();
		$this->param('BreadCrumbs','Administration');
		$this->display->layout = 'Administration';
		// Must be able to Administer something to access these Actions
			
		$result = new Request('Security.Authenticate',array(
				'Permission'	=>	'CanAdministerAtLeastOneAsset',
		));
		
	}	
	
	function viewList() {
		require('query_list.php');		
		require('view_list.php');				
	}
	
	function view() {
		require('query_view.php');		
		require('view_view.php');				
	}	

	function edit() {
		require('query_view.php');		
		require('query_edit.php');
		require('model_edit.php');				
		require('view_edit.php');				
	}	

	function deleteItem() {
		require('model_deleteItem.php');		
	}	
	
	function fixTotal($id) {
		require('model_fixTotal.php');	
	}
	
	function markPaid() {
		require('model_markPaid.php');		
	}	

	function sendEmail() {
		require('model_sendEmail.php');		
	}	
	
	function addItem() {
		require('query_addItem.php');		
	}
	
	function exposeServices() {
		$prefix = 'ShopSystem_ShippingCharges';
		return array(
			"$prefix.List"			=>		array('method' => 'viewList'),
			"$prefix.View"			=>		array('method' => 'view'),
			"$prefix.Edit"			=>		array('method' => 'edit'),
			"$prefix.MarkPaid"		=>		array('method' => 'markPaid'),
			"$prefix.DeleteItem"	=>		array('method' => 'deleteItem'),
			"$prefix.AddItem"	=>		array('method' => 'addItem'),
			"$prefix.SendEmail"		=>		array('method' => 'sendEmail'),
		);
	}
}

?>
