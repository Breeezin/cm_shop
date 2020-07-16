<?php
requireOnceClass('ShopSystem_OrdersAdministration');
class ShopSystem_MemberOrders extends ShopSystem_OrdersAdministration {
	
	function exposeServices() {		
		$prefix = "ShopSystemMember";
		return array(						
			$prefix.'.ViewOrders'	=>	array('method'	=>	'viewOrders'),			
			$prefix.'.ViewInvoice'	=>	array('method'	=>	'viewInvoice'),			
			$prefix.'.ReOrder'		=>	array('method'	=>	'reorder'),			
		);		
	}

	function inputFilter() {			
		
		$result = new Request('Security.Authenticate',array(
				'Permission'	=>	'IsLoggedIn',					
		));					
	}	
	
	function viewOrders() {
		require('query_viewOrders.php');		
	}	
	
	function reorder() {
		require('model_reorder.php');		
	}
	
	function viewInvoice() {		
		//$this->param('or_id');
		//$this->param('tr_id');		
		//$this->param('as_id');
		//ss_DumpVar($this);
		parent::viewInvoice();
	}
		
	
	
	function __construct() {
		parent::__construct(array(	) );
		/*		
		$this->ShopSystem_OrdersAdministration(array());
		$this->ShopSystem_OrdersAdministration(array(
			'prefix'					=>	'ShopSystem',
			'singular'					=>	'Order',
			'plural'					=>	'Orders',
			'tableName'					=>	'shopsystem_orders',
			'tablePrimaryKey'			=>	'or_id',
			'tableDisplayFields'		=>	array('or_id', 'or_recorded', 'or_purchaser_firstname', 'or_purchaser_lastname','or_total'),
			'tableDisplayFieldTitles'	=>	array('ID', 'Ordered', 'First Name', 'Last Name', 'Total'),
			'tableOrderBy'				=>	array('or_recorded' => 'Ordered', 'or_purchaser_firstname, or_purchaser_lastname' => 'Name'),
			'tableAssetLink'			=>	'or_as_id',			
		));*/
	}

}
?>
