<?php

class ShopSystem_ShippingDetails extends Plugin  {
	
	var $fieldSet = NULL;
	var $notSelectedFieldNames = array();
	function __construct() {
		parent::__construct();
	}
	
	function display(&$webpay, $isForm = true) {
		return require('view_display.php');
	}
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function defineFields(&$shop) {
		require('query_defineFields.php');
	}
}
?>
