<?php
class ExportUsers extends Plugin {

	function inputFilter() {
		parent::inputFilter();
		$result = new Request('Security.Authenticate',array(
			'Permission'	=>	'CanAdministerAtLeastOneAsset',
		));	
	}
	
	function exposeServices() {
		return array(			
			'UpdateUsers'	=>	array('method'	=>	'updateUsers'),			
		);
	}

	function updateUsers() {
		$this->display->layout = 'None';
		require('query_updateUsers.php');
		//require('view_updateUsers.php');
	}	
	
}
?>
