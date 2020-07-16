<?php

requireOnceClass('AssetTypes');
requireOnceClass('Field');

class SubscribeAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_SUBSCRIBE_';
	
	function display(&$asset) {	
		require('model_display.php');
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
		die("Please define the delete method for SubscribeAsset");
	}
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
}


?>