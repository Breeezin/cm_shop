<?php


class TransactionsAdministration extends Plugin {
	
	function __construct() {
		parent::__construct();
	}

	function inputFilter() {
		/*
		$result = new Request('Security.Authenticate',array(
			'Permission'	=>	'Administeration',
		));
		
		return $result->value;*/
	}
	
	function exposeServices() {
		$prefix = 'transactions';
		
		return array(
			"{$prefix}.List"		=>		array('method' => 'listTransactions'),
		);
	}
		
	function listTransactions() {
		$this->display->layout = 'Administration';
		
		require('query_listTransactions.php');
		
		//require('model_listTransactions.php');
		
		require('view_listTransactions.php');
	
	} 
	
	
	
	
}

?>
