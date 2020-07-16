<?php

requireOnceClass('AssetTypes');
requireOnceClass('Field');

class TextPageAsset extends AssetTypes {
	
	function display(&$asset) {
		require('view_display.php');
	}
	
	function embed(&$asset) {
		$this->display($asset);
	}

	function properties(&$asset) {
		require('view_properties.php');
	}
	
	function defineFields(&$asset) {
		require('query_defineFields.php');
	}
	
	function delete(&$asset) {
		// Most wont do anything in here	
		//die("Please define delete method.");
		
	}
	
	function edit(&$asset) {
		require('view_edit.php');
	}
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
}


?>
