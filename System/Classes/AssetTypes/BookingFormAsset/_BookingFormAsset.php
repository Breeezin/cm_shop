<?php
requireOnceClass('AssetTypes');

class BookingFormAsset extends AssetTypes {
	
	var $fieldPrefix = 'AST_BOOKINGFORM_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function display(&$asset) {
		require('query_display.php');
		require('model_display.php');
		require('view_display.php');
	}
		
	function embed(&$asset) {
		require('view_embed.php');
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
	
	function delete(&$asset) {
		die("Please define the delete method and do not forget to delete the select field options.");
	}	

}

?>