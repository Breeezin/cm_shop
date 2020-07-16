<?php
requireOnceClass('AssetTypes');


class FlashAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_FLASH_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('view_display.php');
	}
		
	function embed(&$asset) {
		require('view_display.php');
	}
	function embedImage(&$asset) {
		$this->display($asset);
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