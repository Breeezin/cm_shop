<?php
class ShopSystem_Issues extends Plugin {

	function exposeServices() {
		return array(
				'ShopSystem_Issues.Edit' => array('method'	=>	'edit'), 
				'ShopSystem_Issues.AddResponse' => array('method'	=>	'addResponse'), 
				'ShopSystem_Issues.Assign' => array('method'	=>	'assign'), 
				'ShopSystem_Issues.Close' => array('method'	=>	'close'), 
				'ShopSystem_Issues.Open' => array('method'	=>	'open'), 
				'ShopSystem_Issues.Hide' => array('method'	=>	'hide'), 
				'ShopSystem_Issues.Delete' => array('method'	=>	'delete'), 
				'ShopSystem_Issues.UnHide' => array('method'	=>	'unhide'), 
				'ShopSystem_Issues.Display' => array('method'	=>	'display'), 
				'ShopSystem_Issues.Split' => array('method'	=>	'split'), 
				'ShopSystem_Issues.ShowLog' => array('method'	=>	'showLog'), 
			);
	}

	function edit() {
		require('model_edit.php');
		require('query_edit.php');
		require('view_edit.php');
	}

	function close() {
		require('model_close.php');
	}

	function open() {
		require('model_open.php');
	}

	function delete() {
		require('model_deleteResponse.php');
	}

	function hide() {
		require('model_hide.php');
	}

	function unhide() {
		require('model_unhide.php');
	}

	function addResponse() {
		require('model_addResponse.php');
	}

	function assign() {
		require('model_assign.php');
	}

	function display() {
		require('query_display.php');	
		require('view_display.php');
	}

	function split() {
		require('model_split.php');
	}

	function showLog() {
		require('view_showLog.php');
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
	
}
?>
