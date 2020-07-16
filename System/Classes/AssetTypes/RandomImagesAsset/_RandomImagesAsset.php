<?php
requireOnceClass('AssetTypes');

class RandomImagesAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_RANDOMIMAGES_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
		
	function embed(&$asset) {
		require('view_embed.php');
	}
	
	function display(&$asset) {
		require('model_display.php');
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