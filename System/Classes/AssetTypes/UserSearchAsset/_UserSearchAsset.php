<?php

requireOnceClass('AssetTypes');
requireOnceClass('Field');

class UserSearchAsset extends AssetTypes {
	var $fieldPrefix = 'AST_USERSEARCH_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('query_display.php');
	}
	
	function embed(&$asset) {
		$this->display($asset);
	}

	function defineFields(&$asset) {
		require('query_defineFields.php');
	}
	
	function edit(&$asset) {
		require('view_edit.php');
	}	
}


?>
