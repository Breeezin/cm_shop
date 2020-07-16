<?php
requireOnceClass('AssetTypes');

class SchedulerAsset extends AssetTypes {

	var $fieldPrefix = 'AST_SCHEDULER_';
	
	function getClassName() {
		return substr(basename(__FILE__),1,-4);
	}
	
	function processSave(&$asset) {
		require('model_processSave.php');
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
	function NextDay ($t) {
        /* Add One Day plus one possible DST hour */
        $t += 90000;
        $t = MkTime(12,0,0,Date("m",$t),Date("d",$t),Date("Y",$t));
        return ($t);
   }
}

?>
