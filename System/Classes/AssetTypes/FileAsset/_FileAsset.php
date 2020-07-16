<?php
requireOnceClass('AssetTypes');

class FileAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_FILE_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
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

}

?>
