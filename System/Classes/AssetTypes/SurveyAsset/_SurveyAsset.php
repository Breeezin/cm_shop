<?php
requireOnceClass('AssetTypes');

class SurveyAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_SURVEY';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('query_display.php');
	}
	function newAsset(&$asset) {	
		require('model_new.php');
		return null;
	}
	function embed(&$asset) {
		$this->display($asset);
	}
	
	// Delete any records from associated tables
	function delete(&$asset) {
		require('model_delete.php');
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

    
	function processSave(&$asset) {
		require('model_processSave.php');
	}

}

?>
