<?php

requireOnceClass('AssetTypes');
requireOnceClass('Field');

class SearchAsset extends AssetTypes {
		
	var $fieldPrefix = 'AST_SEARCH_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('query_display.php');
		require('view_display.php');
	}
	
	function embed(&$asset) {
		require('view_embed.php');
	}

	function defineFields(&$asset) {
		require('query_defineFields.php');
	}
	
	function edit(&$asset) {
		require('view_edit.php');
	}	
}


?>