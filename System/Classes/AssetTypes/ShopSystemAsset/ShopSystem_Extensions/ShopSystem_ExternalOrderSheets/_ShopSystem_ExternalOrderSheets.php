<?php

class ShopSystem_ExternalOrderSheets extends Plugin {

	var $prefix = 'ExternalOrderSheets';
	var $singular = 'External Order Sheet';
	var $plural = 'External Order Sheets';
	var $tableName = 'shopsystem_order_sheets';
	var $tablePrimaryKey = 'ors_id';
	var $parentTable = null;
	var $tableAssetLink = null;
	var $assetLink = null;
	var $hideNewButton = ' ';
	var $tableDisplayFieldTitles = array('Order Sheet ID','Date','External Invoice Number','Min','Max','Total','Date Paid');
	var $tableDisplayFields = array('ors_id','ors_date','ors_invoice_number','MinOrID','MaxOrID','ors_total','ors_paid');
	var $tableTimeStamp = null;
	var $fields = array();
	
	function __construct() {
		$this->pluginDirectory = dirname(__FILE__);
		parent::__construct();
	}

	function inputFilter() {
		parent::inputFilter();
		$this->param('BreadCrumbs','Administration');
		$this->display->layout = 'Administration';
		// Must be able to Administer something to access these Actions
			
		$result = new Request('Security.Authenticate',array(
				'Permission'	=>	'RestrictedAdmin',
				//'Permission'	=>	'CanAdministerAtLeastOneAsset',
		));
		
	}	

	function viewList() {
		require('query_list.php');		
		require('view_list.php');				
	}

	function view() {
		require('query_sumview.php');		
		require('view_view.php');				
	}	

	function customs() {
		require('query_viewCustoms.php');		
		require('view_viewCustoms.php');				
	}	

	function customs2() {
		require('query_viewCustoms2.php');		
		require('view_viewCustoms2.php');				
	}	

	function inventory() {
		require('query_viewCustoms2.php');		
		require('view_viewInventory.php');				
	}	

	function customs3() {
		require('query_viewCustoms3.php');		
		require('view_viewCustoms3.php');				
	}	

	function scanWindow() {
		require('query_scanwindow.php');		
		require('view_scanwindow.php');				
	}	

	function manageLocations() {
		require('query_manageLocations.php');		
		require('view_manageLocations.php');				
	}	

	function viewPacking() {
		require('query_viewPacking.php');		
		require('view_viewPacking.php');				
	}	

	function showPDF() {
		require('query_showPDF.php');		
		require('view_showPDF.php');				
	}	

	function showCSV() {
		require('query_showCSV.php');		
		require('view_showCSV.php');				
	}	

	function swisspostCSV() {
		require('query_swisspostCSV.php');		
		require('view_swisspostCSV.php');				
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

	function markShipped() {
		require('model_markShipped.php');		
	}	

	function sendEmail() {
		require('model_sendEmail.php');		
	}	
	
	function addItem() {
		require('query_addItem.php');		
	}
	
	function exposeServices() {
		$prefix = 'ExternalOrderSheets';
		return array(
			"$prefix.List"			=>		array('method' => 'viewList'),
			"$prefix.ScanWindow"	=>		array('method' => 'scanWindow'),
			"$prefix.View"			=>		array('method' => 'view'),
			"$prefix.ViewPacking"	=>		array('method' => 'viewPacking'),
			"$prefix.Customs"		=>		array('method' => 'customs'),
			"$prefix.Customs2"		=>		array('method' => 'customs2'),
			"$prefix.Inventory"		=>		array('method' => 'inventory'),
			"$prefix.Customs3"		=>		array('method' => 'customs3'),
			"$prefix.ManageLocations"	=>	array('method' => 'manageLocations'),
			"$prefix.ShowPDF"	=>			array('method' => 'showPDF'),
			"$prefix.ShowCSV"	=>			array('method' => 'showCSV'),
			"$prefix.SwisspostCSV"	=>			array('method' => 'swisspostCSV'),
			"$prefix.MarkShipped"	=>		array('method' => 'markShipped'),
			"$prefix.Edit"			=>		array('method' => 'edit'),
			"$prefix.MarkPaid"		=>		array('method' => 'markPaid'),
			"$prefix.DeleteItem"	=>		array('method' => 'deleteItem'),
			"$prefix.AddItem"	=>			array('method' => 'addItem'),
			"$prefix.SendEmail"		=>		array('method' => 'sendEmail'),
		);
	}
}

?>
