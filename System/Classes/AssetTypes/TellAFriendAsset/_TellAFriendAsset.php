<?php

requireOnceClass('AssetTypes');
requireOnceClass('Field');

class TellAFriendAsset extends AssetTypes {
	
	function display(&$asset) {		
		require('model_display.php');
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
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
}


?>