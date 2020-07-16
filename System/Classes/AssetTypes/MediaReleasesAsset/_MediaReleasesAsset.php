<?php
requireOnceClass('AssetTypes');

class MediaReleasesAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_MEDIARELEASES_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('query_display.php');
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