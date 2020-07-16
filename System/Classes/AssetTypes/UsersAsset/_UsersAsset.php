<?php

requireOnceClass('AssetTypes');
requireOnceClass('Field');

class UsersAsset extends AssetTypes {
	var $fieldPrefix = 'AST_USER_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('view_display.php');
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
	
	function processSave(&$asset) {
		require('model_processSave.php');
	}
}


?>
