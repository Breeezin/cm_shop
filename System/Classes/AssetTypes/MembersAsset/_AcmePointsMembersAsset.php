<?php
requireOnceClass('AssetTypes');

class AcmePointsMembersAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_PPMEMBERS_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('query_display.php');
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
		require('model_delete.php');
	}	
}

?>