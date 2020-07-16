<?php
requireOnceClass('AssetTypes');

class ImageAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_IMAGE_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('view_display.php');
	}
		
	function embed(&$asset) {
		require('view_embed.php');
	}
	
	function getInfo(&$asset) {
		require('view_getInfo.php');
	}
	
	function embedImage(&$asset) {
		require('view_embedImage.php');
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