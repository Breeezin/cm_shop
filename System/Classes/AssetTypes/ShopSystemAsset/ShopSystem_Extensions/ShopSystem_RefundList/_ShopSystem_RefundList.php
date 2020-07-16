<?php
class ShopSystem_RefundList extends Plugin {

	function exposeServices() {
		
		return array(
				'ShopSystem_RefundList.RemoveOrder' => array('method'	=>	'removeOrder'), 
				'ShopSystem_RefundList.UpdateAuthorisationNumber' => array('method'	=>	'updateAuthorisationNumber'), 
				'ShopSystem_RefundList.Display' => array('method'	=>	'display'), 
			);
			
	}

	function updateAuthorisationNumber() {
		forceSSLMode();
		require('model_updateAuthorisationNumber.php');
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
