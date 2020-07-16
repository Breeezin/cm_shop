<?php
requireOnceClass("Administration");

class RecycleBin extends Administration {
	
	function exposeServices() {
		$prefix = 'RecycleBin';
		return array(		 
		 	"{$prefix}.AssetList"		=>	array('method'	=>	'assetlist'),
		 	"{$prefix}.Delete"		=>	array('method'	=>	'delete'),
		 	"{$prefix}.Restore"		=>	array('method'	=>	'restore'),
		 );		
	}
	
	function delete() {
		require('model_delete.php');
	}
	function restore() {
		
		require('model_restore.php');
	}
	function assetlist() {		
		$this->display->title = "Recycle Bin - Deleted Items";
		require('query_list.php');
		require('view_list.php');
	}
	
	function inputFilter() {
		
		parent::inputFilter();	
	}
}
?>