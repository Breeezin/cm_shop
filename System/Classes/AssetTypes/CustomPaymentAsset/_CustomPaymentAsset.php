<?php
//briar made this a System Asset 5.9.05

requireOnceClass('AssetTypes');

class CustomPaymentAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_CUSTOMPAYMENT_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}

	function display(&$asset) {
		require('query_display.php');		
	}

	function newAsset(&$asset) {
		require('model_new.php');
		return null;
	}

	function embed(&$asset) {
		require('view_embed.php');
	}

	function properties(&$asset) {
		require('view_properties.php');
	}
	
	function defineFields(&$asset) {
		require('query_defineFields.php');
	}	

	function edit(&$asset) {
		require('view_edit.php');
	}	
	
	function delete(&$asset) {
		die("Please define the delete method and do not forget to delete the select field options.");
	}	

}

?>