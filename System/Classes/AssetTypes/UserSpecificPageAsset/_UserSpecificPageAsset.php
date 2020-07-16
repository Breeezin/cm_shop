<?php
requireOnceClass('AssetTypes');

class UserSpecificPageAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_USP_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('query_display.php');
		require('view_display.php');
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
		die("Please define the delete method for UserSpecificPageAsset");
	}
	
}

?>