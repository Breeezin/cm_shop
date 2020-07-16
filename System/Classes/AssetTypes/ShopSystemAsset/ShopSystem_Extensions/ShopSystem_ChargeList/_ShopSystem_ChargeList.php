<?php
class ShopSystem_ChargeList extends Plugin {

	function exposeServices() {
		
		return array(
				'ShopSystem_ChargeList.AddOrder' => array('method'	=>	'addOrder'), 
				'ShopSystem_ChargeList.RemoveOrder' => array('method'	=>	'removeOrder'), 
				'ShopSystem_ChargeList.UpdateAuthorisationNumber' => array('method'	=>	'updateAuthorisationNumber'), 
				'ShopSystem_ChargeList.Display' => array('method'	=>	'display'), 
				'ShopSystem_ChargeList.Process' => array('method'	=>	'process'), 
			);
			
	}

	function updateAuthorisationNumber() {
		forceSSLMode();
		require('model_updateAuthorisationNumber.php');
	}	
	function addOrder() {
		forceSSLMode();
		require('model_addOrder.php');
	}
	function removeOrder() {
		forceSSLMode();
		require('model_removeOrder.php');
	}

	function display() {
		forceSSLMode();
		require('query_display.php');	
		require('view_display.php');
	}

	function process() {
		forceSSLMode();
		require('query_process.php');	
		require('view_process.php');
	}

	function inputFilter() {
		parent::inputFilter();
		$this->param('BreadCrumbs','Administration');
		$this->display->layout = 'Administration';
		// Must be able to Administer something to access these Actions
			
		$result = new Request('Security.Authenticate',array(
				'Permission'	=>	'RestrictedAdmin',
		));
		
	}	
	
}
?>
