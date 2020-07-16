<?php

class ChequeSettings extends Plugin  {
	
	var $fieldSet = NULL;
	
	function __construct() {
		parent::__construct();
	}
	
	function display(&$webpay) {
		return require('view_display.php');
	}
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function defineFields(&$webpay) {
		require('query_defineFields.php');
	}
}
?>
