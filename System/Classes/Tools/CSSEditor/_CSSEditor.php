<?php
requireOnceClass("Administration");

class CSSEditor extends Administration {
	
	function exposeServices() {
		$prefix = 'CSSEditor';
		return array(
		 	
		 	"{$prefix}.Edit"		=>	array('method'	=>	'edit')
		 );		
	}
	
	function edit() {		
		$this->display->title = "CSS Editor";
		require('model_edit.php');
		require('view_edit.php');
	}
	
	function inputFilter() {
		parent::inputFilter();	
	}
}
?>